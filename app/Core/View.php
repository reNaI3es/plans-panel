<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Шаблонізатор: рендер з layout, секціями, авто-екрануванням.
 */
final class View
{
    private static array $sections = [];
    private static ?string $currentSection = null;
    private static array $sectionStack = [];

    /**
     * Рендер шаблону з layout.
     * @param string $view Шлях до шаблону відносно views/ (без .php)
     * @param array $data Дані для шаблону
     * @param string|null $layout Шлях до layout (без .php), null = без layout
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewFile = self::findView($view);
        if (!$viewFile) {
            throw new \RuntimeException("Шаблон '{$view}' не знайдено");
        }

        // Рендеримо контент view
        self::$sections = [];
        $content = self::renderFile($viewFile, $data);

        // Якщо є layout — рендеримо його з контентом
        if ($layout) {
            $layoutFile = self::findView($layout);
            if (!$layoutFile) {
                throw new \RuntimeException("Layout '{$layout}' не знайдено");
            }
            self::$sections['content'] = $content;
            echo self::renderFile($layoutFile, $data);
        } else {
            echo $content;
        }
    }

    /**
     * Рендер часткового шаблону (partial) без layout.
     */
    public static function partial(string $view, array $data = []): string
    {
        $viewFile = self::findView($view);
        if (!$viewFile) {
            return "<!-- partial '{$view}' not found -->";
        }
        return self::renderFile($viewFile, $data);
    }

    /**
     * Початок секції.
     */
    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        self::$sectionStack[] = $name;
        ob_start();
    }

    /**
     * Кінець секції.
     */
    public static function endSection(): void
    {
        $content = ob_get_clean();
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = $content;
            self::$currentSection = null;
            array_pop(self::$sectionStack);
        }
    }

    /**
     * Вивести вміст секції.
     */
    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * "Сирый" HTML (для довіреного контенту, наприклад, Markdown-рендер).
     */
    public static function raw(string $html): string
    {
        return $html;
    }

    /**
     * Рендер файлу з ізоляцією змінних.
     */
    private static function renderFile(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            if (Config::get('debug', false)) {
                throw $e;
            }
            error_log('View error: ' . $e->getMessage());
            return '<!-- Помилка рендерингу -->';
        }
        return ob_get_clean();
    }

    /**
     * Пошук файлу шаблону.
     */
    private static function findView(string $view): ?string
    {
        $viewsPath = Config::get('views_path', dirname(__DIR__, 1) . '/views');
        $file = $viewsPath . '/' . $view . '.php';
        return is_file($file) ? $file : null;
    }
}
