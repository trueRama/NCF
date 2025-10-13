<?php
session_start();
require_once '../includes/config.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle file upload
if ($_POST && isset($_FILES['file'])) {
    $uploadDir = '../uploads/';
    $description = $_POST['description'] ?? '';
    
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $originalName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        
        if (isValidFileType($originalName)) {
            // Generate unique filename
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFilename = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO files (filename, original_name, file_type, file_size, description) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$newFilename, $originalName, $fileExtension, $fileSize, $description]);
                
                $success = "File uploaded successfully!";
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Invalid file type. Only PDF and image files are allowed.";
        }
    } else {
        $error = "Upload error: " . $_FILES['file']['error'];
    }
}

// Handle file deletion
if (isset($_GET['delete'])) {
    $fileId = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT filename FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();
    
    if ($file) {
        unlink('../uploads/' . $file['filename']);
        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $success = "File deleted successfully!";
    }
}

// Get all files
$stmt = $pdo->query("SELECT * FROM files ORDER BY upload_date DESC");
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NCF Repository</title>
    <link rel="stylesheet" href="../assets/css/corporate-style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <div class="header-logo">
                    <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image">
                    <span class="logo-text">NCF Repository</span>
                </div>
                <div class="subtitle">Ministry of Finance - Administration Dashboard</div>
            </div>
            <div class="user-menu">
                <a href="qr_manager.php">📱 QR Manager</a>
                <a href="../client/" target="_blank">👁️ View Client</a>
                <a href="?logout=1">🚪 Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>🔗 Quick Access</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Manage your event repository with these quick access tools
            </p>
            <div class="event-info">
                <p><strong>📡 Client Access URL:</strong></p>
                <div class="event-url">
                    <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])) . "/client/"; ?>
                </div>
            </div>
            <div style="margin-top: 1.5rem;">
                <a href="qr_manager.php" class="btn btn-primary">
                    📱 Manage QR Codes & Events
                </a>
                <a href="../client/" target="_blank" class="btn btn-secondary">
                    👁️ Preview Client View
                </a>
            </div>
        </div>
        
        <div class="card">
            <h2>📤 Upload New File</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Upload PDF documents and images to your event repository
            </p>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">📎 Select File (PDF or Images only):</label>
                    <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif" required>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Supported formats: PDF, JPG, JPEG, PNG, GIF
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="description">📝 Description (optional):</label>
                    <textarea id="description" name="description" placeholder="Enter a brief description of this file..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    🚀 Upload File
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2>📊 Repository Statistics</h2>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($files); ?></div>
                    <div class="stat-label">Total Files</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php 
                        $pdfCount = 0;
                        $totalSize = 0;
                        foreach($files as $file) {
                            if(strtolower($file['file_type']) === 'pdf') $pdfCount++;
                            $totalSize += $file['file_size'];
                        }
                        echo $pdfCount;
                    ?></div>
                    <div class="stat-label">PDF Documents</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($files) - $pdfCount; ?></div>
                    <div class="stat-label">Images</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo formatFileSize($totalSize); ?></div>
                    <div class="stat-label">Total Size</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>� File Management (<?php echo count($files); ?> files)</h2>
            
            <?php if (empty($files)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-light);">
                    <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">📂</div>
                    <h3>No Files Uploaded Yet</h3>
                    <p>Start by uploading your first file using the form above.</p>
                </div>
            <?php else: ?>
                <div class="file-grid">
                    <?php foreach ($files as $file): ?>
                        <div class="file-item">
                            <div class="file-icon">
                                <?php
                                $fileType = strtolower($file['file_type']);
                                if ($fileType === 'pdf') {
                                    echo '📄';
                                } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '🖼️';
                                } else {
                                    echo '📁';
                                }
                                ?>
                            </div>
                            
                            <div class="file-name"><?php echo htmlspecialchars($file['original_name']); ?></div>
                            
                            <div class="file-info">
                                <strong>Type:</strong> <?php echo strtoupper($file['file_type']); ?><br>
                                <strong>Size:</strong> <?php echo formatFileSize($file['file_size']); ?><br>
                                <strong>Uploaded:</strong> <?php echo date('M j, Y g:i A', strtotime($file['upload_date'])); ?>
                            </div>
                            
                            <div class="file-description">
                                <?php echo $file['description'] ? htmlspecialchars($file['description']) : '<em style="color: var(--text-muted);">No description provided</em>'; ?>
                            </div>
                            
                            <div class="file-actions">
                                <a href="../uploads/<?php echo $file['filename']; ?>" target="_blank" class="btn btn-info btn-small">
                                    👁️ View
                                </a>
                                <a href="../uploads/<?php echo $file['filename']; ?>" download="<?php echo $file['original_name']; ?>" class="btn btn-secondary btn-small">
                                    ⬇️ Download
                                </a>
                                <a href="?delete=<?php echo $file['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('⚠️ Are you sure you want to delete this file? This action cannot be undone.')">
                                    🗑️ Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Add smooth animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate file items on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });
            
            document.querySelectorAll('.file-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
                observer.observe(item);
            });
            
            // Add file upload progress indication
            const fileInput = document.getElementById('file');
            const form = fileInput.closest('form');
            
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<span class="loading"></span> Uploading...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>