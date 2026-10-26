<?php
declare(strict_types=1);
/**
 * CLI-самоперевірка: php tests/smoke.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo 'CLI only';
    exit;
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void {
    global $passed, $failed;
    try {
        $result = $fn();
        if ($result) {
            echo "  ✅ {$name}\n";
            $passed++;
        } else {
            echo "  ❌ {$name}\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo "  ❌ {$name}: {$e->getMessage()}\n";
        $failed++;
    }
}

echo "=== reNaI3es Smoke Tests ===\n\n";

// 1. Конфігурація
echo "Конфігурація:\n";
test('Config завантажено', fn() => is_array(\App\Core\Config::all()));
test('crypto_key встановлено', fn() => strlen(\App\Core\Config::get('crypto_key', '')) === 64);
test('ip_hmac_secret встановлено', fn() => strlen(\App\Core\Config::get('ip_hmac_secret', '')) > 0);

// 2. База даних
echo "\nБаза даних:\n";
test('PDO підключення', function() {
    $db = \App\Core\Db::instance();
    return $db->pdo() instanceof \PDO;
});
test('Таблиця roles існує', fn() => \App\Core\Db::instance()->fetchColumn("SELECT COUNT(*) FROM roles") > 0);
test('Таблиця users існує', fn() => \App\Core\Db::instance()->fetchColumn("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = 'users'", [\App\Core\Config::get('db.name')]) > 0);
test('Роль owner існує', fn() => \App\Core\Db::instance()->fetchColumn("SELECT COUNT(*) FROM roles WHERE slug = 'owner'") === 1);
test('Категорії завантажені', fn() => \App\Core\Db::instance()->fetchColumn("SELECT COUNT(*) FROM categories") >= 10);
test('Налаштування завантажені', fn() => \App\Core\Db::instance()->fetchColumn("SELECT COUNT(*) FROM settings") >= 20);

// 3. Криптографія
echo "\nКриптографія:\n";
test('Sodium доступний', fn() => function_exists('sodium_crypto_secretbox'));
test('Шифрування/розшифрування', function() {
    $plain = 'test_secret_' . random_bytes(8);
    $enc = \App\Core\Crypto::encrypt($plain);
    $dec = \App\Core\Crypto::decrypt($enc);
    return $dec === $plain;
});

// 4. TOTP
echo "\nTOTP:\n";
test('Генерація секрету', fn() => strlen(\App\Core\Totp::generateSecret()) > 10);
test('Генерація коду', function() {
    $secret = \App\Core\Totp::generateSecret();
    $code = \App\Core\Totp::generateCode($secret);
    return strlen($code) === 6 && ctype_digit($code);
});
test('Верифікація коду', function() {
    $secret = \App\Core\Totp::generateSecret();
    $code = \App\Core\Totp::generateCode($secret);
    return \App\Core\Totp::verify($secret, $code);
});

// 5. Хелпери
echo "\nХелпери:\n";
test('generate_public_id', fn() => strlen(generate_public_id()) === 12);
test('e() екранування', fn() => e('<script>') === '&lt;script&gt;');
test('url() з BASE_PATH', fn() => str_starts_with(url('/test'), BASE_PATH));
test('ip_hash() детермінований', fn() => ip_hash('1.2.3.4') === ip_hash('1.2.3.4'));

// 6. Файли
echo "\nФайли:\n";
test('schema.sql існує', fn() => is_file(dirname(__DIR__) . '/database/schema.sql'));
test('seed.sql існує', fn() => is_file(dirname(__DIR__) . '/database/seed.sql'));
test('public/index.php існує', fn() => is_file(dirname(__DIR__) . '/public/index.php'));
test('CSS існує', fn() => is_file(dirname(__DIR__) . '/public/assets/css/app.css'));
test('JS існує', fn() => is_file(dirname(__DIR__) . '/public/assets/js/app.js'));

// 7. JS ≤150 рядків
echo "\nJS ліміт:\n";
$jsLines = count(file(dirname(__DIR__) . '/public/assets/js/app.js'));
test("JS рядків: {$jsLines} (≤150)", fn() => $jsLines <= 150);

// Підсумок
echo "\n=== Результат ===\n";
echo "Пройдено: {$passed}\n";
echo "Провалено: {$failed}\n";
echo $failed === 0 ? "✅ Всі тести пройдені!\n" : "❌ Є провалені тести.\n";

exit($failed > 0 ? 1 : 0);
