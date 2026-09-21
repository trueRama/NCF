<?php
// Test session functionality
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .test { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>NCF Repository - Session Test</h1>
    
    <div class="test">
        <h3>PHP Configuration</h3>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Session Status:</strong> <?php echo session_status(); ?> (1=disabled, 2=active)</p>
        <p><strong>Session Save Path:</strong> <?php echo session_save_path(); ?></p>
    </div>
    
    <?php
    // Test session writing
    if (isset($_POST['test_session'])) {
        $_SESSION['test_data'] = 'Session is working!';
        $_SESSION['timestamp'] = time();
        echo '<div class="test success"><h3>✅ Session Write Test</h3><p>Session data written successfully!</p></div>';
    }
    
    // Test session reading
    if (isset($_SESSION['test_data'])) {
        echo '<div class="test success"><h3>✅ Session Read Test</h3>';
        echo '<p>Test Data: ' . $_SESSION['test_data'] . '</p>';
        echo '<p>Timestamp: ' . date('Y-m-d H:i:s', $_SESSION['timestamp']) . '</p>';
        echo '</div>';
    }
    
    // Test login simulation
    if (isset($_POST['test_login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if ($username === 'admin' && $password === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            echo '<div class="test success"><h3>✅ Login Test Successful</h3>';
            echo '<p>Admin login session set successfully!</p>';
            echo '<p><a href="dashboard.php">Try accessing dashboard</a></p>';
            echo '</div>';
        } else {
            echo '<div class="test error"><h3>❌ Login Test Failed</h3>';
            echo '<p>Invalid credentials provided.</p>';
            echo '</div>';
        }
    }
    ?>
    
    <div class="test">
        <h3>Current Session Data</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div class="test">
        <h3>Session Tests</h3>
        
        <form method="POST" style="margin-bottom: 15px;">
            <button type="submit" name="test_session">Test Session Write/Read</button>
        </form>
        
        <form method="POST">
            <h4>Login Test</h4>
            <p>
                Username: <input type="text" name="username" value="admin" required>
                Password: <input type="password" name="password" value="admin" required>
                <button type="submit" name="test_login">Test Login</button>
            </p>
        </form>
    </div>
    
    <div class="test">
        <h3>Navigation</h3>
        <p>
            <a href="index.php">← Back to Main Login</a> |
            <a href="simple_login.php">Simple Login Test</a> |
            <a href="dashboard.php">Try Dashboard</a>
        </p>
    </div>
    
</body>
</html>