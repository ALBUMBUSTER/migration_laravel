<?php
$page_title = "Notifications";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();

$database = new Database();
$db = $database->getConnection();

// Get all notifications for current user
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Notifications</h1>
                <p>View all your system notifications</p>
            </div>
        </div>

        <div class="data-table">
            <?php if (count($notifications) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Notification</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notification): ?>
                        <tr class="<?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($notification['title']); ?></strong><br>
                                <small><?php echo htmlspecialchars($notification['message']); ?></small>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $notification['is_read'] ? 'badge-secondary' : 'badge-primary'; ?>">
                                    <?php echo $notification['is_read'] ? 'Read' : 'Unread'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No notifications found.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
tr.unread {
    background-color: #f0f9ff;
    font-weight: 500;
}

tr.read {
    opacity: 0.7;
}
</style>

<?php include '../includes/footer.php'; ?>