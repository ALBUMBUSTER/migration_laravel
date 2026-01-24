<?php
$page_title = "Edit User";
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

// Get user details
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $role = $_POST['role'];
    $full_name = sanitizeInput($_POST['full_name']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $change_password = isset($_POST['change_password']) ? 1 : 0;
    
    $errors = [];
    
    // Check if username changed and if it's unique
    if ($username != $user['username']) {
        $check_user = $db->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $check_user->bindParam(':username', $username);
        $check_user->bindParam(':id', $user_id);
        $check_user->execute();
        
        if ($check_user->rowCount() > 0) {
            $errors[] = "Username already exists.";
        }
    }
    
    // Check email if changed
    if ($email != $user['email']) {
        $check_email = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $check_email->bindParam(':email', $email);
        $check_email->bindParam(':id', $user_id);
        $check_email->execute();
        
        if ($check_email->rowCount() > 0) {
            $errors[] = "Email already registered.";
        }
    }
    
    // Handle password change if requested
    if ($change_password) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }
    
    if (empty($errors)) {
        // Build update query
        $update_fields = [
            'username = :username',
            'email = :email',
            'role = :role',
            'full_name = :full_name',
            'is_active = :is_active'
        ];
        
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':role' => $role,
            ':full_name' => $full_name,
            ':is_active' => $is_active,
            ':id' => $user_id
        ];
        
        // Add password update if changed
        if ($change_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_fields[] = 'password = :password';
            $params[':password'] = $hashed_password;
        }
        
        $query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            // Log activity
            $action = $user['is_active'] != $is_active ? 
                     ($is_active ? 'Activate User' : 'Deactivate User') : 'Update User';
            Auth::logActivity($_SESSION['user_id'], $action, 
                "Updated user: {$user['full_name']} (ID: {$user_id})");
            
            // Create notification for the user if status changed
            if ($user['is_active'] != $is_active) {
                $status = $is_active ? 'activated' : 'deactivated';
                createNotification(
                    $user_id,
                    'Account Status Changed',
                    "Your account has been {$status} by the administrator.",
                    $is_active ? 'success' : 'warning',
                    '../login.php'
                );
            }
            
            $_SESSION['success'] = "User updated successfully!";
            header("Location: users.php");
            exit();
        } else {
            $errors[] = "Failed to update user. Please try again.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Edit User</h1>
                <p>Update user account information</p>
            </div>
            <div class="page-actions">
                <a href="users.php" class="btn btn-outline">Back to Users</a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" id="editUserForm">
                <h3>Account Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="change_password" name="change_password" value="1">
                        <label for="change_password" style="margin: 0;">Change Password</label>
                    </div>
                </div>

                <div id="passwordFields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password">
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                            <div class="password-match" id="passwordMatch"></div>
                        </div>
                    </div>
                </div>

                <h3>User Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">User Role *</label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>System Administrator</option>
                            <option value="captain" <?php echo $user['role'] == 'captain' ? 'selected' : ''; ?>>Barangay Captain</option>
                            <option value="secretary" <?php echo $user['role'] == 'secretary' ? 'selected' : ''; ?>>Barangay Secretary</option>
                            <option value="resident" <?php echo $user['role'] == 'resident' ? 'selected' : ''; ?>>Resident</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" id="is_active" name="is_active" value="1" 
                                   <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                            <label for="is_active" style="margin: 0;">Account is active</label>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="info-box">
                    <h4>📊 Account Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>User ID:</label>
                            <span><?php echo $user['id']; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Created:</label>
                            <span><?php echo formatDateTime($user['created_at']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Last Login:</label>
                            <span><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Current Status:</label>
                            <span class="status <?php echo $user['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="users.php" class="btn btn-outline">Cancel</a>
                    <?php if ($_SESSION['user_id'] != $user_id): ?>
                        <a href="users_delete.php?id=<?php echo $user_id; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this user?')">Delete User</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
.password-strength, .password-match {
    height: 4px;
    margin-top: 0.3rem;
    border-radius: 2px;
    transition: all 0.3s;
}

.password-strength.weak { background: #ef4444; width: 33%; }
.password-strength.medium { background: #f59e0b; width: 66%; }
.password-strength.strong { background: #10b981; width: 100%; }

.password-match.match { background: #10b981; width: 100%; }
.password-match.mismatch { background: #ef4444; width: 100%; }

.info-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const changePasswordCheckbox = document.getElementById('change_password');
    const passwordFields = document.getElementById('passwordFields');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('passwordStrength');
    const matchBar = document.getElementById('passwordMatch');
    
    // Toggle password fields
    changePasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordFields.style.display = 'block';
            passwordInput.required = true;
            confirmInput.required = true;
        } else {
            passwordFields.style.display = 'none';
            passwordInput.required = false;
            confirmInput.required = false;
            passwordInput.value = '';
            confirmInput.value = '';
        }
    });
    
    // Password strength check
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
    
    function checkPasswordStrength() {
        const password = passwordInput.value;
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        strengthBar.className = 'password-strength';
        if (password.length === 0) {
            strengthBar.style.width = '0%';
        } else if (strength <= 2) {
            strengthBar.classList.add('weak');
        } else if (strength <= 4) {
            strengthBar.classList.add('medium');
        } else {
            strengthBar.classList.add('strong');
        }
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        matchBar.className = 'password-match';
        if (confirm.length === 0) {
            matchBar.style.width = '0%';
        } else if (password === confirm) {
            matchBar.classList.add('match');
        } else {
            matchBar.classList.add('mismatch');
        }
    }
    
    // Form validation
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        if (changePasswordCheckbox.checked) {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                passwordInput.focus();
                return false;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match.');
                confirmInput.focus();
                return false;
            }
        }
        
        return true;
    });
});
</script>

<?php include '../includes/footer.php'; ?>