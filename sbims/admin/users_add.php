<?php
$page_title = "Add New User";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['admin']);

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = sanitizeInput($_POST['email']);
    $role = $_POST['role'];
    $full_name = sanitizeInput($_POST['full_name']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    $errors = [];
    
    // Check if username exists
    $check_user = $db->prepare("SELECT id FROM users WHERE username = :username");
    $check_user->bindParam(':username', $username);
    $check_user->execute();
    
    if ($check_user->rowCount() > 0) {
        $errors[] = "Username already exists.";
    }
    
    // Check email if provided
    if (!empty($email)) {
        $check_email = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check_email->bindParam(':email', $email);
        $check_email->execute();
        
        if ($check_email->rowCount() > 0) {
            $errors[] = "Email already registered.";
        }
    }
    
    // Password validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, password, email, role, full_name, is_active) 
                  VALUES (:username, :password, :email, :role, :full_name, :is_active)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':is_active', $is_active);
        
        if ($stmt->execute()) {
            // Get the new user ID
            $new_user_id = $db->lastInsertId();
            
            // Log activity
            Auth::logActivity($_SESSION['user_id'], 'Add User', "Added new user: $full_name ($role)");
            
            // Create notification for the new user if active
            if ($is_active) {
                createNotification(
                    $new_user_id,
                    'Account Created',
                    "Your account has been created. Username: $username",
                    'info',
                    '../login.php'
                );
            }
            
            $_SESSION['success'] = "User added successfully!";
            header("Location: users.php");
            exit();
        } else {
            $errors[] = "Failed to add user. Please try again.";
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
            <h1>Add New User</h1>
            <p>Create a new system user account</p>
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
            <form method="POST" id="addUserForm">
                <h3>Account Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Choose a username">
                        <small>Must be unique, 3-20 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" 
                               placeholder="user@example.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Minimum 8 characters">
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Re-enter password">
                        <div class="password-match" id="passwordMatch"></div>
                    </div>
                </div>

                <h3>User Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required 
                               placeholder="Enter full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="role">User Role *</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">System Administrator</option>
                            <option value="captain">Barangay Captain</option>
                            <option value="secretary">Barangay Secretary</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label for="is_active" style="margin: 0;">Account is active (user can login)</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="users.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Role Permissions Info -->
        <div class="info-box">
            <h4>👥 Role Permissions Summary</h4>
            <div class="permissions-grid">
                <div class="permission-item">
                    <h5>System Administrator</h5>
                    <ul>
                        <li>Full system access</li>
                        <li>User management</li>
                        <li>System configuration</li>
                        <li>Backup & restore</li>
                    </ul>
                </div>
                <div class="permission-item">
                    <h5>Barangay Captain</h5>
                    <ul>
                        <li>Certificate approvals</li>
                        <li>Blotter case oversight</li>
                        <li>Announcements</li>
                        <li>Reports viewing</li>
                    </ul>
                </div>
                <div class="permission-item">
                    <h5>Barangay Secretary</h5>
                    <ul>
                        <li>Resident management</li>
                        <li>Blotter case handling</li>
                        <li>Certificate issuance</li>
                        <li>Report generation</li>
                    </ul>
                </div>
               
            </div>
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

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.permission-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
}

.permission-item h5 {
    margin: 0 0 0.5rem 0;
    color: var(--dark);
}

.permission-item ul {
    margin: 0;
    padding-left: 1.2rem;
    font-size: 0.9rem;
    color: var(--secondary);
}

.permission-item li {
    margin-bottom: 0.3rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('passwordStrength');
    const matchBar = document.getElementById('passwordMatch');
    
    passwordInput.addEventListener('input', checkPasswordStrength);
    confirmInput.addEventListener('input', checkPasswordMatch);
    
    function checkPasswordStrength() {
        const password = passwordInput.value;
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Complexity checks
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        // Update strength bar
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
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
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
        
        return true;
    });
});
</script>

<?php include '../includes/footer.php'; ?>