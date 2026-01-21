<?php
require_once '../config/auth.php';
require_once '../config/functions.php';
Auth::checkTimeout();

// Get notification count
$notification_count = getNotificationCount($_SESSION['user_id']);
// Get unread notifications
$unread_notifications = getUnreadNotifications($_SESSION['user_id']);
?>
<header class="header">
    <div class="logo-container">

       <!-- logo -->
            <div class="logo">
                 <img src="../assets/img/logo1.png" alt="BL Logo" class="logo-img">
            </div>

        <div class="system-title">
            <h1>SBIMS-PRO</h1>
            <p>Brgy. Libertad, Isabel, Leyte</p>
        </div>
    </div>
    
    <div class="user-menu">
        <!-- Notification Dropdown -->
        <div class="notification-dropdown">
            <div class="notification-icon" id="notificationIcon">
                <span>🔔</span>
                <?php if ($notification_count > 0): ?>
                    <span class="notification-badge" id="notificationBadge"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </div>
            <div class="notification-panel" id="notificationPanel">
                <div class="notification-header">
                    <h4>Notifications</h4>
                    <?php if ($notification_count > 0): ?>
                        <button class="mark-all-read" onclick="markAllNotificationsAsRead()">Mark all as read</button>
                    <?php endif; ?>
                </div>
                <div class="notification-list" id="notificationList">
                    <?php if (count($unread_notifications) > 0): ?>
                        <?php foreach ($unread_notifications as $notification): ?>
                        <div class="notification-item" data-id="<?php echo $notification['id']; ?>">
                            <div class="notification-type type-<?php echo $notification['type']; ?>"></div>
                            <div class="notification-content">
                                <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small><?php echo timeAgo($notification['created_at']); ?></small>
                            </div>
                            <?php if ($notification['link']): ?>
                                <a href="<?php echo $notification['link']; ?>" class="notification-link">View</a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-notifications">
                            <p>No new notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="notification-footer">
                    <a href="notifications.php">View all notifications</a>
                </div>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="user-profile">
            <span><?php echo $_SESSION['full_name']; ?> (<?php echo ucfirst($_SESSION['user_role']); ?>)</span>
            <div class="user-dropdown">
                <a href="../logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>

<style>
/* Logo Styles */
    .logo-img {
  height: 70px;        
  width: auto;
  display: block;
}

/* Notification Styles */
.notification-dropdown {
    position: relative;
}

.notification-panel {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    display: none;
    z-index: 1000;
    margin-top: 10px;
}

.notification-panel.show {
    display: block;
}

.notification-header {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h4 {
    margin: 0;
    color: var(--dark);
}

.mark-all-read {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    font-size: 0.9rem;
    padding: 0.3rem 0.5rem;
    border-radius: 4px;
}

.mark-all-read:hover {
    background: #eef2ff;
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background: #f8fafc;
}

.notification-item.read {
    opacity: 0.7;
}

.notification-type {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.8rem;
    margin-top: 0.5rem;
    flex-shrink: 0;
}

.type-info { background: #3b82f6; }
.type-warning { background: #f59e0b; }
.type-success { background: #10b981; }
.type-danger { background: #ef4444; }

.notification-content {
    flex: 1;
}

.notification-content strong {
    display: block;
    color: var(--dark);
    font-size: 0.95rem;
    margin-bottom: 0.3rem;
}

.notification-content p {
    margin: 0;
    color: var(--secondary);
    font-size: 0.9rem;
    line-height: 1.4;
}

.notification-content small {
    color: #94a3b8;
    font-size: 0.8rem;
    margin-top: 0.3rem;
    display: block;
}

.notification-link {
    background: var(--primary);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 4px;
    font-size: 0.8rem;
    text-decoration: none;
    margin-left: 0.5rem;
    flex-shrink: 0;
}

.notification-link:hover {
    background: var(--primary-dark);
}

.no-notifications {
    padding: 2rem;
    text-align: center;
    color: var(--secondary);
}

.notification-footer {
    padding: 0.8rem 1rem;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.notification-footer a {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.9rem;
}

.notification-footer a:hover {
    text-decoration: underline;
}

/* Arrow for dropdown */
.notification-panel::before {
    content: '';
    position: absolute;
    top: -6px;
    right: 15px;
    width: 12px;
    height: 12px;
    background: white;
    transform: rotate(45deg);
    box-shadow: -2px -2px 5px rgba(0,0,0,0.05);
}
</style>

<script>
// Notification functionality
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationPanel = document.getElementById('notificationPanel');
    const notificationBadge = document.getElementById('notificationBadge');
    
    // Toggle notification panel
    notificationIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationPanel.classList.toggle('show');
    });
    
    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationPanel.contains(e.target) && !notificationIcon.contains(e.target)) {
            notificationPanel.classList.remove('show');
        }
    });
    
    // Mark notification as read when clicked
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            markNotificationAsRead(notificationId, this);
        });
    });
    
    // Auto-refresh notifications every 30 seconds
    setInterval(refreshNotifications, 30000);
});

function markNotificationAsRead(notificationId, element) {
    fetch('../api/mark_notification_read.php?id=' + notificationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.classList.add('read');
                updateNotificationBadge();
            }
        });
}

function markAllNotificationsAsRead() {
    fetch('../api/mark_all_notifications_read.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.add('read');
                });
                updateNotificationBadge();
            }
        });
}

function refreshNotifications() {
    fetch('../api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            updateNotificationList(data.notifications);
            updateNotificationBadge(data.count);
        });
}

function updateNotificationList(notifications) {
    // Update notification list (simplified version)
    console.log('Notifications updated:', notifications);
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (count === 0 && badge) {
        badge.remove();
    } else if (count > 0) {
        if (!badge) {
            const newBadge = document.createElement('span');
            newBadge.className = 'notification-badge';
            newBadge.id = 'notificationBadge';
            notificationIcon.appendChild(newBadge);
        }
        document.getElementById('notificationBadge').textContent = count;
    }
}
</script>