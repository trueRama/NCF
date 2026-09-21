<?php
// Fresh-install script. Creates the database, applies the schema and secures uploads/.
require_once __DIR__ . '/includes/schema.php';
require_once __DIR__ . '/includes/settings.php';

$settings = [
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_name' => getenv('DB_NAME') ?: 'ncf_repository',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
];

$localFile = __DIR__ . '/includes/config.local.php';
if (is_readable($localFile)) {
    $loaded = require $localFile;
    if (is_array($loaded)) {
        $settings = array_merge($settings, array_intersect_key($loaded, $settings));
    }
}

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host={$settings['db_host']};charset=utf8mb4", $settings['db_user'], $settings['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$settings['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '{$settings['db_name']}' created successfully!<br>";

    // Use the database
    $pdo->exec("USE `{$settings['db_name']}`");

    foreach (ensureSchema($pdo) as $line) {
        echo "✅ " . htmlspecialchars($line) . "<br>";
    }

    $adminSettings = ncfConfig();
    foreach (seedAdminUser($pdo, $adminSettings['admin_username'], $adminSettings['admin_password_hash']) as $line) {
        echo "✅ " . htmlspecialchars($line) . "<br>";
    }

    // Insert default event if none exists
    $checkEvent = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($checkEvent == 0) {
        $baseUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['REQUEST_URI'] ?? '') . "/client/";
        $stmt = $pdo->prepare("INSERT INTO events (event_name, event_code, qr_url) VALUES ('Default Event', 'default', ?)");
        $stmt->execute([$baseUrl]);
        echo "✅ Default event created!<br>";
    }

    // Create uploads directory if it doesn't exist
    $uploadsDir = __DIR__ . '/uploads/';
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

    if (!is_readable($localFile)) {
        echo "⚠️ No includes/config.local.php found — copy includes/config.local.example.php and set your credentials.<br>";
    }

    echo "<br><h2>🎉 Setup Complete!</h2>";
    echo "<p>Your NCF Repository is ready to use:</p>";
    echo "<ul>";
    echo "<li><strong>Home:</strong> <a href='./'>./</a></li>";
    echo "<li><strong>Admin:</strong> <a href='admin/'>admin/</a></li>";
    echo "<li><strong>Client:</strong> <a href='client/'>client/</a></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
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