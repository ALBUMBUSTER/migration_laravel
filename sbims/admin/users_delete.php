<?php
$page_title = "Delete User";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['admin']);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_GET['id'];

// Prevent deleting own account
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: users.php");
    exit();
}

// Get user details for confirmation
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php");
    exit();
}

// Handle deletion confirmation
$deleted = false;
if (isset($_POST['confirm_delete'])) {
    // First, check if this is the last admin
    if ($user['role'] == 'admin') {
        $admin_count = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND is_active = 1")->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($admin_count <= 1) {
            $_SESSION['error'] = "Cannot delete the last active administrator.";
            header("Location: users.php");
            exit();
        }
    }
    
    // Log the deletion attempt
    Auth::logActivity($_SESSION['user_id'], 'Delete User Attempt', 
        "Attempted to delete user: {$user['full_name']} (ID: {$user_id})");
    
    // Soft delete (deactivate) instead of hard delete
    $query = "UPDATE users SET is_active = 0 WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    
    if ($stmt->execute()) {
        // Log successful deactivation
        Auth::logActivity($_SESSION['user_id'], 'Deactivate User', 
            "Deactivated user: {$user['full_name']} (ID: {$user_id})");
        
        $_SESSION['success'] = "User deactivated successfully!";
        $deleted = true;
    } else {
        $_SESSION['error'] = "Failed to deactivate user.";
    }
    
    header("Location: users.php");
    exit();
}

