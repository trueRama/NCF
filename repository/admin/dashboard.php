<?php
session_start();
require_once '../includes/config.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$csrfToken = csrfToken();

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
    $eventId = $_POST['event_id'] ?? null;
    $category = $_POST['category'] ?? '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = "Your session expired. Please try the upload again.";
    } elseif (!$eventId) {
        $error = "Please select an event for the file upload.";
    } elseif (!isValidCategory($category)) {
        $error = "Please choose which repository this file belongs to.";
    } elseif ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $originalName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        
        if (isValidFileType($originalName)) {
            // Generate unique filename
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $newFilename = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Save to database with event and repository association
                $stmt = $pdo->prepare("INSERT INTO files (event_id, category, filename, original_name, file_type, file_size, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$eventId, $category, $newFilename, $originalName, $fileExtension, $fileSize, $description]);

                $success = "File uploaded to " . categoryLabel($category) . "!";
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
    if (!verifyCsrfToken($_GET['token'] ?? null)) {
        $error = "Invalid or expired delete request.";
    } else {
        $fileId = $_GET['delete'];
        $stmt = $pdo->prepare("SELECT filename FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();

        if ($file) {
            $path = '../uploads/' . basename($file['filename']);
            if (is_file($path)) {
                unlink($path);
            }
            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$fileId]);
            $success = "File deleted successfully!";
        }
    }
}

// Handle moving a file between repositories
if ($_POST && isset($_POST['recategorize_id'])) {
    $newCategory = $_POST['category'] ?? '';
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = "Your session expired. Please try again.";
    } elseif (!isValidCategory($newCategory)) {
        $error = "Unknown repository.";
    } else {
        $stmt = $pdo->prepare("UPDATE files SET category = ? WHERE id = ?");
        $stmt->execute([$newCategory, $_POST['recategorize_id']]);
        $success = "File moved to " . categoryLabel($newCategory) . ".";
    }
}

// Get all active events for the dropdown
$allActiveEvents = getAllActiveEvents($pdo);

// Get selected event or default to first active event
$selectedEventId = $_GET['event_id'] ?? ($_POST['event_id'] ?? null);
if (!$selectedEventId && !empty($allActiveEvents)) {
    $selectedEventId = $allActiveEvents[0]['id'];
}

// Empty string means "every repository".
$filterCategory = $_GET['category'] ?? '';
if ($filterCategory !== '' && !isValidCategory($filterCategory)) {
    $filterCategory = '';
}

// Get files for the selected event, optionally narrowed to one repository
$files = [];
if ($selectedEventId) {
    if ($filterCategory !== '') {
        $files = getCategoryFiles($pdo, $filterCategory, $selectedEventId);
    } else {
        $files = getEventFiles($pdo, $selectedEventId);
    }

    // Get selected event details
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$selectedEventId]);
    $selectedEvent = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $selectedEvent = null;
}

