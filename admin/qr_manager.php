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

// Get current active event
$currentEvent = getCurrentEvent($pdo);
if (!$currentEvent) {
    // Create default event if none exists
    createNewEvent($pdo, 'Default Event');
    $currentEvent = getCurrentEvent($pdo);
}

$clientUrl = $currentEvent['qr_url'];
$qrCodeUrl = generateQRCode($clientUrl, 400);

// Get all events for history
$stmt = $pdo->query("SELECT * FROM events ORDER BY created_date DESC");
$allEvents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Manager - NCF Repository</title>
    <link rel="stylesheet" href="../assets/css/corporate-style.css">
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
            <h2>📱 Current Active Event</h2>
            
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
                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="qr-code-img" id="qrCodeImage">
                </div>
                
                <div style="margin-top: 2rem;">
                    <button onclick="downloadQR()" class="btn btn-success">
                        ⬇️ Download QR Code
                    </button>
                    <button onclick="printQR()" class="btn btn-info">
                        🖨️ Print QR Code
                    </button>
                    <a href="<?php echo $qrCodeUrl; ?>" target="_blank" class="btn btn-primary">
                        👁️ View Full Size
                    </a>
                    <button onclick="copyURL()" class="btn btn-warning">
                        📋 Copy URL
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>🆕 Create New Event</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Creating a new event will generate a fresh QR code and deactivate the current one. This is useful for different occasions or when you want to reset access.
            </p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="event_name">🎯 Event Name:</label>
                    <input type="text" id="event_name" name="event_name" placeholder="e.g., Annual Conference 2025, Workshop Series, Product Launch" required>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Choose a descriptive name that identifies this specific event
                    </small>
                </div>
                
                <button type="submit" name="create_event" class="btn btn-primary" onclick="return confirm('⚠️ This will deactivate the current QR code and create a new one. All previous QR codes will stop working. Continue?')">
                    🎯 Create New Event
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2>📝 Events History</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                View all events and reactivate previous ones if needed
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
                                        <strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($event['created_date'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <?php if (!$event['is_active']): ?>
                                        <button onclick="reactivateEvent(<?php echo $event['id']; ?>)" class="btn btn-info btn-small">
                                            🔄 Reactivate
                                        </button>
                                    <?php else: ?>
                                        <span class="btn btn-success btn-small" style="cursor: default;">
                                            ✅ Current
                                        </span>
                                    <?php endif; ?>
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
        <div class="print-subtitle"><?php echo htmlspecialchars($currentEvent['event_name']); ?></div>
        <div class="print-qr">
            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" style="max-width: 100%; height: auto;">
        </div>
        <div class="print-url"><?php echo htmlspecialchars($clientUrl); ?></div>
        <p style="margin-top: 2rem; color: #666; font-size: 1.2rem;">
            📱 Scan this QR code with your phone to access the event repository
        </p>
        <p style="margin-top: 1rem; color: #999; font-size: 1rem;">
            Or visit the URL above in your web browser
        </p>
    </div>
    
    <script>
        function downloadQR() {
            const qrUrl = "<?php echo $qrCodeUrl; ?>";
            const link = document.createElement('a');
            link.href = qrUrl;
            link.download = 'NCF_Repository_QR_<?php echo htmlspecialchars($currentEvent['event_code']); ?>.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success feedback
            showToast('✅ QR Code downloaded successfully!');
        }
        
        function printQR() {
            window.print();
        }
        
        function copyURL() {
            const url = "<?php echo $clientUrl; ?>";
            navigator.clipboard.writeText(url).then(function() {
                showToast('✅ URL copied to clipboard!');
            }, function(err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('✅ URL copied to clipboard!');
            });
        }
        
        function reactivateEvent(eventId) {
            if (confirm('⚠️ This will deactivate the current event and activate the selected one. All current QR codes will stop working. Continue?')) {
                window.location.href = 'qr_manager.php?reactivate=' + eventId;
            }
        }
        
        function showToast(message) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--success-green);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                z-index: 10000;
                font-weight: 500;
                transition: all 0.3s ease;
                transform: translateX(100%);
            `;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
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