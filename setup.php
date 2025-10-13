<?php
// Database setup script
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'ncf_repository';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✅ Database '$dbname' created successfully!<br>";
    
    // Use the database
    $pdo->exec("USE $dbname");
    
    // Create files table
    $createTable = "
        CREATE TABLE IF NOT EXISTS files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_type VARCHAR(10) NOT NULL,
            file_size INT NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            description TEXT,
            INDEX idx_upload_date (upload_date),
            INDEX idx_file_type (file_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTable);
    echo "✅ Table 'files' created successfully!<br>";
    
    // Create events table for QR code management
    $createEventsTable = "
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_name VARCHAR(255) NOT NULL,
            event_code VARCHAR(50) UNIQUE NOT NULL,
            qr_url VARCHAR(500) NOT NULL,
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_is_active (is_active),
            INDEX idx_created_date (created_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createEventsTable);
    echo "✅ Table 'events' created successfully!<br>";
    
    // Insert default event if none exists
    $checkEvent = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($checkEvent == 0) {
        $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/client/";
        $pdo->exec("INSERT INTO events (event_name, event_code, qr_url) VALUES ('Default Event', 'default', '$baseUrl')");
        echo "✅ Default event created!<br>";
    }
    
    // Create uploads directory if it doesn't exist
    $uploadsDir = '../uploads/';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
        echo "✅ Uploads directory created successfully!<br>";
    } else {
        echo "✅ Uploads directory already exists!<br>";
    }
    
    // Create .htaccess file for uploads directory security
    $htaccessContent = "# Prevent PHP execution in uploads folder
<Files *.php>
    Order Deny,Allow
    Deny from all
</Files>

# Allow only specific file types
<FilesMatch \"\\.(pdf|jpg|jpeg|png|gif)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>";
    
    file_put_contents($uploadsDir . '.htaccess', $htaccessContent);
    echo "✅ Security .htaccess file created!<br>";
    
    echo "<br><h2>🎉 Setup Complete!</h2>";
    echo "<p>Your NCF Repository is ready to use:</p>";
    echo "<ul>";
    echo "<li><strong>Home:</strong> <a href='../'>../</a></li>";
    echo "<li><strong>Admin:</strong> <a href='../admin/'>../admin/</a> (admin/admin)</li>";
    echo "<li><strong>Client:</strong> <a href='../client/'>../client/</a></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCF Repository Setup Complete</title>
    <link rel="stylesheet" href="assets/css/corporate-style.css">
    <style>
        .setup-page {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .setup-container {
            max-width: 800px;
            margin: 2rem auto;
            background: var(--light-bg);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid var(--border-light);
            position: relative;
        }
        
        .setup-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-gold), var(--light-gold), var(--primary-gold));
            border-radius: 20px 20px 0 0;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .setup-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-gold), var(--light-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .setup-logs {
            background: var(--bg-color);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            color: var(--text-dark);
        }
        
        .completion-message {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
        }
        
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .link-card {
            background: var(--light-bg);
            border: 1px solid var(--border-gold);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-dark);
        }
        
        .link-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(184, 134, 11, 0.2);
            border-color: var(--primary-gold);
            text-decoration: none;
            color: var(--text-dark);
        }
        
        .link-card .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .link-card h3 {
            color: var(--primary-gold);
            margin-bottom: 0.5rem;
        }
        
        .link-card p {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .setup-container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .setup-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="setup-page">
    <div class="setup-container fade-in">
        <div class="setup-header">
            <div class="setup-title">🏗️ NCF Repository Setup</div>
            <p style="color: var(--text-light); font-size: 1.1rem;">Database Installation & Configuration</p>
        </div>
        
        <div class="setup-logs">
</body>
</html>