<?php
// Database migration script for multi-event system
require_once 'includes/config.php';

echo "<h1>NCF Repository - Multi-Event Database Migration</h1>";

try {
    // Check if event_id column exists in files table
    $stmt = $pdo->query("SHOW COLUMNS FROM files LIKE 'event_id'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "<p>🔧 Adding event_id column to files table...</p>";
        
        // Add event_id column
        $pdo->exec("ALTER TABLE files ADD COLUMN event_id INT DEFAULT 1");
        echo "<p>✅ event_id column added successfully!</p>";
        
        // Create default event if none exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM events");
        $eventCount = $stmt->fetchColumn();
        
        if ($eventCount == 0) {
            echo "<p>🎯 Creating default event...</p>";
            createNewEvent($pdo, 'Default Event', 'Default repository for file sharing');
            echo "<p>✅ Default event created!</p>";
        }
        
        // Update existing files to belong to the first event
        $stmt = $pdo->query("SELECT id FROM events ORDER BY created_date ASC LIMIT 1");
        $firstEvent = $stmt->fetch();
        
        if ($firstEvent) {
            $pdo->prepare("UPDATE files SET event_id = ? WHERE event_id IS NULL OR event_id = 0")
                ->execute([$firstEvent['id']]);
            echo "<p>✅ Existing files assigned to default event!</p>";
        }
        
        // Try to add foreign key constraint (may fail if referential integrity issues)
        try {
            $pdo->exec("ALTER TABLE files ADD CONSTRAINT fk_files_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE");
            echo "<p>✅ Foreign key constraint added!</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Warning: Could not add foreign key constraint: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>✅ Database already migrated - event_id column exists!</p>";
    }
    
    // Check and add description column to events table if needed
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'description'");
    $descColumnExists = $stmt->rowCount() > 0;
    
    if (!$descColumnExists) {
        echo "<p>🔧 Adding description column to events table...</p>";
        $pdo->exec("ALTER TABLE events ADD COLUMN description TEXT");
        echo "<p>✅ Description column added to events table!</p>";
    }
    
    // Check and add created_by column to events table if needed
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'created_by'");
    $createdByExists = $stmt->rowCount() > 0;
    
    if (!$createdByExists) {
        echo "<p>🔧 Adding created_by column to events table...</p>";
        $pdo->exec("ALTER TABLE events ADD COLUMN created_by VARCHAR(100) DEFAULT 'admin'");
        echo "<p>✅ Created_by column added to events table!</p>";
    }
    
    // Show current database status
    echo "<h2>📊 Current Database Status</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $eventCount = $stmt->fetchColumn();
    echo "<p><strong>Total Events:</strong> $eventCount</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM files");
    $fileCount = $stmt->fetchColumn();
    echo "<p><strong>Total Files:</strong> $fileCount</p>";
    
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
    echo "<p>Your NCF Repository is now ready for multi-event management!</p>";
    echo "<ul>";
    echo "<li>📱 <a href='admin/qr_manager.php'>Manage QR Codes</a></li>";
    echo "<li>🎯 <a href='admin/events_manager.php'>Manage Events</a></li>";
    echo "<li>📋 <a href='admin/dashboard.php'>Admin Dashboard</a></li>";
    echo "<li>👁️ <a href='client/'>View Client</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
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