<?php
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Handle login
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NCF Repository</title>
    <link rel="stylesheet" href="../assets/css/corporate-style.css">
    <style>
        .login-page {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at center, rgba(184, 134, 11, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .login-container {
            background: var(--light-bg);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--border-light);
            position: relative;
            z-index: 1;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-gold), var(--light-gold), var(--primary-gold));
            border-radius: 20px 20px 0 0;
        }
        
        .login-logo {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-gold), var(--light-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: var(--dark-gold);
        }
        
        .credentials-hint {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-light);
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            background: rgba(184, 134, 11, 0.05);
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-gold);
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .login-logo {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container fade-in">
            <div class="header-logo">
                <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image">
                <span class="logo-text">NCF Repository</span>
            </div>
            <p class="login-subtitle">Ministry of Finance, Planning and Economic Development</p>
            <p class="login-subtitle">Administration Portal</p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">👤 Username:</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    🚀 Login to Dashboard
                </button>
            </form>
            
            <div class="back-link">
                <a href="../">← Back to Home</a>
            </div>
            
            <div class="credentials-hint">
                <strong>Default Credentials:</strong><br>
                Username: <code>admin</code> | Password: <code>admin</code>
            </div>
        </div>
    </div>
</body>
</html>