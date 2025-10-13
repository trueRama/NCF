<?php
session_start();

echo "<h1>Login Debug Test</h1>";

if ($_POST) {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    echo "<p><strong>Username:</strong> '$username'</p>";
    echo "<p><strong>Password:</strong> '$password'</p>";
    echo "<p><strong>Username Length:</strong> " . strlen($username) . "</p>";
    echo "<p><strong>Password Length:</strong> " . strlen($password) . "</p>";
    
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        echo "<p style='color: green;'><strong>✅ Login should be successful!</strong></p>";
        echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Login failed!</strong></p>";
        echo "<p>Expected: username='admin', password='admin'</p>";
    }
}

echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>PHP Info:</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0; }
        input { padding: 8px; margin: 5px 0; width: 200px; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; }
    </style>
</head>
<body>
    <form method="POST">
        <h3>Test Login Form</h3>
        <div>
            <label>Username:</label><br>
            <input type="text" name="username" value="admin" required>
        </div>
        <div>
            <label>Password:</label><br>
            <input type="password" name="password" value="admin" required>
        </div>
        <div>
            <button type="submit">Test Login</button>
        </div>
    </form>
    
    <p><a href="index.php">Back to Main Login</a></p>
</body>
</html>