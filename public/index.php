<?php
declare(strict_types=1);
/**
 * Front controller — єдина точка входу для всіх запитів.
 * nginx має перенаправляти всі запити сюди (try_files $uri /index.php?$query_string).
 */

// Перевірка встановлення
$installedLock = dirname(__DIR__) . '/storage/installed.lock';
if (!is_file($installedLock)) {
    // Не встановлено — перенаправляємо на install.php
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base = rtrim($base, '/');
    header('Location: ' . $base . '/install.php');
    exit;
}

// Завантаження ядра
require_once dirname(__DIR__) . '/app/bootstrap.php';

// Ініціалізація сесії та аутентифікації
\App\Core\Auth::boot();

// Створюємо роутер
$router = new \App\Core\Router();

// --- Публічні маршрути (без авторизації) ---
$router->get('/', [\App\Controllers\AuthController::class, 'landing']);
$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/2fa', [\App\Controllers\AuthController::class, 'show2fa']);
$router->post('/2fa', [\App\Controllers\AuthController::class, 'verify2fa']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);
$router->get('/password-reset', [\App\Controllers\AuthController::class, 'showPasswordReset']);
$router->post('/password-reset', [\App\Controllers\AuthController::class, 'passwordReset']);
$router->get('/password-reset/confirm', [\App\Controllers\AuthController::class, 'showPasswordResetConfirm']);
$router->post('/password-reset/confirm', [\App\Controllers\AuthController::class, 'passwordResetConfirm']);

// --- Перевірка обов'язкової зміни пароля ---
$router->get('/change-password', [\App\Controllers\AuthController::class, 'showChangePassword']);
$router->post('/change-password', [\App\Controllers\AuthController::class, 'changePassword']);

// --- Middleware: авторизація для захищених маршрутів ---
$authMiddleware = [function(): void {
    if (!\App\Core\Auth::check()) {
        redirect(url('/login'));
    }
    // Перевірка обов'язкової зміни пароля
    $user = \App\Core\Auth::user();
    if ($user && $user['must_change_password'] && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/change-password')) {
        redirect(url('/change-password'));
    }
}];

// --- Dashboard ---
$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index'], $authMiddleware);

// --- Завдання ---
$router->get('/tasks', [\App\Controllers\TasksController::class, 'index'], $authMiddleware);
$router->get('/tasks/kanban', [\App\Controllers\TasksController::class, 'kanban'], $authMiddleware);
$router->get('/tasks/calendar', [\App\Controllers\TasksController::class, 'calendar'], $authMiddleware);
$router->get('/tasks/create', [\App\Controllers\TasksController::class, 'create'], $authMiddleware);
$router->post('/tasks/create', [\App\Controllers\TasksController::class, 'store'], $authMiddleware);
$router->get('/tasks/{id}', [\App\Controllers\TasksController::class, 'show'], $authMiddleware);
$router->get('/tasks/{id}/edit', [\App\Controllers\TasksController::class, 'edit'], $authMiddleware);
$router->post('/tasks/{id}/edit', [\App\Controllers\TasksController::class, 'update'], $authMiddleware);
$router->post('/tasks/{id}/status', [\App\Controllers\TasksController::class, 'changeStatus'], $authMiddleware);
$router->post('/tasks/{id}/comment', [\App\Controllers\TasksController::class, 'addComment'], $authMiddleware);

// --- Категорії ---
$router->get('/categories', [\App\Controllers\CategoriesController::class, 'index'], $authMiddleware);
$router->get('/categories/{id}', [\App\Controllers\CategoriesController::class, 'show'], $authMiddleware);

// --- Команда ---
$router->get('/team', [\App\Controllers\TeamController::class, 'index'], $authMiddleware);
$router->get('/team/matrix', [\App\Controllers\TeamController::class, 'matrix'], $authMiddleware);
$router->get('/team/{id}', [\App\Controllers\TeamController::class, 'show'], $authMiddleware);

// --- База знань ---
$router->get('/kb', [\App\Controllers\KbController::class, 'index'], $authMiddleware);
$router->get('/kb/search', [\App\Controllers\KbController::class, 'search'], $authMiddleware);
$router->get('/kb/article/{id}', [\App\Controllers\KbController::class, 'article'], $authMiddleware);
$router->get('/kb/links', [\App\Controllers\KbController::class, 'links'], $authMiddleware);

// --- Сервери ---
$router->get('/servers', [\App\Controllers\ServersController::class, 'index'], $authMiddleware);
$router->get('/servers/{id}', [\App\Controllers\ServersController::class, 'show'], $authMiddleware);

// --- ШІ-помічник ---
$router->get('/ai', [\App\Controllers\AiController::class, 'chat'], $authMiddleware);
$router->post('/ai/chat', [\App\Controllers\AiController::class, 'sendMessage'], $authMiddleware);

// --- Сповіщення ---
$router->get('/notifications', [\App\Controllers\NotificationsController::class, 'index'], $authMiddleware);
$router->post('/notifications/read', [\App\Controllers\NotificationsController::class, 'markRead'], $authMiddleware);

// --- Адмінка ---
$router->get('/admin/settings', [\App\Controllers\AdminController::class, 'settings'], $authMiddleware);
$router->post('/admin/settings', [\App\Controllers\AdminController::class, 'updateSettings'], $authMiddleware);
$router->get('/admin/users', [\App\Controllers\AdminController::class, 'users'], $authMiddleware);
$router->get('/admin/audit', [\App\Controllers\AdminController::class, 'audit'], $authMiddleware);
$router->get('/admin/logins', [\App\Controllers\AdminController::class, 'logins'], $authMiddleware);

// --- Файли ---
$router->get('/files/{token}', [\App\Controllers\FilesController::class, 'download'], $authMiddleware);

// Запуск
$router->dispatch();
