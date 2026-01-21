<?php
$page_title = "User Management";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['admin']);

$database = new Database();
$db = $database->getConnection();

// Handle user activation/deactivation
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $action = $_GET['toggle_status'];
    
    // Get user current status
    $user_query = "SELECT is_active, role, username FROM users WHERE id = :id";
    $user_stmt = $db->prepare($user_query);
    $user_stmt->bindParam(':id', $user_id);
    $user_stmt->execute();
    $target_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$target_user) {
        $_SESSION['error'] = "User not found.";
        header("Location: users.php");
        exit();
    }
    
    // Prevent deactivating own account
    if ($user_id == $_SESSION['user_id'] && $action == 'deactivate') {
        $_SESSION['error'] = "You cannot deactivate your own account.";
        header("Location: users.php");
        exit();
    }
    
    // Check if this is the last active admin when deactivating
    if ($action == 'deactivate' && $target_user['role'] == 'admin') {
        $admin_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND is_active = 1";
        $admin_count_stmt = $db->prepare($admin_count_query);
        $admin_count_stmt->execute();
        $admin_count = $admin_count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($admin_count <= 1) {
            $_SESSION['error'] = "Cannot deactivate the last active administrator.";
            header("Location: users.php");
            exit();
        }
    }
    
    // Update user status
    $new_status = $action == 'activate' ? 1 : 0;
    $update_query = "UPDATE users SET is_active = :status WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':status', $new_status);
    $update_stmt->bindParam(':id', $user_id);
    
    if ($update_stmt->execute()) {
        $status_text = $action == 'activate' ? 'activated' : 'deactivated';
        
        // Log activity
        Auth::logActivity($_SESSION['user_id'], 'User Status Change', 
            "User account {$target_user['username']} $status_text");
        
        // Create notification for the user if activating
        if ($action == 'activate') {
            createNotification(
                $user_id,
                'Account Activated',
                "Your account has been activated by the administrator.",
                'success',
                '../login.php'
            );
        }
        
        $_SESSION['success'] = "User account {$target_user['username']} has been $status_text successfully!";
    } else {
        $_SESSION['error'] = "Failed to update user status.";
    }
    
    header("Location: users.php");
    exit();
}

// Get all users with additional info
$query = "SELECT u.*, 
                 (SELECT COUNT(*) FROM activity_logs al WHERE al.user_id = u.id) as activity_count
          FROM users u 
          ORDER BY u.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>User Management</h1>
                <p>Manage system users and their permissions</p>
            </div>
            <div class="page-actions">
                <a href="users_add.php" class="btn btn-primary">Add New User</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- User Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value"><?php echo count($users); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Active Users</h3>
                <div class="stat-value">
                    <?php 
                    $active_count = array_filter($users, function($u) { return $u['is_active']; });
                    echo count($active_count);
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Administrators</h3>
                <div class="stat-value">
                    <?php 
                    $admin_count = array_filter($users, function($u) { return $u['role'] == 'admin'; });
                    echo count($admin_count);
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Today's Logins</h3>
                <div class="stat-value">
                    <?php 
                    $today_logins = $db->query("SELECT COUNT(DISTINCT user_id) as count FROM activity_logs 
                                               WHERE action = 'Login' AND DATE(created_at) = CURDATE()")->fetch(PDO::FETCH_ASSOC)['count'];
                    echo $today_logins;
                    ?>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="data-table">
            <div class="table-header">
                <h3>System Users</h3>
                <div class="table-actions">
                    <button class="btn btn-outline" onclick="exportUsers()">Export Users</button>
                </div>
            </div>

            <table id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Activity</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                        <tr id="user-<?php echo $user['id']; ?>">
                            <td>#<?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status <?php echo $user['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="activity-info">
                                    <small><?php echo $user['activity_count']; ?> logs</small>
                                </div>
                            </td>
                            <td>
                                <?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="users_edit.php?id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-outline" title="Edit User">
                                        Edit
                                    </a>
                                    
                                    <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                        <?php if ($user['is_active']): ?>
                                            <!-- Show Deactivate button for active users -->
                                            <a href="users.php?toggle_status=deactivate&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-warning" 
                                               onclick="return confirm('Deactivate this user account?')"
                                               title="Deactivate User">
                                                Deactivate
                                            </a>
                                        <?php else: ?>
                                            <!-- Show Activate button for inactive users -->
                                            <a href="users.php?toggle_status=activate&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Activate this user account?')"
                                               title="Activate User">
                                                Activate
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="users_delete.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure? This will redirect to delete confirmation page.')"
                                           title="Delete User">
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #64748b;">
                                No users found. <a href="users_add.php">Add the first user</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- User Roles Legend -->
        <div class="info-box">
            <h4>👥 User Roles Overview</h4>
            <div class="roles-legend">
                <div class="legend-item">
                    <span class="role-badge role-admin">Admin</span>
                    <span> - Full system access, user management, backups</span>
                </div>
                <div class="legend-item">
                    <span class="role-badge role-captain">Captain</span>
                    <span> - Approve certificates, view reports, post announcements</span>
                </div>
                <div class="legend-item">
                    <span class="role-badge role-secretary">Secretary</span>
                    <span> - Manage residents, blotters, certificates, generate reports</span>
                </div>
                
            </div>
            <p><small>Total: <?php echo count($users); ?> users registered in the system</small></p>
        </div>
    </main>
</div>

<style>
.role-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.role-admin {
    background: #6366f1;
    color: white;
}

.role-captain {
    background: #10b981;
    color: white;
}

.role-secretary {
    background: #f59e0b;
    color: white;
}

.role-resident {
    background: #64748b;
    color: white;
}

.activity-info {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.activity-info small {
    font-size: 0.8rem;
    color: var(--secondary);
}

.action-buttons {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
}

.text-muted {
    color: #94a3b8;
    font-size: 0.8rem;
    font-style: italic;
}

.roles-legend {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin: 1rem 0;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Animation for status change */
@keyframes statusChange {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.status-changed {
    animation: statusChange 0.5s ease;
}
</style>

<script>
function exportUsers() {
    // Simple export to CSV
    let csv = 'User ID,Username,Full Name,Email,Role,Status,Last Login\n';
    
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        if (row.cells.length > 1) {
            const cells = row.cells;
            const data = [
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim(),
                cells[7].textContent.trim()
            ];
            csv += data.map(cell => `"${cell}"`).join(',') + '\n';
        }
    });
    
    // Create and download CSV file
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'users-export-' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add animation when status changes
document.addEventListener('DOMContentLoaded', function() {
    // Check if we just changed a user's status
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('toggle_status') && urlParams.has('id')) {
        const userId = urlParams.get('id');
        const userRow = document.getElementById('user-' + userId);
        if (userRow) {
            userRow.classList.add('status-changed');
            setTimeout(() => {
                userRow.classList.remove('status-changed');
            }, 500);
        }
    }
    
    // Confirm before deactivating/activating user
    const statusLinks = document.querySelectorAll('a[href*="toggle_status"]');
    statusLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const action = this.textContent.trim().toLowerCase();
            if (!confirm(`Are you sure you want to ${action} this user?`)) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>