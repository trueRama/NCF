<?php
/**
 * Single source of truth for the database shape.
 *
 * setup.php (fresh install), migrate.php (existing install) and config.php
 * (first-run fallback) all call ensureSchema() so the three can never drift.
 */

/** The three repositories the landing page links to. Keys are stored in files.category. */
const FILE_CATEGORIES = [
    'event_resources' => [
        'label' => 'Event Resources',
        'blurb' => 'Concept notes, programmes and delegate material for the Forum.',
        'icon'  => '📁',
    ],
    'reports' => [
        'label' => 'Reports',
        'blurb' => 'Outcome matrices, reform trackers and post-Forum reporting.',
        'icon'  => '📊',
    ],
    'commodity_profiles' => [
        'label' => 'Commodity Profiles',
        'blurb' => 'Value-chain profiles for the priority commodities under discussion.',
        'icon'  => '🌾',
    ],
];

const DEFAULT_CATEGORY = 'event_resources';

function categoryKeys(): array
{
    return array_keys(FILE_CATEGORIES);
}

function isValidCategory(?string $category): bool
{
    return $category !== null && array_key_exists($category, FILE_CATEGORIES);
}

function categoryLabel(?string $category): string
{
    return FILE_CATEGORIES[$category]['label'] ?? 'Uncategorised';
}

function categoryIcon(?string $category): string
{
    return FILE_CATEGORIES[$category]['icon'] ?? '📁';
}

/** Falls back to the default rather than trusting a value off the query string. */
function sanitizeCategory(?string $category): string
{
    return isValidCategory($category) ? $category : DEFAULT_CATEGORY;
}

/** The ENUM(...) fragment, generated from FILE_CATEGORIES so the two stay in step. */
function categoryEnumSql(): string
{
    $quoted = array_map(static fn($key) => "'" . $key . "'", categoryKeys());
    return 'ENUM(' . implode(', ', $quoted) . ") NOT NULL DEFAULT '" . DEFAULT_CATEGORY . "'";
}

/**
 * Creates the events and files tables when absent and brings an older install
 * up to date. Safe to run repeatedly.
 */
function ensureSchema(PDO $pdo): array
{
    $log = [];

    // events first: files carries a foreign key onto it.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_name VARCHAR(255) NOT NULL,
            event_code VARCHAR(50) UNIQUE NOT NULL,
            qr_url VARCHAR(500) NOT NULL,
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_active TINYINT(1) DEFAULT 1,
            description TEXT,
            created_by VARCHAR(100) DEFAULT 'admin',
            INDEX idx_is_active (is_active),
            INDEX idx_created_date (created_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $log[] = "Table 'events' is present.";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL DEFAULT 1,
            category " . categoryEnumSql() . ",
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_type VARCHAR(10) NOT NULL,
            file_size INT NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            description TEXT,
            INDEX idx_event_id (event_id),
            INDEX idx_category (category),
            INDEX idx_upload_date (upload_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $log[] = "Table 'files' is present.";

    $log = array_merge($log, migrateFilesCategory($pdo));
    $log = array_merge($log, ensureEventsColumns($pdo));
    $log = array_merge($log, ensureFilesForeignKey($pdo));
    $log = array_merge($log, ensureUsersTable($pdo));

    return $log;
}

/** Administrator accounts for the admin portal. */
function ensureUsersTable(PDO $pdo): array
{
    // The utf8mb4_unicode_ci collation makes usernames case-insensitive for
    // both lookup and uniqueness, so 'admin' and 'ADMIN' cannot both exist.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(64) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NULL,
            role ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    return ["Table 'users' is present."];
}

/**
 * Creates the first administrator if the table is empty. Never overwrites an
 * existing account — a password change must be deliberate, not a side effect
 * of running a migration.
 */
function seedAdminUser(PDO $pdo, string $username, string $passwordHash, ?string $fullName = null): array
{
    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
        return [];
    }

    if ($username === '' || $passwordHash === '') {
        return ['No administrator seeded: set admin_username and admin_password_hash first.'];
    }

    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $passwordHash, $fullName, 'admin']);

    return ["Administrator '$username' created."];
}

/** Adds files.category to installs that predate the three-repository split. */
function migrateFilesCategory(PDO $pdo): array
{
    $log = [];

    if (columnExists($pdo, 'files', 'category')) {
        // Widen the ENUM in case a category was added to FILE_CATEGORIES later.
        $pdo->exec('ALTER TABLE files MODIFY COLUMN category ' . categoryEnumSql());
        $log[] = "Column 'files.category' is up to date.";
        return $log;
    }

    $pdo->exec('ALTER TABLE files ADD COLUMN category ' . categoryEnumSql() . ' AFTER event_id');
    $log[] = "Column 'files.category' added; existing files defaulted to '" . DEFAULT_CATEGORY . "'.";

    if (!indexExists($pdo, 'files', 'idx_category')) {
        $pdo->exec('ALTER TABLE files ADD INDEX idx_category (category)');
        $log[] = "Index 'idx_category' added.";
    }

    return $log;
}

function ensureEventsColumns(PDO $pdo): array
{
    $log = [];

    foreach ([
        'description' => 'TEXT',
        'created_by'  => "VARCHAR(100) DEFAULT 'admin'",
    ] as $column => $definition) {
        if (!columnExists($pdo, 'events', $column)) {
            $pdo->exec("ALTER TABLE events ADD COLUMN $column $definition");
            $log[] = "Column 'events.$column' added.";
        }
    }

    return $log;
}

function ensureFilesForeignKey(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'files'
          AND CONSTRAINT_NAME = 'fk_files_event'
    ");

    if ((int) $stmt->fetchColumn() > 0) {
        return [];
    }

    try {
        $pdo->exec('ALTER TABLE files ADD CONSTRAINT fk_files_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE');
        return ['Foreign key fk_files_event added.'];
    } catch (PDOException $e) {
        // Orphaned rows block the constraint; the app works without it.
        return ['Foreign key fk_files_event skipped: ' . $e->getMessage()];
    }
}

// SHOW COLUMNS / SHOW INDEX reject placeholders under native prepared
// statements, so these quote the value instead. Inputs are internal constants,
// never request data.
function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($table));
    return $stmt->rowCount() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE " . $pdo->quote($column));
    return $stmt->rowCount() > 0;
}

function indexExists(PDO $pdo, string $table, string $index): bool
{
    $stmt = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = " . $pdo->quote($index));
    return $stmt->rowCount() > 0;
}
