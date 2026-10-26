<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Простий роутер: GET/POST + {id} плейсхолдери + middleware.
 */
final class Router
{
    /** @var array<string, array> Маршрути [method => [pattern => [handler, middleware[]]]] */
    private array $routes = [];

    /** @var array Глобальні middleware (виконуються для кожного запиту) */
    private array $globalMiddleware = [];

    /** @var array Поточні параметри маршруту */
    private array $params = [];

    /**
     * Додати маршрут.
     * @param string $method GET|POST|PUT|DELETE|ANY
     * @param string $pattern Шлях з {placeholder}
     * @param callable|array $handler [Controller::class, 'method'] або closure
     * @param array $middleware Масив middleware-класів або callable
     */
    public function addRoute(string $method, string $pattern, callable|array $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        if ($method === 'ANY') {
            foreach (['GET', 'POST', 'PUT', 'DELETE'] as $m) {
                $this->routes[$m][] = ['pattern' => $pattern, 'handler' => $handler, 'middleware' => $middleware];
            }
        } else {
            $this->routes[$method][] = ['pattern' => $pattern, 'handler' => $handler, 'middleware' => $middleware];
        }
    }

    public function get(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $middleware);
    }

    public function any(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('ANY', $pattern, $handler, $middleware);
    }

    /**
     * Додати глобальний middleware.
     */
    public function addGlobalMiddleware(callable|array $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

    /**
     * Запуск роутингу.
     */
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $uri = $this->parseUri();

        // Підтримка method override через _method в POST
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE', 'PATCH'])) {
                $method = $override;
            }
        }

        // Шукаємо відповідний маршрут
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $route) {
            $params = $this->match($route['pattern'], $uri);
            if ($params !== false) {
                $this->params = $params;

                // Виконуємо глобальні middleware
                foreach ($this->globalMiddleware as $mw) {
                    $this->callMiddleware($mw);
                }

                // Виконуємо middleware маршруту
                foreach ($route['middleware'] as $mw) {
                    $this->callMiddleware($mw);
                }

                // Викликаємо handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $method_name] = $handler;
                    $controller = new $class();
                    $controller->$method_name(...array_values($params));
                } else {
                    $handler(...array_values($params));
                }
                return;
            }
        }

        // Перевіримо чи є маршрут з іншим методом (для 405)
        foreach ($this->routes as $m => $mRoutes) {
            if ($m === $method) continue;
            foreach ($mRoutes as $route) {
                if ($this->match($route['pattern'], $uri) !== false) {
                    http_response_code(405);
                    header('Allow: ' . implode(', ', array_keys($this->routes)));
                    View::render('errors/405', [], 'layouts/error');
                    return;
                }
            }
        }

        // 404
        http_response_code(404);
        View::render('errors/404', [], 'layouts/error');
    }

    /**
     * Отримати параметри поточного маршруту.
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Парсинг URI (без query string, з урахуванням BASE_PATH).
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = str_replace('\\', '/', $uri);

        // Прибираємо BASE_PATH
        $base = BASE_PATH;
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . ltrim($uri, '/');
        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Порівняння URI з патерном маршруту.
     * @return array|false Параметри або false якщо не підходить.
     */
    private function match(string $pattern, string $uri): array|false
    {
        // Перетворюємо {param} в regex-групи
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }
        return false;
    }

    /**
     * Виклик middleware.
     */
    private function callMiddleware(callable|array $mw): void
    {
        if (is_array($mw)) {
            [$class, $method] = $mw;
            if (is_string($class)) {
                $instance = new $class();
                $instance->$method();
            } else {
                $class->$method();
            }
        } elseif (is_callable($mw)) {
            $mw();
        } elseif (is_string($mw) && class_exists($mw)) {
            $instance = new $mw();
            if (method_exists($instance, 'handle')) {
                $instance->handle();
            }
        }
    }
}
