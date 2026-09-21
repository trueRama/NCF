<?php
/**
 * Database connection, configuration and shared helpers.
 *
 * Credentials come from includes/config.local.php (gitignored) or from
 * environment variables. Nothing sensitive belongs in this file.
 */

require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/settings.php';

/**
 * Checks a login against the users table. Returns the account on success.
 *
 * Bumps last_login_at and silently upgrades the stored hash if PHP's default
 * cost has moved on since the password was set.
 */
function authenticateAdmin(PDO $pdo, string $username, string $password): ?array
{
    $stmt = $pdo->prepare('SELECT id, username, password_hash, full_name, role FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        // Spend comparable time on an unknown user so the response does not
        // reveal which usernames exist.
        password_verify($password, '$2y$10$usernamedoesnotexistpaddingpaddingpaddingpaddingpaddingpad');
        return null;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

    unset($user['password_hash']);
    return $user;
}

/** Warns in the dashboard while an account still uses a well-known password. */
function accountUsesWeakPassword(PDO $pdo, string $username): bool
{
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $hash = $stmt->fetchColumn();

    if (!$hash) {
        return false;
    }

    foreach (['admin', 'Admin123', 'password', 'admin123'] as $weak) {
        if (password_verify($weak, $hash)) {
            return true;
        }
    }

    return false;
}

/** Per-session token for state-changing admin requests. */
function csrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

$ncfSettings = ncfConfig();

// Never leak stack traces or credentials to a live page.
error_reporting(E_ALL);
ini_set('display_errors', isProduction() ? '0' : '1');
ini_set('log_errors', '1');

try {
    $pdo = new PDO(
        "mysql:host={$ncfSettings['db_host']};dbname={$ncfSettings['db_name']};charset=utf8mb4",
        $ncfSettings['db_user'],
        $ncfSettings['db_pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Auto-creating a database is a local convenience only; on a live host a
    // failure here means the credentials are wrong, so say so and stop.
    if (isProduction()) {
        error_log('NCF: database connection failed: ' . $e->getMessage());
        http_response_code(500);
        exit('The repository is temporarily unavailable. Please try again shortly.');
    }

    try {
        $pdo = new PDO(
            "mysql:host={$ncfSettings['db_host']};charset=utf8mb4",
            $ncfSettings['db_user'],
            $ncfSettings['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$ncfSettings['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$ncfSettings['db_name']}`");
        ensureSchema($pdo);
        $pdo->exec("INSERT IGNORE INTO events (event_name, event_code, qr_url) VALUES ('Default Event', 'default', '')");
    } catch (PDOException $inner) {
        http_response_code(500);
        die('Database unavailable. Run setup.php, or check includes/config.local.php.');
    }
}

// Helper functions
function formatFileSize($size) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, 2) . ' ' . $units[$i];
}

function isValidFileType($filename) {
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif' ];
    $file_extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
    return in_array( $file_extension, $allowed_extensions );
}

// Event and QR Code functions
function getCurrentEvent($pdo) {
    $stmt = $pdo->query("SELECT * FROM events WHERE is_active = 1 ORDER BY created_date DESC LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEventByCode($pdo, $eventCode) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_code = ? AND is_active = 1");
    $stmt->execute([$eventCode]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllActiveEvents($pdo) {
    $stmt = $pdo->query("SELECT * FROM events WHERE is_active = 1 ORDER BY created_date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventFiles($pdo, $eventId) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE event_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$eventId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Files in one of the three repositories. Passing $eventId scopes the result to
 * a single event; omitting it returns the category across every event, which is
 * what the landing page links to.
 */
function getCategoryFiles($pdo, $category, $eventId = null) {
    $category = sanitizeCategory($category);

    if ($eventId !== null) {
        $stmt = $pdo->prepare('SELECT * FROM files WHERE category = ? AND event_id = ? ORDER BY upload_date DESC');
        $stmt->execute([$category, $eventId]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM files WHERE category = ? ORDER BY upload_date DESC');
        $stmt->execute([$category]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** File count and total bytes per category, keyed by category. */
function getCategoryTotals($pdo, $eventId = null) {
    if ($eventId !== null) {
        $stmt = $pdo->prepare('SELECT category, COUNT(*) AS file_count, COALESCE(SUM(file_size), 0) AS total_size FROM files WHERE event_id = ? GROUP BY category');
        $stmt->execute([$eventId]);
    } else {
        $stmt = $pdo->query('SELECT category, COUNT(*) AS file_count, COALESCE(SUM(file_size), 0) AS total_size FROM files GROUP BY category');
    }

    $totals = [];
    foreach (categoryKeys() as $key) {
        $totals[$key] = ['file_count' => 0, 'total_size' => 0];
    }
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isValidCategory($row['category'])) {
            $totals[$row['category']] = [
                'file_count' => (int) $row['file_count'],
                'total_size' => (int) $row['total_size'],
            ];
        }
    }

    return $totals;
}

function createNewEvent($pdo, $eventName, $description = '') {
    // Create new event code
    $eventCode = strtolower(str_replace([' ', '-', '_'], '', $eventName)) . '_' . time();
    
    // Construct proper URL with event code
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $baseDir = dirname(dirname($_SERVER['REQUEST_URI']));
    $baseUrl = $protocol . $host . $baseDir . '/client/?event=' . $eventCode;

    // Insert new event (keep all events active - no deactivation)
    $stmt = $pdo->prepare('INSERT INTO events (event_name, event_code, qr_url, description, is_active) VALUES (?, ?, ?, ?, 1)');
    $stmt->execute([$eventName, $eventCode, $baseUrl, $description]);

    return $eventCode;
}

function toggleEventStatus($pdo, $eventId, $status) {
    $stmt = $pdo->prepare("UPDATE events SET is_active = ? WHERE id = ?");
    return $stmt->execute([$status, $eventId]);
}

function deleteEvent($pdo, $eventId) {
    // This will also delete associated files due to CASCADE
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    return $stmt->execute([$eventId]);
}

function generateQRCode($url, $size = 300) {
    // Ensure URL is properly encoded
    $encodedUrl = urlencode($url);
    
    // Use QR Server API with better parameters
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&format=png&ecc=M&margin=1&data=" . $encodedUrl;
    
    return $qrUrl;
}
?>