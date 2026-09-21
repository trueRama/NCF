<?php
require_once '../includes/config.php';

// Which of the three repositories is being browsed.
$category = sanitizeCategory($_GET['category'] ?? null);

// Get event code from URL parameter
$eventCode = $_GET['event'] ?? null;

// Get current event
if ($eventCode) {
    $currentEvent = getEventByCode($pdo, $eventCode);
    if (!$currentEvent) {
        // Event not found, redirect to error or show message
        $error = "Event not found or has been deactivated.";
    }
} else {
    // No event specified, get the most recent active event
    $currentEvent = getCurrentEvent($pdo);
}

if (!$currentEvent && !isset($error)) {
    // Create default event if none exists
    createNewEvent($pdo, 'Default Event', 'Default repository for file sharing');
    $currentEvent = getCurrentEvent($pdo);
}

// Files in this repository. Linking without ?event= shows the category across
// every event, which is what the landing page cards do.
$files = [];
if (!isset($error)) {
    $files = getCategoryFiles($pdo, $category, $eventCode ? $currentEvent['id'] : null);
}

$categoryTotals = getCategoryTotals($pdo, $eventCode && $currentEvent ? $currentEvent['id'] : null);

// Preserve the event scope when switching between repositories.
$eventQuery = $eventCode ? '&event=' . urlencode($eventCode) : '';
$downloadAllUrl = '../download_all.php?category=' . urlencode($category) . $eventQuery;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(categoryLabel($category)); ?> - NCF Repository</title>
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

        .repo-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 1rem;
        }

        .repo-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            border: 1px solid var(--border-gold);
            border-radius: 999px;
            text-decoration: none;
            color: var(--text-dark);
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .repo-tab:hover {
            background: rgba(184, 134, 11, 0.08);
            text-decoration: none;
        }

        .repo-tab.is-active {
            background: var(--primary-gold);
            border-color: var(--primary-gold);
            color: #fff;
        }

        .repo-tab-count {
            background: rgba(0, 0, 0, 0.12);
            border-radius: 999px;
            padding: 0.05rem 0.5rem;
            font-size: 0.8rem;
        }

        .repo-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
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
                <a href="<?php echo htmlspecialchars(landingPageUrl()); ?>" class="logo-link" title="Back to the Forum home page">
                    <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image" style="height: 60px; width: 60px; margin-right: 1rem; vertical-align: middle;">
                    NCF Repository
                </a>
            </div>
            <div class="event-subtitle">Ministry of Finance, Planning and Economic Development</div>
            <?php if (isset($error)): ?>
                <div class="event-subtitle" style="color: #ff6b6b;">⚠️ <?php echo htmlspecialchars($error); ?></div>
                <div class="access-info">Please contact the administrator for a valid event link</div>
            <?php elseif ($currentEvent): ?>
                <div class="event-subtitle"><?php echo categoryIcon($category) . ' ' . htmlspecialchars(categoryLabel($category)); ?></div>
                <div class="access-info"><?php echo htmlspecialchars(FILE_CATEGORIES[$category]['blurb']); ?></div>
                <?php if ($eventCode): ?>
                    <div class="access-info" style="font-size: 0.9rem; margin-top: 0.5rem;">
                        <?php echo htmlspecialchars($currentEvent['event_name']); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="card">
                <h2>❌ Event Access Error</h2>
                <div style="text-align: center; padding: 4rem 2rem; color: var(--text-light);">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.3;">🚫</div>
                    <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Event Not Found</h3>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <p style="margin-top: 1.5rem; font-size: 0.9rem;">
                        Please verify the event link or contact the administrator for assistance.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <nav class="repo-tabs" aria-label="Repositories">
                    <?php foreach (FILE_CATEGORIES as $key => $meta): ?>
                        <a
                            href="?category=<?php echo urlencode($key) . $eventQuery; ?>"
                            class="repo-tab<?php echo $key === $category ? ' is-active' : ''; ?>"
                            <?php echo $key === $category ? 'aria-current="page"' : ''; ?>
                        >
                            <?php echo $meta['icon'] . ' ' . htmlspecialchars($meta['label']); ?>
                            <span class="repo-tab-count"><?php echo $categoryTotals[$key]['file_count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="repo-summary">
                    <div>
                        <h2 style="margin: 0;"><?php echo htmlspecialchars(categoryLabel($category)); ?></h2>
                        <p style="margin: 0.35rem 0 0; color: var(--text-light);">
                            <?php echo count($files); ?> file<?php echo count($files) === 1 ? '' : 's'; ?>
                            · <?php echo formatFileSize($categoryTotals[$category]['total_size']); ?>
                        </p>
                    </div>
                    <?php if (!empty($files)): ?>
                        <a href="<?php echo htmlspecialchars($downloadAllUrl); ?>" class="btn btn-primary">
                            ⬇️ Download all (.zip)
                        </a>
                    <?php endif; ?>
                </div>

            <?php if (empty($files)): ?>
                <div style="text-align: center; padding: 4rem 2rem; color: var(--text-light);">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.3;">📂</div>
                    <h3 style="color: var(--text-dark); margin-bottom: 1rem;">Repository is Empty</h3>
                    <p>No files have been published to <?php echo htmlspecialchars(categoryLabel($category)); ?> yet.</p>
                    <p style="margin-top: 0.5rem; font-size: 0.9rem;">Files will appear here once uploaded by the administrator.</p>
                </div>
            <?php else: ?>
                <p style="margin-bottom: 2rem; color: var(--text-light);">
                    Download the whole repository above, or take files one at a time below.
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
                                <strong>Type:</strong> <?php echo strtoupper(htmlspecialchars($file['file_type'])); ?> Document<br>
                                <strong>Size:</strong> <?php echo formatFileSize($file['file_size']); ?><br>
                                <strong>Added:</strong> <?php echo date('M j, Y', strtotime($file['upload_date'])); ?>
                            </div>
                            
                            <div class="file-description">
                                <?php echo $file['description'] ? htmlspecialchars($file['description']) : '<em style="color: var(--text-muted);">No description available for this file.</em>'; ?>
                            </div>
                            
                            <div class="file-actions">
                                <a href="../uploads/<?php echo rawurlencode($file['filename']); ?>" target="_blank" class="btn btn-primary">
                                    👁️ View File
                                </a>
                                <a href="../uploads/<?php echo rawurlencode($file['filename']); ?>" download="<?php echo htmlspecialchars($file['original_name']); ?>" class="btn btn-secondary">
                                    ⬇️ Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <footer style="background: var(--dark-bg); color: rgba(255,255,255,0.8); text-align: center; padding: 2rem; margin-top: 3rem;">
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="footer-logo">
                <a href="<?php echo htmlspecialchars(landingPageUrl()); ?>" class="logo-link" title="Back to the Forum home page">
                    <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image">
                    <span style="color: var(--accent-gold); font-weight: 700;">NCF Repository</span>
                </a>
            </div>
            <p style="margin-bottom: 1rem;">
                <strong>Ministry of Finance, Planning and Economic Development</strong>
            </p>
            <?php if ($eventCode && $currentEvent): ?>
                <p style="margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($currentEvent['event_name']); ?>
                </p>
            <?php endif; ?>
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