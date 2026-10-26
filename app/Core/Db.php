<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Singleton PDO-обгортка. Підраховує запити, логує повільні (>100 мс).
 */
final class Db
{
    private static ?self $instance = null;
    private \PDO $pdo;
    private int $queryCount = 0;
    private float $totalTime = 0.0;

    private function __construct()
    {
        $host    = Config::get('db.host', '127.0.0.1');
        $port    = Config::get('db.port', 3306);
        $name    = Config::get('db.name', 'renai3es');
        $user    = Config::get('db.user', 'root');
        $pass    = Config::get('db.pass', '');
        $charset = Config::get('db.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
        ];

        $this->pdo = new \PDO($dsn, $user, $pass, $options);
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function pdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Виконати запит з параметрами і повернути всі рядки.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll();
    }

    /**
     * Виконати запит і повернути один рядок.
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->execute($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Виконати запит і повернути одне значення (перша колонка першого рядка).
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return $this->execute($sql, $params)->fetchColumn($column);
    }

    /**
     * Виконати INSERT/UPDATE/DELETE, повернути кількість змінених рядків.
     */
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        $start = microtime(true);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $elapsed = microtime(true) - $start;

        $this->queryCount++;
        $this->totalTime += $elapsed;

        // Лог повільних запитів
        if ($elapsed > 0.1) {
            $this->logSlow($sql, $params, $elapsed);
        }

        return $stmt;
    }

    /**
     * Останній INSERT ID.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Кількість виконаних запитів.
     */
    public function queryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Загальний час виконання запитів.
     */
    public function totalTime(): float
    {
        return $this->totalTime;
    }

    /**
     * Транзакція: begin.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Транзакція: commit.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Транзакція: rollback.
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Записати повільний запит у лог.
     */
    private function logSlow(string $sql, array $params, float $elapsed): void
    {
        $storagePath = Config::get('storage_path', dirname(__DIR__, 1) . '/storage');
        $logDir = $storagePath . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }
        $logFile = $logDir . '/slow.log';
        $line = sprintf(
            "[%s] %.3fms | SQL: %s | Params: %s\n",
            date('Y-m-d H:i:s'),
            $elapsed * 1000,
            str_replace(["\n", "\r"], ' ', $sql),
            json_encode($params, JSON_UNESCAPED_UNICODE)
        );
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
