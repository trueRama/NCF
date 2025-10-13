<?php
require_once '../includes/config.php';

// Get current active event
$currentEvent = getCurrentEvent($pdo);
if (!$currentEvent) {
    // Create default event if none exists
    createNewEvent($pdo, 'Default Event');
    $currentEvent = getCurrentEvent($pdo);
}

// Get all files
$stmt = $pdo->query("SELECT * FROM files ORDER BY upload_date DESC");
$files = $stmt->fetchAll();

// Get current URL for QR code
$currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentEvent['event_name']); ?> - NCF Repository</title>
    <link rel="stylesheet" href="../assets/css/corporate-style.css">
    <style>
        .client-page {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            min-height: 100vh;
        }
        
        .hero-banner {
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-gold) 100%);
            color: white;
            padding: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 0%, rgba(218, 165, 32, 0.1) 50%, transparent 100%);
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .event-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--accent-gold), var(--light-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .event-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .access-info {
            color: rgba(255,255,255,0.8);
            font-size: 1rem;
        }
        
        .mobile-friendly {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }
        
        .mobile-friendly h4 {
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .event-title {
                font-size: 2rem;
            }
            
            .event-subtitle {
                font-size: 1.1rem;
            }
            
            .hero-content {
                padding: 0 1rem;
            }
            
            .stats {
                flex-direction: column;
                align-items: center;
            }
            
            .stat-item {
                min-width: 200px;
            }
        }
    </style>
</head>
<body class="client-page">
    <div class="hero-banner">
        <div class="hero-content">
            <div class="event-title">
                <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image" style="height: 60px; width: 60px; margin-right: 1rem; vertical-align: middle;">
                NCF Repository
            </div>
            <div class="event-subtitle">Ministry of Finance, Planning and Economic Development</div>
            <div class="event-subtitle"><?php echo htmlspecialchars($currentEvent['event_name']); ?></div>
            <div class="access-info">Professional Event File Repository</div>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>📋 Available Files</h2>
            
            <div class="stats" style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
                <div class="stat-item" style="text-align: center; padding: 1rem; background: var(--light-bg); border-radius: 12px; border-left: 4px solid var(--primary-gold); min-width: 120px;">
                    <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--primary-gold); margin-bottom: 0.5rem;"><?php echo count($files); ?></div>
                    <div class="stat-label" style="color: var(--text-light); font-size: 0.9rem;">Total Files</div>
                </div>
                <div class="stat-item" style="text-align: center; padding: 1rem; background: var(--light-bg); border-radius: 12px; border-left: 4px solid var(--accent-gold); min-width: 120px;">
                    <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--accent-gold); margin-bottom: 0.5rem;"><?php 
                        $pdfCount = 0;
                        foreach($files as $file) {
                            if(strtolower($file['file_type']) === 'pdf') $pdfCount++;
                        }
                        echo $pdfCount;
                    ?></div>
                    <div class="stat-label" style="color: var(--text-light); font-size: 0.9rem;">PDF Documents</div>
                </div>
                <div class="stat-item" style="text-align: center; padding: 1rem; background: var(--light-bg); border-radius: 12px; border-left: 4px solid var(--light-gold); min-width: 120px;">
                    <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--light-gold); margin-bottom: 0.5rem;"><?php echo count($files) - $pdfCount; ?></div>
                    <div class="stat-label" style="color: var(--text-light); font-size: 0.9rem;">Images</div>
                </div>
            </div>
            
            <?php if (empty($files)): ?>
                <div style="text-align: center; padding: 4rem 2rem; color: var(--text-light);">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.3;">📂</div>
                    <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Repository is Empty</h3>
                    <p>No files have been uploaded to this event repository yet.</p>
                    <p style="margin-top: 0.5rem; font-size: 0.9rem;">Files will appear here once uploaded by the administrator.</p>
                </div>
            <?php else: ?>
                <p style="margin-bottom: 2rem; color: var(--text-light);">
                    Browse and download files from the <strong><?php echo htmlspecialchars($currentEvent['event_name']); ?></strong> repository
                </p>
                
                <div class="file-grid">
                    <?php foreach ($files as $file): ?>
                        <div class="file-card">
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
                                <strong>Type:</strong> <?php echo strtoupper($file['file_type']); ?> Document<br>
                                <strong>Size:</strong> <?php echo formatFileSize($file['file_size']); ?><br>
                                <strong>Added:</strong> <?php echo date('M j, Y', strtotime($file['upload_date'])); ?>
                            </div>
                            
                            <div class="file-description">
                                <?php echo $file['description'] ? htmlspecialchars($file['description']) : '<em style="color: var(--text-muted);">No description available for this file.</em>'; ?>
                            </div>
                            
                            <div class="file-actions">
                                <a href="../uploads/<?php echo $file['filename']; ?>" target="_blank" class="btn btn-primary">
                                    👁️ View File
                                </a>
                                <a href="../uploads/<?php echo $file['filename']; ?>" download="<?php echo $file['original_name']; ?>" class="btn btn-secondary">
                                    ⬇️ Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer style="background: var(--dark-bg); color: rgba(255,255,255,0.8); text-align: center; padding: 2rem; margin-top: 3rem;">
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="footer-logo">
                <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image">
                <span style="color: var(--accent-gold); font-weight: 700;">NCF Repository</span>
            </div>
            <p style="margin-bottom: 1rem;">
                <strong>Ministry of Finance, Planning and Economic Development</strong>
            </p>
            <p style="margin-bottom: 1rem;">
                <?php echo htmlspecialchars($currentEvent['event_name']); ?>
            </p>
            <p style="font-size: 0.9rem; opacity: 0.7;">
                Professional Event File Repository • © <?php echo date('Y'); ?> Republic of Uganda
            </p>
        </div>
    </footer>
    
    <script>
        // Add smooth animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate file cards on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });
            
            // Animate file cards
            document.querySelectorAll('.file-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
            
            // Add click feedback for mobile
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                btn.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Smooth scroll behavior
            document.documentElement.style.scrollBehavior = 'smooth';
            
            // Add loading states for file actions
            document.querySelectorAll('a[target="_blank"]').forEach(link => {
                link.addEventListener('click', function() {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="loading"></span> Opening...';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                });
            });
        });
        
        // Add progressive web app features
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // Future: Add service worker for offline access
            });
        }
    </script>
</body>
</html>