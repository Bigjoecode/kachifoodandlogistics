<?php
/** Apply each production SQL migration exactly once. */

function apply_database_migrations(): void
{
    $pdo = Db::conn();
    try {
        $applied = $pdo->query('SELECT migration FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $error) {
        // Existing installations predate the migration ledger; create it once.
        if ($error->getCode() !== '42S02') {
            throw $error;
        }
        $pdo->exec(
            'CREATE TABLE schema_migrations (
                migration VARCHAR(190) PRIMARY KEY,
                applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $applied = [];
    }
    $known   = array_fill_keys($applied, true);
    $files   = glob(__DIR__ . '/migrations/*.sql') ?: [];
    sort($files, SORT_STRING);

    foreach ($files as $file) {
        $migration = basename($file);
        if (isset($known[$migration])) {
            continue;
        }

        $sql = trim((string) file_get_contents($file));
        $pdo->beginTransaction();
        try {
            if ($sql !== '') {
                $pdo->exec($sql);
            }
            $statement = $pdo->prepare('INSERT IGNORE INTO schema_migrations (migration) VALUES (?)');
            $statement->execute([$migration]);
            $pdo->commit();

            if (PHP_SAPI === 'cli') {
                echo "Applied {$migration}\n";
            }
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    }
}

// Keep direct web requests inert while retaining the useful CLI entry point.
$calledDirectly = realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__;
if ($calledDirectly) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(404);
        exit;
    }

    require dirname(__DIR__) . '/config/config.php';
    require dirname(__DIR__) . '/config/database.php';
    apply_database_migrations();
}
