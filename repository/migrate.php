<?php
// Brings an existing install up to the current schema, including files.category.
session_start();
require_once 'includes/config.php';

$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Before the users table exists nobody can log in, so the very first run on a
// fresh install is allowed through. After that an admin session is required.
$isBootstrap = !$isAdmin && !tableExists($pdo, 'users');

if (!$isAdmin && !$isBootstrap) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Sign in to the admin portal first, then reload this page.\n");
}

echo "<h1>NCF Repository - Database Migration</h1>";

try {
    echo "<h2>🔧 Applying schema</h2>";
    foreach (ensureSchema($pdo) as $line) {
        echo "<p>✅ " . htmlspecialchars($line) . "</p>";
    }

    $settings = ncfConfig();
    foreach (seedAdminUser($pdo, $settings['admin_username'], $settings['admin_password_hash']) as $line) {
        echo "<p>✅ " . htmlspecialchars($line) . "</p>";
    }

    if ($isBootstrap) {
        echo "<h2>🎉 Schema created</h2>";
        echo "<p>Now sign in at <a href='admin/'>the admin portal</a>. Reload this page while signed in to see the full status report.</p>";
        return;
    }

    $eventCount = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($eventCount === 0) {
        createNewEvent($pdo, 'Default Event', 'Default repository for file sharing');
        echo "<p>✅ Default event created!</p>";
    }

    // Any file left without an event lands on the oldest one.
    $firstEvent = $pdo->query("SELECT id FROM events ORDER BY created_date ASC LIMIT 1")->fetch();
    if ($firstEvent) {
        $pdo->prepare("UPDATE files SET event_id = ? WHERE event_id IS NULL OR event_id = 0")
            ->execute([$firstEvent['id']]);
    }

    // Show current database status
    echo "<h2>📊 Current Database Status</h2>";

    $eventCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    echo "<p><strong>Total Events:</strong> $eventCount</p>";

    $fileCount = $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
    echo "<p><strong>Total Files:</strong> $fileCount</p>";

    echo "<h3>🗂️ Repositories:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f5f5f5;'><th>Repository</th><th>Key</th><th>Files</th><th>Size</th></tr>";
    foreach (getCategoryTotals($pdo) as $key => $totals) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars(categoryLabel($key)) . "</td>";
        echo "<td><code>" . htmlspecialchars($key) . "</code></td>";
        echo "<td>" . $totals['file_count'] . "</td>";
        echo "<td>" . formatFileSize($totals['total_size']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    $stmt = $pdo->query("SELECT e.event_name, e.event_code, e.is_active, COUNT(f.id) as file_count 
                         FROM events e 
                         LEFT JOIN files f ON e.id = f.event_id 
                         GROUP BY e.id 
                         ORDER BY e.created_date DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>🎯 Events Overview:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f5f5f5;'><th>Event Name</th><th>Code</th><th>Status</th><th>Files</th></tr>";

    foreach ($events as $event) {
        $status = $event['is_active'] ? '<span style="color: green;">ACTIVE</span>' : '<span style="color: red;">INACTIVE</span>';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($event['event_name']) . "</td>";
        echo "<td>" . htmlspecialchars($event['event_code']) . "</td>";
        echo "<td>$status</td>";
        echo "<td>" . $event['file_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>🎉 Migration Complete!</h2>";
    echo "<ul>";
    echo "<li>📱 <a href='admin/qr_manager.php'>Manage QR Codes</a></li>";
    echo "<li>🎯 <a href='admin/events_manager.php'>Manage Events</a></li>";
    echo "<li>📋 <a href='admin/dashboard.php'>Admin Dashboard</a></li>";
    echo "<li>👁️ <a href='client/'>View Client</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1 { color: #B8860B; }
h2 { color: #333; border-bottom: 2px solid #B8860B; padding-bottom: 5px; }
table { margin: 10px 0; }
th, td { padding: 8px 12px; text-align: left; }
th { background: #B8860B; color: white; }
</style>