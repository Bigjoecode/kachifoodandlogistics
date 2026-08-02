<?php
/** Apply each production SQL migration exactly once. CLI only. */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/config/database.php';

$pdo = Db::conn();
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        migration VARCHAR(190) PRIMARY KEY,
        applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$applied = $pdo->query('SELECT migration FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
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
        $statement = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (?)');
        $statement->execute([$migration]);
        $pdo->commit();
        echo "Applied {$migration}\n";
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
