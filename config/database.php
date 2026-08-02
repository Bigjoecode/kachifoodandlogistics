<?php
/**
 * PDO connection holder. One connection per request, lazily created.
 */
class Db
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                if (APP_DEBUG) {
                    die('<pre style="font:14px/1.6 monospace;padding:24px">Database connection failed: '
                        . htmlspecialchars($e->getMessage())
                        . "\n\nRun <b>install.php</b> to create the database, or check config/config.php.</pre>");
                }
                die('Service temporarily unavailable.');
            }
        }
        return self::$pdo;
    }

    /** Run a prepared statement and return the PDOStatement. */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** First matching row, or null. */
    public static function first(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** All matching rows. */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** Single scalar value from the first column. */
    public static function value(string $sql, array $params = [])
    {
        $val = self::run($sql, $params)->fetchColumn();
        return $val === false ? null : $val;
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::conn()->lastInsertId();
    }
}
