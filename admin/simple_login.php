<?php
session_start();

// Simple login test
if ($_POST) {
    if ($_POST['username'] == 'admin' && $_POST['password'] == 'admin') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Login Test</title>
</head>
<body>
    <h1>Simple Login Test</h1>
    
    <?php if (isset($_SESSION['admin_logged_in'])): ?>
        <p style="color: green;">You are logged in! <a href="dashboard.php">Go to Dashboard</a></p>
    <?php endif; ?>
    
    <form method="POST">
        <p>Username: <input type="text" name="username" value="admin"></p>
        <p>Password: <input type="password" name="password" value="admin"></p>
        <p><button type="submit">Login</button></p>
    </form>
    
    <h3>Debug Info:</h3>
    <pre><?php print_r($_SESSION); ?></pre>
    <pre><?php print_r($_POST); ?></pre>
</body>
</html>