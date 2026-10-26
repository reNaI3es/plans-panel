<?php
declare(strict_types=1);
/**
 * Інсталятор — створює Owner-акаунт після першого запуску.
 * Після встановлення створює storage/installed.lock і більше не запускається.
 */

$installedLock = dirname(__DIR__) . '/storage/installed.lock';
if (is_file($installedLock)) {
    http_response_code(403);
    echo 'Система вже встановлена. Інсталятор заблоковано.';
    exit;
}

// Завантаження ядра
require_once dirname(__DIR__) . '/app/bootstrap.php';

$errors = [];
$success = false;
$generatedPassword = '';
$generatedLogin = '';

// Перевірка розширень PHP
$requiredExtensions = ['pdo_mysql', 'mbstring', 'json', 'curl', 'openssl', 'fileinfo'];
$optionalExtensions = ['sockets', 'sodium'];
$missingRequired = [];
$missingOptional = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) $missingRequired[] = $ext;
}
foreach ($optionalExtensions as $ext) {
    if (!extension_loaded($ext)) $missingOptional[] = $ext;
}

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? 'owner');
    $displayName = trim($_POST['display_name'] ?? 'Засновник');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // Валідація
    if (strlen($login) < 3 || strlen($login) > 32) {
        $errors[] = 'Логін має бути від 3 до 32 символів';
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
        $errors[] = 'Логін може містити лише латинські літери, цифри та підкреслення';
    }
    if (strlen($password) < 10) {
        $errors[] = 'Пароль має бути мінімум 10 символів';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Паролі не збігаються';
    }

    if (empty($errors)) {
        try {
            $db = \App\Core\Db::instance();

            // Перевірка чи є вже owner
            $existing = $db->fetchOne("SELECT id FROM users WHERE role_id = (SELECT id FROM roles WHERE slug = 'owner')");
            if ($existing) {
                $errors[] = 'Обліковий запис Owner вже існує';
            } else {
                // Створюємо Owner
                $publicId = generate_public_id();
                $passwordHash = password_hash($password, PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 2,
                ]);

                $ownerRole = $db->fetchOne("SELECT id FROM roles WHERE slug = 'owner'");

                $db->execute(
                    "INSERT INTO users (public_id, login, password_hash, display_name, avatar_initials, role_id, status, must_change_password, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'active', 0, NOW())",
                    [
                        $publicId,
                        $login,
                        $passwordHash,
                        $displayName,
                        mb_substr($displayName, 0, 3),
                        $ownerRole['id'],
                    ]
                );

                $ownerId = (int)$db->lastInsertId();

                // Оновлюємо author_id в статтях та посиланнях
                $db->execute("UPDATE kb_articles SET author_id = ? WHERE author_id = 1", [$ownerId]);
                $db->execute("UPDATE kb_links SET created_by = ? WHERE created_by = 1", [$ownerId]);
                $db->execute("UPDATE task_templates SET created_by = ? WHERE created_by = 1", [$ownerId]);

                // Створюємо lock-файл
                $storagePath = \App\Core\Config::get('storage_path', dirname(__DIR__) . '/storage');
                if (!is_dir($storagePath)) {
                    mkdir($storagePath, 0750, true);
                }
                file_put_contents($installedLock, date('Y-m-d H:i:s') . ' | Owner: ' . $login);
                @chmod($installedLock, 0600);

                $success = true;
                $generatedLogin = $login;
                $generatedPassword = '(ваш пароль)';
            }
        } catch (\Throwable $e) {
            $errors[] = 'Помилка: ' . $e->getMessage();
        }
    }
}

// Рендеринг
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення — reNaI3es</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background: #0f1419; color: #e1e8ed; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { max-width: 480px; width: 100%; padding: 2rem; }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; color: #fff; }
        p { color: #8899a6; margin-bottom: 1.5rem; line-height: 1.5; }
        .card { background: #1a2332; border: 1px solid #2a3a4a; border-radius: 12px; padding: 2rem; }
        label { display: block; font-size: 0.875rem; color: #8899a6; margin-bottom: 0.25rem; }
        input { width: 100%; padding: 0.75rem; background: #0f1419; border: 1px solid #2a3a4a; border-radius: 8px; color: #e1e8ed; font-size: 1rem; margin-bottom: 1rem; }
        input:focus { outline: none; border-color: #4a9eff; }
        button { width: 100%; padding: 0.75rem; background: #4a9eff; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; }
        button:hover { background: #3a8eef; }
        .error { background: #2a1a1a; border: 1px solid #5a2a2a; color: #ff6b6b; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
        .success { background: #1a2a1a; border: 1px solid #2a5a2a; color: #6bff6b; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
        .warning { background: #2a2a1a; border: 1px solid #5a5a2a; color: #ffcc6b; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
        .ext-list { font-size: 0.75rem; color: #6b7b8b; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Встановлення reNaI3es</h1>
        <p>Перший запуск системи. Створіть обліковий запис Засновника.</p>

        <?php if ($missingRequired): ?>
            <div class="error">
                <strong>Відсутні обов'язкові розширення PHP:</strong><br>
                <?= implode(', ', array_map('e', $missingRequired)) ?>
            </div>
        <?php endif; ?>

        <?php if ($missingOptional): ?>
            <div class="warning">
                <strong>Необов'язкові розширення (м'яка деградація):</strong><br>
                <?= implode(', ', array_map('e', $missingOptional)) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                <strong>✅ Встановлення завершено!</strong><br><br>
                Логін: <strong><?= e($generatedLogin) ?></strong><br>
                Пароль: <strong>(той, що ви задали)</strong><br><br>
                <a href="<?= url('/login') ?>" style="color: #6bff6b;">Перейти до входу →</a><br><br>
                <small>Рекомендуємо видалити файл install.php з сервера.</small>
            </div>
        <?php else: ?>
            <div class="card">
                <?php foreach ($errors as $error): ?>
                    <div class="error"><?= e($error) ?></div>
                <?php endforeach; ?>

                <form method="POST">
                    <label for="login">Логін</label>
                    <input type="text" id="login" name="login" value="<?= e($_POST['login'] ?? 'owner') ?>" required autocomplete="username">

                    <label for="display_name">Відображуване ім'я</label>
                    <input type="text" id="display_name" name="display_name" value="<?= e($_POST['display_name'] ?? 'Засновник') ?>" required>

                    <label for="password">Пароль (мін. 10 символів)</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password" minlength="10">

                    <label for="password_confirm">Підтвердження пароля</label>
                    <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">

                    <button type="submit">Створити обліковий запис</button>
                </form>

                <div class="ext-list">
                    PHP <?= PHP_VERSION ?> |
                    Розширення: <?= implode(', ', array_map(fn($e) => extension_loaded($e) ? "✅$e" : "❌$e", array_merge($requiredExtensions, $optionalExtensions))) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
