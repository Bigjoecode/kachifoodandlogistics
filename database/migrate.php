<?php
/** Apply each production SQL migration exactly once. */

/** Split SQL while preserving semicolons inside quoted strings. */
function migration_sql_statements(string $sql): array
{
    $statements = [];
    $current    = '';
    $inString   = false;
    $quote      = '';
    $length     = strlen($sql);

    for ($index = 0; $index < $length; $index++) {
        $character = $sql[$index];

        if ($inString) {
            $current .= $character;
            if ($character === '\\' && $index + 1 < $length) {
                $current .= $sql[++$index];
            } elseif ($character === $quote) {
                $inString = false;
            }
            continue;
        }

        if ($character === "'" || $character === '"') {
            $inString = true;
            $quote    = $character;
            $current .= $character;
            continue;
        }

        if (($character === '-' && substr($sql, $index, 3) === '-- ') || $character === '#') {
            while ($index < $length && $sql[$index] !== "\n") {
                $index++;
            }
            $current .= "\n";
            continue;
        }

        if ($character === ';') {
            if (trim($current) !== '') {
                $statements[] = trim($current);
            }
            $current = '';
            continue;
        }

        $current .= $character;
    }

    if (trim($current) !== '') {
        $statements[] = trim($current);
    }

    return $statements;
}

function migration_run_sql_file(PDO $pdo, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Cannot read {$path}");
    }

    foreach (migration_sql_statements($sql) as $statement) {
        $pdo->exec($statement);
    }
}

/** Initialize only a genuinely empty database; never overwrite live data. */
function bootstrap_empty_database(PDO $pdo): void
{
    $tables = $pdo->query(
        'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'
    )->fetchAll(PDO::FETCH_COLUMN);
    $applicationTables = array_values(array_diff($tables, ['schema_migrations']));

    if (in_array('settings', $tables, true)) {
        return;
    }

    if ($applicationTables !== []) {
        throw new RuntimeException('Database schema is incomplete; automatic initialization was refused.');
    }

    migration_run_sql_file($pdo, __DIR__ . '/schema.sql');
    migration_run_sql_file($pdo, __DIR__ . '/seed.sql');
}

function apply_database_migrations(): void
{
    $pdo = Db::conn();
    bootstrap_empty_database($pdo);

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
