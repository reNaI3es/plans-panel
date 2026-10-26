<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Validator, Audit, RateLimit, Csrf, Totp, Config};

/**
 * Контролер аутентифікації: логін, 2FA, логаут, скидання пароля.
 */
class AuthController
{
    public function landing(): void
    {
        if (Auth::check()) {
            redirect(url('/dashboard'));
        }
        View::render('auth/landing', [], null);
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect(url('/dashboard'));
        }
        View::render('auth/login', [], null);
    }

    public function login(): void
    {
        if (!Csrf::csrf_check()) {
            http_response_code(403);
            flash('error', 'Недійсний токен. Спробуйте ще раз.');
            redirect(url('/login'));
        }

        $v = new Validator($_POST, [
            'login'    => 'required|string|min:3|max:32',
            'password' => 'required|string|min:6',
        ]);

        if (!$v->validate()) {
            flash('error', $v->firstError());
            redirect(url('/login'));
        }

        $result = Auth::attempt($_POST['login'], $_POST['password']);

        if ($result['success']) {
            Audit::log('login_success', 'user', Auth::id());
            redirect(url('/dashboard'));
        }

        if ($result['reason'] === 'need_2fa') {
            redirect(url('/2fa'));
        }

        if ($result['reason'] === 'rate_limit') {
            flash('error', 'Забагато спроб. Зачекайте кілька хвилин.');
        } elseif ($result['reason'] === 'locked') {
            flash('error', 'Обліковий запис заблоковано. Зверніться до Засновника.');
        } elseif ($result['reason'] === 'suspended') {
            flash('error', 'Обліковий запис призупинено.');
        } else {
            flash('error', 'Невірний логін або пароль.');
        }

        redirect(url('/login'));
    }

    public function show2fa(): void
    {
        if (!isset($_SESSION['pending_user_id'])) {
            redirect(url('/login'));
        }
        View::render('auth/2fa', [], null);
    }

    public function verify2fa(): void
    {
        if (!isset($_SESSION['pending_user_id'])) {
            redirect(url('/login'));
        }

        $code = $_POST['code'] ?? '';
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            flash('error', 'Введіть 6-значний код.');
            redirect(url('/2fa'));
        }

        $db = Db::instance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['pending_user_id']]);
        if (!$user || !$user['totp_secret_encrypted']) {
            flash('error', 'Помилка. Спробуйте увійти знову.');
            redirect(url('/login'));
        }

        try {
            $secret = \App\Core\Crypto::decrypt(bin2hex($user['totp_secret_encrypted']));
        } catch (\Throwable) {
            flash('error', 'Помилка шифрування. Зверніться до адміністратора.');
            redirect(url('/login'));
        }

        if (Totp::verify($secret, $code)) {
            Auth::complete2fa((int)$user['id']);
            redirect(url('/dashboard'));
        }

        // Перевірка резервного коду
        $backupCodes = $db->fetchAll(
            "SELECT * FROM user_backup_codes WHERE user_id = ? AND used_at IS NULL",
            [$user['id']]
        );
        foreach ($backupCodes as $bc) {
            if (password_verify($code, $bc['code_hash'])) {
                $db->execute("UPDATE user_backup_codes SET used_at = NOW() WHERE id = ?", [$bc['id']]);
                Auth::complete2fa((int)$user['id']);
                flash('success', 'Вхід за резервним кодом. Залишилось: ' . (count($backupCodes) - 1));
                redirect(url('/dashboard'));
            }
        }

        flash('error', 'Невірний код. Спробуйте ще раз.');
        redirect(url('/2fa'));
    }

    public function logout(): void
    {
        Audit::log('logout', 'user', Auth::id());
        Auth::logout();
        redirect(url('/login'));
    }

    public function showChangePassword(): void
    {
        View::render('auth/change-password', ['mustChange' => true], 'layouts/main');
    }

    public function changePassword(): void
    {
        $v = new Validator($_POST, [
            'password'         => 'required|string|min:10',
            'password_confirm' => 'required|string',
        ]);

        if (!$v->validate()) {
            flash('error', $v->firstError());
            redirect(url('/change-password'));
        }

        if ($_POST['password'] !== $_POST['password_confirm']) {
            flash('error', 'Паролі не збігаються.');
            redirect(url('/change-password'));
        }

        $db = Db::instance();
        $hash = password_hash($_POST['password'], PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2,
        ]);
        $db->execute(
            "UPDATE users SET password_hash = ?, must_change_password = 0, session_version = session_version + 1 WHERE id = ?",
            [$hash, Auth::id()]
        );

        Audit::log('password_changed', 'user', Auth::id());
        flash('success', 'Пароль змінено.');
        redirect(url('/dashboard'));
    }

    public function showPasswordReset(): void
    {
        View::render('auth/password-reset', [], null);
    }

    public function passwordReset(): void
    {
        // Однаковий час відповіді незалежно від існування акаунта
        flash('success', 'Якщо акаунт існує, посилання для скидання надіслано.');
        redirect(url('/login'));
    }
}