$categoryTotals = getCategoryTotals($pdo, $selectedEventId);
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
                    <a href="<?php echo htmlspecialchars(landingPageUrl()); ?>" class="logo-link" title="Back to the Forum home page">
                        <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image">
                        <span class="logo-text">NCF Repository</span>
                    </a>
                </div>
                <div class="subtitle">Ministry of Finance - Administration Dashboard</div>
            </div>
            <div class="user-menu">
                <a href="qr_manager.php">📱 QR Manager</a>
                <a href="events_manager.php">🎯 Events Manager</a>
                <a href="../client/" target="_blank">👁️ View Client</a>
                <a href="?logout=1">🚪 Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (accountUsesWeakPassword($pdo, $_SESSION['admin_username'] ?? '')): ?>
            <div class="alert alert-error">
                ⚠️ The <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?></strong>
                account is using a well-known password. Change it before the site goes live.
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
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
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label for="event_id">🎯 Select Event:</label>
                    <select id="event_id" name="event_id" required>
                        <?php if (empty($allActiveEvents)): ?>
                            <option value="">No active events - Create one in QR Manager</option>
                        <?php else: ?>
                            <option value="">Choose an event...</option>
                            <?php foreach ($allActiveEvents as $event): ?>
                                <option value="<?php echo $event['id']; ?>" <?php echo ($selectedEventId == $event['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($event['event_name']); ?> (<?php echo htmlspecialchars($event['event_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Files will be uploaded to the selected event repository
                    </small>
                </div>

                <div class="form-group">
                    <label for="category">🗂️ Repository:</label>
                    <select id="category" name="category" required>
                        <option value="">Choose a repository...</option>
                        <?php foreach (FILE_CATEGORIES as $key => $meta): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($filterCategory === $key) ? 'selected' : ''; ?>>
                                <?php echo $meta['icon'] . ' ' . htmlspecialchars($meta['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Determines which card on the landing page this file appears under
                    </small>
                </div>
                
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
            <h2>🗂️ Repositories</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                The three repositories the landing page links to<?php echo $selectedEvent ? ', for ' . htmlspecialchars($selectedEvent['event_name']) : ''; ?>
            </p>
            <div class="stats">
                <?php foreach (FILE_CATEGORIES as $key => $meta): ?>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $categoryTotals[$key]['file_count']; ?></div>
                        <div class="stat-label"><?php echo $meta['icon'] . ' ' . htmlspecialchars($meta['label']); ?></div>
                        <div class="stat-label" style="font-size: 0.8rem; opacity: 0.7;">
                            <?php echo formatFileSize($categoryTotals[$key]['total_size']); ?>
                        </div>
                        <p style="margin-top: 0.75rem;">
                            <a href="../client/?category=<?php echo urlencode($key); ?>" target="_blank" class="btn btn-info btn-small">👁️ View</a>
                            <a href="../download_all.php?category=<?php echo urlencode($key); ?>" class="btn btn-secondary btn-small">⬇️ Zip</a>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h2>🎯 Filter Files</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Narrow the file list below by event and repository
            </p>
            
            <form method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label for="filter_event_id">🎯 Select Event:</label>
                    <select id="filter_event_id" name="event_id" onchange="this.form.submit()">
                        <option value="">All Events</option>
                        <?php foreach ($allActiveEvents as $event): ?>
                            <option value="<?php echo $event['id']; ?>" <?php echo ($selectedEventId == $event['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['event_name']); ?> (<?php echo htmlspecialchars($event['event_code']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label for="filter_category">🗂️ Repository:</label>
                    <select id="filter_category" name="category" onchange="this.form.submit()">
                        <option value="">All Repositories</option>
                        <?php foreach (FILE_CATEGORIES as $key => $meta): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($filterCategory === $key) ? 'selected' : ''; ?>>
                                <?php echo $meta['icon'] . ' ' . htmlspecialchars($meta['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary">🔍 Filter</button>
                </div>
            </form>
            
            <?php if ($selectedEvent): ?>
                <div class="event-info" style="margin-top: 1.5rem; padding: 1rem; background: var(--light-bg); border-radius: 8px; border-left: 4px solid var(--primary-gold);">
                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">📊 Current Event: <?php echo htmlspecialchars($selectedEvent['event_name']); ?></h4>
                    <p style="margin: 0.25rem 0; color: var(--text-light);"><strong>Code:</strong> <?php echo htmlspecialchars($selectedEvent['event_code']); ?></p>
                    <p style="margin: 0.25rem 0; color: var(--text-light);"><strong>Created:</strong> <?php echo date('M j, Y', strtotime($selectedEvent['created_date'])); ?></p>
                    <?php if ($selectedEvent['description']): ?>
                        <p style="margin: 0.25rem 0; color: var(--text-light);"><strong>Description:</strong> <?php echo htmlspecialchars($selectedEvent['description']); ?></p>
                    <?php endif; ?>
                    <p style="margin: 0.5rem 0 0 0;">
                        <a href="<?php echo $selectedEvent['qr_url']; ?>" target="_blank" class="btn btn-info btn-small">👁️ View Event</a>
                        <a href="qr_manager.php" class="btn btn-warning btn-small">📱 Manage QR</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>📁 File Management<?php echo $selectedEvent ? ' - ' . htmlspecialchars($selectedEvent['event_name']) : ''; ?> (<?php echo count($files); ?> files)</h2>
            
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

                            <div style="margin: 0.5rem 0;">
                                <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 999px; background: rgba(184,134,11,0.12); border: 1px solid var(--border-gold); font-size: 0.8rem;">
                                    <?php echo categoryIcon($file['category']) . ' ' . htmlspecialchars(categoryLabel($file['category'])); ?>
                                </span>
                            </div>

                            <div class="file-info">
                                <strong>Type:</strong> <?php echo strtoupper(htmlspecialchars($file['file_type'])); ?><br>
                                <strong>Size:</strong> <?php echo formatFileSize($file['file_size']); ?><br>
                                <strong>Uploaded:</strong> <?php echo date('M j, Y g:i A', strtotime($file['upload_date'])); ?>
                            </div>
                            
                            <div class="file-description">
                                <?php echo $file['description'] ? htmlspecialchars($file['description']) : '<em style="color: var(--text-muted);">No description provided</em>'; ?>
                            </div>

                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center; margin: 0.75rem 0;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                <input type="hidden" name="recategorize_id" value="<?php echo (int) $file['id']; ?>">
                                <select name="category" onchange="this.form.submit()" style="flex: 1;">
                                    <?php foreach (FILE_CATEGORIES as $key => $meta): ?>
                                        <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($file['category'] === $key) ? 'selected' : ''; ?>>
                                            Move to <?php echo htmlspecialchars($meta['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <div class="file-actions">
                                <a href="../uploads/<?php echo rawurlencode($file['filename']); ?>" target="_blank" class="btn btn-info btn-small">
                                    👁️ View
                                </a>
                                <a href="../uploads/<?php echo rawurlencode($file['filename']); ?>" download="<?php echo htmlspecialchars($file['original_name']); ?>" class="btn btn-secondary btn-small">
                                    ⬇️ Download
                                </a>
                                <a href="?delete=<?php echo (int) $file['id']; ?>&token=<?php echo urlencode($csrfToken); ?>" class="btn btn-danger btn-small" onclick="return confirm('⚠️ Are you sure you want to delete this file? This action cannot be undone.')">
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