// For hard delete (completely remove from database)
if (isset($_POST['hard_delete'])) {
    $confirm_text = $_POST['confirm_text'];
    
    if ($confirm_text !== "DELETE {$user['username']}") {
        $_SESSION['error'] = "Confirmation text does not match.";
        header("Location: users_delete.php?id=" . $user_id);
        exit();
    }
    
    // Check dependencies before hard delete
    $dependencies = [];
    
    // Check if user has activity logs
    $log_count = $db->prepare("SELECT COUNT(*) as count FROM activity_logs WHERE user_id = :id");
    $log_count->bindParam(':id', $user_id);
    $log_count->execute();
    if ($log_count->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        $dependencies[] = "activity logs";
    }
    
    // Check if user issued certificates
    $cert_count = $db->prepare("SELECT COUNT(*) as count FROM certificates WHERE issued_by = :id OR approved_by = :id");
    $cert_count->bindParam(':id', $user_id);
    $cert_count->execute();
    if ($cert_count->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        $dependencies[] = "certificate records";
    }
    
    // Check if user handled blotters
    $blotter_count = $db->prepare("SELECT COUNT(*) as count FROM blotters WHERE handled_by = :id");
    $blotter_count->bindParam(':id', $user_id);
    $blotter_count->execute();
    if ($blotter_count->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        $dependencies[] = "blotter cases";
    }
    
    if (!empty($dependencies)) {
        $_SESSION['error'] = "Cannot delete user. User has associated records: " . implode(', ', $dependencies);
        header("Location: users_delete.php?id=" . $user_id);
        exit();
    }
    
    // Hard delete the user
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    
    if ($stmt->execute()) {
        // Log hard deletion
        Auth::logActivity($_SESSION['user_id'], 'Hard Delete User', 
            "Permanently deleted user: {$user['full_name']} (ID: {$user_id})");
        
        $_SESSION['success'] = "User permanently deleted!";
        $deleted = true;
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }
    
    header("Location: users.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Delete User Account</h1>
                <p>Remove user from the system</p>
            </div>
            <div class="page-actions">
                <a href="users.php" class="btn btn-outline">Back to Users</a>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="delete-confirmation">
            <!-- User Information -->
            <div class="user-info-card">
                <h3>User to be Deleted</h3>
                <div class="user-details">
                    <div class="detail-row">
                        <label>Username:</label>
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Full Name:</label>
                        <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Role:</label>
                        <span class="status"><?php echo ucfirst($user['role']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Account Status:</label>
                        <span class="status <?php echo $user['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <label>Created:</label>
                        <span><?php echo formatDateTime($user['created_at']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Last Login:</label>
                        <span><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Warning Message -->
            <div class="warning-box">
                <h4>⚠️ Warning: Account Deletion</h4>
                <p><strong>This action cannot be undone.</strong> Deleting this user account will:</p>
                <ul>
                    <li>Remove all user access to the system</li>
                    <li>Preserve user's activity logs for audit trail</li>
                    <li>Keep all data created by this user (residents, blotters, certificates)</li>
                    <li>Affect any pending tasks assigned to this user</li>
                </ul>
            </div>

            <!-- Deactivate Option (Recommended) -->
            <div class="option-card">
                <h4>🛑 Recommended: Deactivate Account</h4>
                <p>Instead of permanent deletion, you can deactivate the account. This will:</p>
                <ul>
                    <li>Prevent user from logging in</li>
                    <li>Keep all user data and records intact</li>
                    <li>Allow reactivation if needed in the future</li>
                    <li>Maintain system integrity and audit trail</li>
                </ul>
                
                <form method="POST" class="deactivate-form">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-warning" 
                            onclick="return confirm('Deactivate this user account?')">
                        Deactivate Account
                    </button>
                </form>
            </div>

            <!-- Hard Delete Option (Dangerous) -->
            <div class="option-card danger">
                <h4>🗑️ Permanent Deletion (Dangerous)</h4>
                <p><strong>Only use this if absolutely necessary.</strong> Permanent deletion will:</p>
                <ul>
                    <li>Completely remove user from database</li>
                    <li>Break any references to this user</li>
                    <li>Affect system integrity</li>
                    <li>Require manual cleanup of orphaned records</li>
                </ul>
                
                <div class="danger-zone">
                    <p><strong>To confirm permanent deletion, type the following text exactly:</strong></p>
                    <p class="confirmation-text"><code>DELETE <?php echo $user['username']; ?></code></p>
                    
                    <form method="POST" id="hardDeleteForm">
                        <input type="hidden" name="hard_delete" value="1">
                        <div class="form-group">
                            <input type="text" id="confirm_text" name="confirm_text" 
                                   placeholder="Type the confirmation text above" 
                                   style="width: 100%; margin-bottom: 1rem;">
                        </div>
                        <button type="submit" class="btn btn-danger" id="hardDeleteBtn" disabled>
                            Permanently Delete User
                        </button>
                    </form>
                </div>
            </div>

            <!-- Back to Safety -->
            <div class="back-option">
                <a href="users.php" class="btn btn-primary">← Go Back to Users List</a>
                <p class="text-muted">No changes will be made if you go back.</p>
            </div>
        </div>
    </main>
</div>

<style>
.delete-confirmation {
    max-width: 800px;
    margin: 0 auto;
}

.user-info-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.user-info-card h3 {
    color: var(--dark);
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.user-details {
    display: grid;
    gap: 0.8rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row label {
    font-weight: 600;
    color: var(--secondary);
    min-width: 120px;
}

.warning-box {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.warning-box h4 {
    color: #92400e;
    margin: 0 0 1rem 0;
}

.warning-box ul {
    margin: 1rem 0;
    padding-left: 1.5rem;
    color: #92400e;
}

.warning-box li {
    margin-bottom: 0.5rem;
}

.option-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.option-card.danger {
    border-color: #ef4444;
    background: #fef2f2;
}

.option-card h4 {
    margin: 0 0 1rem 0;
    color: var(--dark);
}

.option-card.danger h4 {
    color: #dc2626;
}

.option-card ul {
    margin: 1rem 0;
    padding-left: 1.5rem;
    color: var(--secondary);
}

.option-card.danger ul {
    color: #991b1b;
}

.deactivate-form {
    margin-top: 1rem;
}

.danger-zone {
    margin-top: 1rem;
    padding: 1rem;
    background: #fee2e2;
    border-radius: 8px;
    border: 1px solid #fca5a5;
}

.confirmation-text {
    background: #1e293b;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-family: monospace;
    margin: 1rem 0;
}

.back-option {
    text-align: center;
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f0f9ff;
    border: 1px solid #7dd3fc;
    border-radius: 10px;
}

.text-muted {
    color: #64748b;
    margin-top: 0.5rem;
    font-size: 0.9rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirm_text');
    const hardDeleteBtn = document.getElementById('hardDeleteBtn');
    const requiredText = "DELETE <?php echo $user['username']; ?>";
    
    confirmInput.addEventListener('input', function() {
        if (this.value === requiredText) {
            hardDeleteBtn.disabled = false;
            confirmInput.style.borderColor = '#10b981';
        } else {
            hardDeleteBtn.disabled = true;
            confirmInput.style.borderColor = '#ef4444';
        }
    });
    
    document.getElementById('hardDeleteForm').addEventListener('submit', function(e) {
        if (confirmInput.value !== requiredText) {
            e.preventDefault();
            alert('Confirmation text does not match.');
            return false;
        }
        
        if (!confirm('🚨 FINAL WARNING: This will PERMANENTLY delete the user. This action cannot be undone. Continue?')) {
            e.preventDefault();
            return false;
        }
        
        return true;
    });
});
</script>

<?php include '../includes/footer.php'; ?>