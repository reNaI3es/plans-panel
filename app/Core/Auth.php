<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Аутентифікація: сесія, логін/логаут, re-auth, 2FA-стан.
 */
final class Auth
{
    private static ?array $currentUser = null;

    /**
     * Спробувати автентифікувати з сесії.
     */
    public static function boot(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Оновлюємо last_activity
        if (isset($_SESSION['user_id'])) {
            $lastActivity = $_SESSION['last_activity'] ?? 0;
            $idleTimeout = Config::get('session_idle', 3600);
            if (time() - $lastActivity > $idleTimeout) {
                self::logout();
                return;
            }
            $_SESSION['last_activity'] = time();

            // Перевірка session_version
            $db = Db::instance();
            $user = $db->fetchOne(
                "SELECT session_version FROM users WHERE id = ?",
                [$_SESSION['user_id']]
            );
            if (!$user || (int)$user['session_version'] !== ($_SESSION['session_version'] ?? 0)) {
                self::logout();
                return;
            }

            // Перевірка відбитка (UA + IP/24)
            $fingerprint = self::makeFingerprint();
            if (($fingerprint ?? '') !== ($_SESSION['fingerprint'] ?? '')) {
                self::logout();
                return;
            }
        }
    }

    /**
     * Поточний користувач (або null).
     */
    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        if (self::$currentUser === null) {
            $db = Db::instance();
            self::$currentUser = $db->fetchOne(
                "SELECT u.*, r.slug as role_slug, r.title as role_title, r.level as role_level
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE u.id = ? AND u.status = 'active'",
                [$_SESSION['user_id']]
            );
        }
        return self::$currentUser;
    }

    /**
     * Чи авторизований користувач.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * ID поточного користувача.
     */
    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    /**
     * Спроба входу.
     * @return array{success: bool, reason: string, user: ?array, require_2fa: bool}
     */
    public static function attempt(string $login, string $password): array
    {
        $db = Db::instance();
        $ipHash = ip_hash();
        $ua = user_agent();

        // Rate limit
        if (RateLimit::check('login:ip:' . $ipHash, 10, 900)) {
            Audit::log('login_rate_limited', null, null, null, $ipHash, $ua);
            return ['success' => false, 'reason' => 'rate_limit', 'user' => null, 'require_2fa' => false];
        }

        // Пошук користувача
        $user = $db->fetchOne("SELECT * FROM users WHERE login = ?", [$login]);

        if (!$user) {
            // Одинаковий час відповіді незалежно від існування акаунта
            password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2]);
            self::logLogin(null, $login, false, 'not_found');
            return ['success' => false, 'reason' => 'bad_credentials', 'user' => null, 'require_2fa' => false];
        }

        // Перевірка блокування
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            self::logLogin((int)$user['id'], $login, false, 'locked');
            return ['success' => false, 'reason' => 'locked', 'user' => null, 'require_2fa' => false];
        }

        // Перевірка статусу
        if ($user['status'] !== 'active') {
            self::logLogin((int)$user['id'], $login, false, 'suspended');
            return ['success' => false, 'reason' => 'suspended', 'user' => null, 'require_2fa' => false];
        }

        // Перевірка пароля
        if (!password_verify($password, $user['password_hash'])) {
            self::incrementFailedAttempts((int)$user['id']);
            self::logLogin((int)$user['id'], $login, false, 'bad_password');
            return ['success' => false, 'reason' => 'bad_credentials', 'user' => null, 'require_2fa' => false];
        }

        // Rehash якщо потрібно
        if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2])) {
            $newHash = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2]);
            $db->execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $user['id']]);
        }

        // Скидаємо failed attempts
        $db->execute("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?", [$user['id']]);

        // Перевірка 2FA
        $role = $db->fetchOne("SELECT require_2fa FROM roles WHERE id = ?", [$user['role_id']]);
        $require2fa = ($role && $role['require_2fa']) || $user['totp_enabled'];

        if ($require2fa && !$user['totp_enabled']) {
            // Роль вимагає 2FA, але не налаштовано
            self::logLogin((int)$user['id'], $login, false, '2fa_required');
            return ['success' => false, 'reason' => '2fa_required', 'user' => $user, 'require_2fa' => true];
        }

        if ($require2fa && $user['totp_enabled']) {
            // Потрібен крок 2FA
            $_SESSION['pending_user_id'] = $user['id'];
            $_SESSION['pending_2fa'] = true;
            return ['success' => false, 'reason' => 'need_2fa', 'user' => $user, 'require_2fa' => true];
        }

        // Успішний вхід
        self::completeLogin($user);
        return ['success' => true, 'reason' => 'ok', 'user' => $user, 'require_2fa' => false];
    }

    /**
     * Завершити вхід після успішної 2FA.
     */
    public static function complete2fa(int $userId): bool
    {
        $db = Db::instance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId]);
        if (!$user) return false;

        unset($_SESSION['pending_user_id'], $_SESSION['pending_2fa']);
        self::completeLogin($user);
        return true;
    }

    /**
     * Завершити вхід (встановити сесію).
     */
    private static function completeLogin(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['session_version'] = (int)$user['session_version'];
        $_SESSION['last_activity'] = time();
        $_SESSION['fingerprint'] = self::makeFingerprint();
        $_SESSION['login_time'] = time();

        // Оновлюємо last_login_at
        $db = Db::instance();
        $db->execute("UPDATE users SET last_login_at = NOW(), last_activity_at = NOW() WHERE id = ?", [$user['id']]);

        // Записуємо сесію
        $db->execute(
            "INSERT INTO sessions_meta (user_id, session_id_hash, ip_hash, user_agent, fingerprint, session_version, last_seen_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                $user['id'],
                hash('sha256', session_id()),
                ip_hash(),
                user_agent(),
                $_SESSION['fingerprint'],
                $user['session_version'],
            ]
        );

        self::logLogin((int)$user['id'], $user['login'], true, 'ok');
        self::$currentUser = null; // Скидаємо кеш
    }

    /**
     * Вихід.
     */
    public static function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $db = Db::instance();
            $db->execute("DELETE FROM sessions_meta WHERE session_id_hash = ?", [hash('sha256', session_id())]);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        self::$currentUser = null;
    }

    /**
     * Перевірка re-authentication (для чутливих дій).
     */
    public static function isReAuthenticated(): bool
    {
        return isset($_SESSION['reauth_at']) && (time() - $_SESSION['reauth_at']) < 300; // 5 хв
    }

    /**
     * Підтвердити re-auth.
     */
    public static function confirmReAuth(): void
    {
        $_SESSION['reauth_at'] = time();
    }

    /**
     * Імперсонація (для Owner).
     */
    public static function impersonate(int $userId): bool
    {
        if (!self::check()) return false;
        $currentUser = self::user();
        if (!$currentUser || $currentUser['role_slug'] !== 'owner') return false;
        if (!self::isReAuthenticated()) return false;

        $_SESSION['impersonator_id'] = self::id();
        $_SESSION['user_id'] = $userId;
        $_SESSION['session_version'] = 1; // Буде перевірятися
        self::$currentUser = null;

        Audit::log('impersonate_start', 'user', $userId, null);
        return true;
    }

    /**
     * Вийти з імперсонації.
     */
    public static function stopImpersonate(): void
    {
        if (!isset($_SESSION['impersonator_id'])) return;
        $originalId = $_SESSION['impersonator_id'];
        unset($_SESSION['impersonator_id']);
        $_SESSION['user_id'] = $originalId;
        self::$currentUser = null;
        Audit::log('impersonate_stop', 'user', $originalId, null);
    }

    /**
     * Чи в режимі імперсонації.
     */
    public static function isImpersonating(): bool
    {
        return isset($_SESSION['impersonator_id']);
    }

    /**
     * Відбиток сесії (UA + IP/24).
     */
    private static function makeFingerprint(): string
    {
        $ua = user_agent();
        $ip = client_ip();
        // IP/24 — беремо перші 3 октети
        $parts = explode('.', $ip);
        $ip24 = count($parts) >= 3 ? implode('.', array_slice($parts, 0, 3)) . '.0' : $ip;
        return hash('sha256', $ua . '|' . $ip24 . '|' . Config::get('ip_hmac_secret', ''));
    }

    /**
     * Збільшити лічильник невдалих спроб.
     */
    private static function incrementFailedAttempts(int $userId): void
    {
        $db = Db::instance();
        $db->execute(
            "UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?",
            [$userId]
        );
        $user = $db->fetchOne("SELECT failed_attempts FROM users WHERE id = ?", [$userId]);
        $attempts = (int)($user['failed_attempts'] ?? 0);

        if ($attempts >= 5) {
            // Експоненційне блокування: 15 хв, 30 хв, 1 год, 2 год, ... до 24 год
            $lockMinutes = min(15 * pow(2, $attempts - 5), 1440);
            $db->execute(
                "UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?",
                [(int)$lockMinutes, $userId]
            );
        }
    }

    /**
     * Записати вхід у лог.
     */
    private static function logLogin(?int $userId, string $login, bool $success, string $reason): void
    {
        $db = Db::instance();
        $db->execute(
            "INSERT INTO login_logs (user_id, login_attempted, ip_hash, user_agent, success, reason) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $login, ip_hash(), user_agent(), (int)$success, $reason]
        );
    }
}
