<?php
// Database configuration
//production
$host = 'localhost';
$dbname = 'u895763689_ncf';
$username = 'u895763689_ncf';
$password = '(Admin@2025)';
//development
// $host = 'localhost';
// $dbname = 'ncf_repository';
// $username = 'root';
// $password = '';

try {
    $pdo = new PDO( "mysql:host=$host;dbname=$dbname", $username, $password );
    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch( PDOException $e ) {
    // If database doesn't exist, create it
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $pdo->exec("USE $dbname");
        
        // Create files table with event association
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS files (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_id INT NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_type VARCHAR(10) NOT NULL,
                file_size INT NOT NULL,
                upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                description TEXT,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
            )
        ");
        
        // Create events table for multi-event management
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(255) NOT NULL,
                event_code VARCHAR(50) UNIQUE NOT NULL,
                qr_url VARCHAR(500) NOT NULL,
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active TINYINT(1) DEFAULT 1,
                description TEXT,
                created_by VARCHAR(100) DEFAULT 'admin'
            )
        ");
        
        // Add event_id column to existing files table if it doesn't exist
        $pdo->exec("
            ALTER TABLE files 
            ADD COLUMN IF NOT EXISTS event_id INT DEFAULT 1,
            ADD FOREIGN KEY IF NOT EXISTS (event_id) REFERENCES events(id) ON DELETE CASCADE
        ");
        
        // Insert default event if none exists
        $pdo->exec("
            INSERT IGNORE INTO events (event_name, event_code, qr_url) 
            VALUES ('Default Event', 'default', '')
        ");
        
        echo "Database and table created successfully!";
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
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