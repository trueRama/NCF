<?php
session_start();
require_once '../includes/config.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Handle event creation
if ($_POST && isset($_POST['create_event'])) {
    $eventName = trim($_POST['event_name']);
    $description = trim($_POST['description']);
    
    if (!empty($eventName)) {
        $eventCode = createNewEvent($pdo, $eventName, $description);
        $success = "New event '$eventName' created successfully with code: $eventCode";
    } else {
        $error = "Event name cannot be empty.";
    }
}

// Handle event status toggle
if (isset($_GET['toggle_status'])) {
    $eventId = $_GET['toggle_status'];
    $newStatus = $_GET['status'];
    
    if (toggleEventStatus($pdo, $eventId, $newStatus)) {
        $success = $newStatus ? "Event activated successfully!" : "Event deactivated successfully!";
    } else {
        $error = "Failed to update event status.";
    }
}

// Handle event deletion
if (isset($_GET['delete_event'])) {
    $eventId = $_GET['delete_event'];
    
    if (deleteEvent($pdo, $eventId)) {
        $success = "Event and all associated files deleted successfully!";
    } else {
        $error = "Failed to delete event.";
    }
}

// Get all events (both active and inactive)
$stmt = $pdo->query("SELECT *, (SELECT COUNT(*) FROM files WHERE event_id = events.id) as file_count FROM events ORDER BY created_date DESC");
$allEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Manager - NCF Repository</title>
    <link rel="stylesheet" href="../assets/css/corporate-style.css">
    <style>
        .event-card {
            background: var(--light-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-gold);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        
        .event-card.inactive {
            border-left-color: var(--text-muted);
            opacity: 0.7;
        }
        
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .event-title {
            flex: 1;
            min-width: 250px;
        }
        
        .event-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .event-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .event-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .stat-box {
            text-align: center;
            padding: 0.75rem;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border-light);
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-gold);
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }
        
        @media (max-width: 768px) {
            .event-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .event-actions {
                justify-content: center;
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
                    <span class="logo-text">Events Manager</span>
                </div>
                <div class="subtitle">Ministry of Finance - Multi-Event Management</div>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php">📋 Dashboard</a>
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
            <h2>🆕 Create New Event</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Create multiple events to organize different file repositories. Each event gets its own QR code and access URL.
            </p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="event_name">🎯 Event Name:</label>
                    <input type="text" id="event_name" name="event_name" placeholder="e.g., Annual Conference 2025, Budget Workshop, Training Session" required>
                </div>
                
                <div class="form-group">
                    <label for="description">📝 Event Description (optional):</label>
                    <textarea id="description" name="description" placeholder="Brief description of the event and its purpose..."></textarea>
                </div>
                
                <button type="submit" name="create_event" class="btn btn-primary">
                    🎯 Create New Event
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2>🎯 All Events (<?php echo count($allEvents); ?>)</h2>
            <p style="margin-bottom: 1.5rem; color: var(--text-light);">
                Manage all your events and their repositories. Each event has its own file collection and QR code.
            </p>
            
            <?php if (empty($allEvents)): ?>
                <div style="text-align: center; padding: 4rem 2rem; color: var(--text-light);">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.3;">🎯</div>
                    <h3 style="color: var(--text-dark); margin-bottom: 1rem;">No Events Created Yet</h3>
                    <p>Create your first event using the form above to start organizing your file repositories.</p>
                </div>
            <?php else: ?>
                <?php foreach ($allEvents as $event): ?>
                    <div class="event-card <?php echo $event['is_active'] ? '' : 'inactive'; ?>">
                        <div class="event-header">
                            <div class="event-title">
                                <h3 style="margin: 0 0 0.5rem 0; color: var(--text-dark);">
                                    <?php echo htmlspecialchars($event['event_name']); ?>
                                    <span class="event-status <?php echo $event['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $event['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </h3>
                                <p style="margin: 0.25rem 0; color: var(--text-light); font-size: 0.9rem;">
                                    <strong>Code:</strong> <?php echo htmlspecialchars($event['event_code']); ?>
                                </p>
                                <p style="margin: 0.25rem 0; color: var(--text-light); font-size: 0.9rem;">
                                    <strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($event['created_date'])); ?>
                                </p>
                                <?php if ($event['description']): ?>
                                    <p style="margin: 0.5rem 0; color: var(--text-light); font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($event['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="event-actions">
                                <a href="<?php echo $event['qr_url']; ?>" target="_blank" class="btn btn-info btn-small">
                                    👁️ View
                                </a>
                                <a href="dashboard.php?event_id=<?php echo $event['id']; ?>" class="btn btn-warning btn-small">
                                    📁 Files
                                </a>
                                <?php if ($event['is_active']): ?>
                                    <a href="?toggle_status=<?php echo $event['id']; ?>&status=0" class="btn btn-secondary btn-small" onclick="return confirm('Deactivate this event? It will no longer be accessible via QR code.')">
                                        ⏸️ Deactivate
                                    </a>
                                <?php else: ?>
                                    <a href="?toggle_status=<?php echo $event['id']; ?>&status=1" class="btn btn-success btn-small">
                                        ▶️ Activate
                                    </a>
                                <?php endif; ?>
                                <a href="?delete_event=<?php echo $event['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('⚠️ Delete this event and ALL its files permanently? This cannot be undone!')">
                                    🗑️ Delete
                                </a>
                            </div>
                        </div>
                        
                        <div class="event-stats">
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $event['file_count']; ?></div>
                                <div class="stat-label">Files</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $event['is_active'] ? '🟢' : '🔴'; ?></div>
                                <div class="stat-label">Status</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number">📱</div>
                                <div class="stat-label">QR Code</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 0.75rem; background: white; border-radius: 6px; font-family: monospace; font-size: 0.8rem; word-break: break-all; border: 1px solid var(--border-light);">
                            <strong>🔗 Access URL:</strong> <?php echo htmlspecialchars($event['qr_url']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Add smooth animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.event-card');
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