<?php
/**
 * Database migrations (idempotent).
 *
 * CLI: php public/migrate.php
 * Web: https://your-domain/mcu-ticketing/public/migrate.php
 *
 * Catatan: URL ini bisa diakses siapa saja — pastikan server/firewall membatasi akses jika perlu.
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

require_once dirname(__DIR__) . '/config/autoload.php';

$db = (new Database())->getConnection();
if (!$db instanceof PDO) {
    echo "Database connection failed.\n";
    exit(1);
}

/**
 * @param callable(PDO): string $fn returns: applied | already_ok | skip_no_table | skip_reason
 */
function run_migration(PDO $db, string $name, callable $fn): void
{
    $check = $db->prepare('SELECT 1 FROM schema_migrations WHERE name = ?');
    $check->execute([$name]);
    if ($check->fetchColumn()) {
        echo "[skip recorded] $name\n";
        return;
    }

    $result = $fn($db);
    echo "[$result] $name\n";

    if ($result === 'skip_no_table') {
        return;
    }

    $ins = $db->prepare('INSERT INTO schema_migrations (name) VALUES (?)');
    $ins->execute([$name]);
}

function ensure_migrations_table(PDO $db): void
{
    $db->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(190) NOT NULL,
            applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_migration_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );
}

function table_exists(PDO $db, string $table): bool
{
    $q = $db->prepare(
        'SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = ?'
    );
    $q->execute([$table]);

    return (bool) $q->fetchColumn();
}

/** @return 'utf8mb4'|'utf8'|'other'|null */
function table_default_charset(PDO $db, string $table): ?string
{
    $q = $db->prepare(
        'SELECT CCSA.character_set_name
         FROM information_schema.TABLES T
         INNER JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY CCSA
            ON T.table_collation = CCSA.collation_name
         WHERE T.table_schema = DATABASE() AND T.table_name = ?'
    );
    $q->execute([$table]);
    $name = $q->fetchColumn();
    if ($name === false || $name === null) {
        return null;
    }
    $name = strtolower((string) $name);
    if ($name === 'utf8mb4') {
        return 'utf8mb4';
    }
    if ($name === 'utf8' || $name === 'utf8mb3') {
        return 'utf8';
    }

    return 'other';
}

try {
    ensure_migrations_table($db);

    run_migration($db, '001_utf8mb4_project_comments', static function (PDO $db): string {
        if (!table_exists($db, 'project_comments')) {
            return 'skip_no_table';
        }
        if (table_default_charset($db, 'project_comments') === 'utf8mb4') {
            return 'already_ok';
        }
        $db->exec(
            'ALTER TABLE project_comments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci'
        );

        return 'applied';
    });

    run_migration($db, '002_add_reject_reason_to_projects', static function (PDO $db): string {
        if (!table_exists($db, 'projects')) {
            return 'skip_no_table';
        }
        
        // Check if column exists
        $q = $db->prepare("SHOW COLUMNS FROM projects LIKE 'reject_reason'");
        $q->execute();
        if ($q->fetch()) {
            return 'already_ok';
        }

        $db->exec("ALTER TABLE projects ADD COLUMN reject_reason TEXT NULL AFTER status_project");
        return 'applied';
    });

    echo "\nDone.\n";
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
