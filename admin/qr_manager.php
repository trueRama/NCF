<?php
session_start();
require_once '../includes/config.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Handle new event creation
if ($_POST && isset($_POST['create_event'])) {
    $eventName = trim($_POST['event_name']);
    if (!empty($eventName)) {
        $eventCode = createNewEvent($pdo, $eventName);
        $success = "New event '$eventName' created successfully!";
    } else {
        $error = "Event name cannot be empty.";
    }
}

// Get selected event or current active event
$selectedEventId = $_GET['event_id'] ?? null;
if ($selectedEventId) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND is_active = 1");
    $stmt->execute([$selectedEventId]);
    $currentEvent = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $currentEvent = getCurrentEvent($pdo);
}

if (!$currentEvent) {
    // Create default event if none exists
    createNewEvent($pdo, 'Default Event', 'Default repository for file sharing');
    $currentEvent = getCurrentEvent($pdo);
}

// Use the event's stored QR URL (which includes the event code)
$clientUrl = $currentEvent['qr_url'];

// Generate QR code with proper size
$qrCodeUrl = generateQRCode($clientUrl, 400);

// Get all active events for selection
$allActiveEvents = getAllActiveEvents($pdo);

// Get all events for history
$stmt = $pdo->query("SELECT *, (SELECT COUNT(*) FROM files WHERE event_id = events.id) as file_count FROM events ORDER BY created_date DESC");
$allEvents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Manager - NCF Repository</title>
    <link rel="stylesheet" href="../assets/css/corporate-style.css">
    <style>
        .qr-code-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 2px solid var(--border-gold);
            display: inline-block;
        }
        
        .qr-code-img {
            display: block;
            margin: 0 auto;
            border-radius: 8px;
        }
        
        .qr-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        
        .qr-info {
            margin-top: 2rem;
            padding: 1.5rem;
            background: var(--light-bg);
            border-radius: 12px;
            border-left: 4px solid var(--primary-gold);
        }
        
        .url-display {
            background: white;
            padding: 0.75rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
            border: 1px solid var(--border-light);
            color: var(--text-dark);
        }
        
        .print-area {
            display: none;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            
            .print-area, .print-area * {
                visibility: visible;
            }
            
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                text-align: center;
                padding: 2rem;
            }
            
            .print-title {
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 1rem;
                color: #B8860B;
            }
            
            .print-subtitle {
                font-size: 1.2rem;
                margin-bottom: 2rem;
                color: #333;
            }
            
            .print-qr img {
                max-width: 300px;
                height: auto;
                margin: 2rem 0;
            }
            
            .print-url {
                font-family: monospace;
                font-size: 1rem;
                margin: 1rem 0;
                word-break: break-all;
            }
        }
        
        @media (max-width: 768px) {
            .qr-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .qr-actions .btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <div class="header-logo">
                    <img src="../assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image">
                    <span class="logo-text">QR Code Manager</span>
                </div>
                <div class="subtitle">Ministry of Finance - Event QR Code Management</div>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php">📋 Dashboard</a>
                <a href="events_manager.php">🎯 Events Manager</a>
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
            <h2>🎯 Select Event for QR Management</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Choose an event to view and manage its QR code
            </p>
            
            <form method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label for="event_id">🎯 Select Event:</label>
                    <select id="event_id" name="event_id" onchange="this.form.submit()">
                        <?php foreach ($allActiveEvents as $event): ?>
                            <option value="<?php echo $event['id']; ?>" <?php echo ($currentEvent['id'] == $event['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['event_name']); ?> (<?php echo $event['event_code']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary">🔍 Select</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>📱 QR Code for: <?php echo htmlspecialchars($currentEvent['event_name']); ?></h2>
            
            <div class="event-info">
                <div class="event-name">
                    🎯 <?php echo htmlspecialchars($currentEvent['event_name']); ?>
                    <span class="event-status status-active">ACTIVE</span>
                </div>
                <p><strong>Event Code:</strong> <code><?php echo htmlspecialchars($currentEvent['event_code']); ?></code></p>
                <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($currentEvent['created_date'])); ?></p>
                <p><strong>Client Access URL:</strong></p>
                <div class="event-url"><?php echo htmlspecialchars($clientUrl); ?></div>
            </div>
            
            <div class="qr-display">
                <div class="qr-code-container">
                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code for <?php echo htmlspecialchars($currentEvent['event_name']); ?>" 
                         class="qr-code-img" id="qrCodeImage" 
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjVmNWY1Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkVycm9yIExvYWRpbmcgUVIgQ29kZTwvdGV4dD48L3N2Zz4='; this.alt='Error loading QR code';"
                         style="max-width: 100%; height: auto; border: 2px solid var(--border-gold); border-radius: 12px; background: white; padding: 20px;">
                </div>
                
                <div class="qr-actions" style="margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
                    <button onclick="downloadQR()" class="btn btn-success">
                        ⬇️ Download QR Code
                    </button>
                    <button onclick="printQR()" class="btn btn-info">
                        🖨️ Print QR Code
                    </button>
                    <button onclick="previewQR()" class="btn btn-primary">
                        👁️ Preview Full Size
                    </button>
                    <button onclick="copyURL()" class="btn btn-warning">
                        📋 Copy URL
                    </button>
                    <button onclick="refreshQR()" class="btn btn-secondary">
                        🔄 Refresh QR Code
                    </button>
                </div>
                
                <div class="qr-info" style="margin-top: 2rem; padding: 1rem; background: var(--light-bg); border-radius: 8px; border-left: 4px solid var(--primary-gold);">
                    <h4 style="margin: 0 0 1rem 0; color: var(--text-dark);">📋 QR Code Information</h4>
                    <p style="margin: 0.5rem 0; color: var(--text-light);"><strong>Size:</strong> 400x400 pixels</p>
                    <p style="margin: 0.5rem 0; color: var(--text-light);"><strong>Format:</strong> PNG</p>
                    <p style="margin: 0.5rem 0; color: var(--text-light);"><strong>Target URL:</strong></p>
                    <div class="url-display" style="background: white; padding: 0.75rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; word-break: break-all; border: 1px solid var(--border-light);">
                        <?php echo htmlspecialchars($clientUrl); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>📝 All Events</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Quick overview of all events. Use <a href="events_manager.php" style="color: var(--primary-gold);">Events Manager</a> to create new events or manage existing ones.
            </p>
            
            <div class="events-history">
                <?php if (empty($allEvents)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                        <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">📅</div>
                        <p>No events created yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($allEvents as $event): ?>
                        <div class="event-item <?php echo $event['is_active'] ? 'active' : ''; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div style="flex: 1; min-width: 200px;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($event['event_name']); ?></strong>
                                        <span class="event-status <?php echo $event['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $event['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                        </span>
                                    </div>
                                    <div style="color: var(--text-light); font-size: 0.9rem;">
                                        <strong>Code:</strong> <?php echo htmlspecialchars($event['event_code']); ?><br>
                                        <strong>Files:</strong> <?php echo $event['file_count']; ?><br>
                                        <strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($event['created_date'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <a href="?event_id=<?php echo $event['id']; ?>" class="btn btn-info btn-small">
                                        � View QR
                                    </a>
                                    <a href="events_manager.php" class="btn btn-warning btn-small">
                                        ⚙️ Manage
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Hidden print area -->
    <div class="print-area">
        <div class="print-title">📁 NCF Repository</div>
        <div class="print-subtitle">Ministry of Finance, Planning and Economic Development</div>
        <div class="print-subtitle"><?php echo htmlspecialchars($currentEvent['event_name']); ?></div>
        <div class="print-qr">
            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" style="max-width: 100%; height: auto;">
        </div>
        <div class="print-url"><?php echo htmlspecialchars($clientUrl); ?></div>
        <p style="margin-top: 2rem; color: #666; font-size: 1.2rem;">
            📱 Scan this QR code with your mobile device to access the event repository
        </p>
        <p style="margin-top: 1rem; color: #999; font-size: 1rem;">
            Or visit the URL above in your web browser • Event Code: <?php echo htmlspecialchars($currentEvent['event_code']); ?>
        </p>
        <p style="margin-top: 2rem; color: #999; font-size: 0.9rem;">
            Generated on <?php echo date('F j, Y \a\t g:i A'); ?> • Republic of Uganda
        </p>
    </div>
    
    <script>
        // QR Code management functions
        function downloadQR() {
            try {
                const qrUrl = "<?php echo $qrCodeUrl; ?>";
                const eventCode = "<?php echo htmlspecialchars($currentEvent['event_code']); ?>";
                
                // Create a temporary link for download
                const link = document.createElement('a');
                link.href = qrUrl;
                link.download = `NCF_Repository_QR_${eventCode}.png`;
                link.target = '_blank';
                
                // Add to document and click
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showToast('✅ QR Code download started!');
            } catch (error) {
                console.error('Download error:', error);
                showToast('❌ Download failed. Please try again.', 'error');
            }
        }
        
        function printQR() {
            // Update print area content before printing
            const printQRImg = document.querySelector('.print-qr img');
            if (printQRImg) {
                printQRImg.src = "<?php echo $qrCodeUrl; ?>";
            }
            
            window.print();
            showToast('🖨️ Print dialog opened!');
        }
        
        function previewQR() {
            const qrUrl = "<?php echo $qrCodeUrl; ?>";
            const previewWindow = window.open('', '_blank', 'width=500,height=600,scrollbars=yes,resizable=yes');
            
            previewWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>QR Code Preview - <?php echo htmlspecialchars($currentEvent['event_name']); ?></title>
                    <style>
                        body { margin: 0; padding: 20px; text-align: center; font-family: Arial, sans-serif; background: #f5f5f5; }
                        .container { background: white; padding: 30px; border-radius: 10px; display: inline-block; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                        img { max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; }
                        h2 { color: #333; margin-bottom: 20px; }
                        .info { margin-top: 20px; color: #666; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h2>📱 NCF Repository QR Code</h2>
                        <img src="${qrUrl}" alt="QR Code" />
                        <div class="info">
                            <p><strong>Event:</strong> <?php echo htmlspecialchars($currentEvent['event_name']); ?></p>
                            <p><strong>URL:</strong> <?php echo htmlspecialchars($clientUrl); ?></p>
                        </div>
                    </div>
                </body>
                </html>
            `);
            
            previewWindow.document.close();
            showToast('👁️ QR Code preview opened!');
        }
        
        function copyURL() {
            const url = "<?php echo $clientUrl; ?>";
            
            if (navigator.clipboard && window.isSecureContext) {
                // Modern clipboard API
                navigator.clipboard.writeText(url).then(function() {
                    showToast('✅ URL copied to clipboard!');
                }, function(err) {
                    console.error('Clipboard error:', err);
                    fallbackCopyText(url);
                });
            } else {
                // Fallback for older browsers or non-secure contexts
                fallbackCopyText(url);
            }
        }
        
        function fallbackCopyText(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showToast('✅ URL copied to clipboard!');
            } catch (err) {
                console.error('Fallback copy error:', err);
                showToast('❌ Failed to copy URL. Please copy manually.', 'error');
            }
            
            document.body.removeChild(textArea);
        }
        
        function refreshQR() {
            const qrImg = document.getElementById('qrCodeImage');
            const originalSrc = qrImg.src;
            
            // Add timestamp to force refresh
            const separator = originalSrc.includes('?') ? '&' : '?';
            qrImg.src = originalSrc + separator + 't=' + new Date().getTime();
            
            showToast('🔄 QR Code refreshed!');
        }
        
        function reactivateEvent(eventId) {
            if (confirm('⚠️ This will deactivate the current event and activate the selected one. All current QR codes will stop working. Continue?')) {
                window.location.href = 'qr_manager.php?reactivate=' + eventId;
            }
        }
        
        function showToast(message, type = 'success') {
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.toast-notification');
            existingToasts.forEach(toast => toast.remove());
            
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.textContent = message;
            
            const bgColor = type === 'error' ? '#e74c3c' : type === 'warning' ? '#f39c12' : '#27ae60';
            
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                z-index: 10000;
                font-weight: 500;
                transition: all 0.3s ease;
                transform: translateX(100%);
                max-width: 300px;
                word-wrap: break-word;
            `;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Remove after 4 seconds
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 4000);
        }
        
        // Add smooth animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Test QR code loading
            const qrImg = document.getElementById('qrCodeImage');
            if (qrImg) {
                qrImg.addEventListener('load', function() {
                    console.log('QR Code loaded successfully');
                });
                
                qrImg.addEventListener('error', function() {
                    console.error('QR Code failed to load');
                    showToast('⚠️ QR Code failed to load. Please refresh the page.', 'warning');
                });
            }
        });
    </script>
</body>
</html>

<?php
// Handle event reactivation
if (isset($_GET['reactivate'])) {
    $eventId = $_GET['reactivate'];
    
    // Deactivate all events
    $pdo->exec("UPDATE events SET is_active = 0");
    
    // Activate selected event
    $stmt = $pdo->prepare("UPDATE events SET is_active = 1 WHERE id = ?");
    $stmt->execute([$eventId]);
    
    // Redirect to avoid reactivation on refresh
    header('Location: qr_manager.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>