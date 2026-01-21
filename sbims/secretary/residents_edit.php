<?php
$page_title = "Edit Resident Record";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['secretary']);

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: residents.php");
    exit();
}

// Fetch resident data
$stmt = $db->prepare("SELECT * FROM residents WHERE id = ?");
$stmt->execute([$id]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) {
    $_SESSION['error'] = "Resident not found.";
    header("Location: residents.php");
    exit();
}

// Generate PWD ID if needed
if ($resident['is_pwd'] && empty($resident['pwd_id'])) {
    // Generate PWD ID: PWD-YYYY-XXXX
    $year = date('Y');
    $query = "SELECT COUNT(*) as count FROM residents WHERE is_pwd = 1 AND YEAR(updated_at) = :year";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':year', $year);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $sequence = $result['count'] + 1;
    $pwd_id = "PWD-$year-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    
    // Save generated PWD ID
    $update_pwd_stmt = $db->prepare("UPDATE residents SET pwd_id = ? WHERE id = ?");
    $update_pwd_stmt->execute([$pwd_id, $id]);
    $resident['pwd_id'] = $pwd_id;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $civil_status = $_POST['civil_status'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $purok = $_POST['purok'];
    $household_number = $_POST['household_number'];
    $address = $_POST['address'];
    $is_voter = isset($_POST['is_voter']) ? 1 : 0;
    $is_4ps = isset($_POST['is_4ps']) ? 1 : 0;
    $is_senior = isset($_POST['is_senior']) ? 1 : 0;
    $is_pwd = isset($_POST['is_pwd']) ? 1 : 0;
    $pwd_id = $_POST['pwd_id'] ?? '';
    $disability_type = $_POST['disability_type'] ?? '';
    
    // Auto-generate PWD ID if PWD is checked and no ID exists
    if ($is_pwd && empty($pwd_id)) {
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM residents WHERE is_pwd = 1 AND YEAR(updated_at) = :year";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $sequence = $result['count'] + 1;
        $pwd_id = "PWD-$year-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    // If PWD is unchecked, clear PWD ID
    elseif (!$is_pwd) {
        $pwd_id = '';
        $disability_type = '';
    }
    
    try {
        $update_stmt = $db->prepare("UPDATE residents SET 
            first_name = ?,
            last_name = ?,
            middle_name = ?,
            gender = ?,
            birthdate = ?,
            civil_status = ?,
            contact_number = ?,
            email = ?,
            purok = ?,
            household_number = ?,
            address = ?,
            is_voter = ?,
            is_4ps = ?,
            is_senior = ?,
            is_pwd = ?,
            pwd_id = ?,
            disability_type = ?,
            updated_at = NOW()
            WHERE id = ?");
        
        $update_stmt->execute([
            $first_name,
            $last_name,
            $middle_name,
            $gender,
            $birthdate,
            $civil_status,
            $contact_number,
            $email,
            $purok,
            $household_number,
            $address,
            $is_voter,
            $is_4ps,
            $is_senior,
            $is_pwd,
            $pwd_id,
            $disability_type,
            $id
        ]);
        
        // Log activity
        Auth::logActivity($_SESSION['user_id'], 'Update Resident', "Updated resident: {$first_name} {$last_name} (ID: {$id})");
        
        $_SESSION['success'] = "Resident record updated successfully.";
        header("Location: residents.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to update resident: " . $e->getMessage();
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
                <h1>Edit Resident Record</h1>
                <p>Update resident information</p>
            </div>
            <div class="page-actions">
                <a href="residents.php" class="btn btn-outline">← Back to Residents</a>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" id="editResidentForm">
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($resident['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($resident['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" 
                                   value="<?php echo htmlspecialchars($resident['middle_name'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo $resident['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $resident['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Birthdate *</label>
                            <input type="date" id="birthdate" name="birthdate" 
                                   value="<?php echo $resident['birthdate']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="civil_status">Civil Status *</label>
                            <select id="civil_status" name="civil_status" required>
                                <option value="">Select Status</option>
                                <option value="Single" <?php echo $resident['civil_status'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo $resident['civil_status'] == 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo $resident['civil_status'] == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Separated" <?php echo $resident['civil_status'] == 'Separated' ? 'selected' : ''; ?>>Separated</option>
                                <option value="Divorced" <?php echo $resident['civil_status'] == 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="text" id="age_display" value="<?php echo calculateAge($resident['birthdate']); ?> years old" readonly style="background: #f1f5f9;">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Contact Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_number">Contact Number *</label>
                            <input type="text" id="contact_number" name="contact_number" 
                                   value="<?php echo htmlspecialchars($resident['contact_number']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($resident['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="purok">Purok *</label>
                            <select id="purok" name="purok" required>
                                <option value="">Select Purok</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="Purok <?php echo $i; ?>" 
                                        <?php echo $resident['purok'] == "Purok $i" ? 'selected' : ''; ?>>
                                        Purok <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="household_number">Household Number *</label>
                            <input type="text" id="household_number" name="household_number" 
                                   value="<?php echo htmlspecialchars($resident['household_number']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Complete Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($resident['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Additional Information</h3>
                    <div class="form-row">
                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_voter" value="1" <?php echo $resident['is_voter'] ? 'checked' : ''; ?>>
                                <span>Registered Voter</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_4ps" value="1" <?php echo $resident['is_4ps'] ? 'checked' : ''; ?>>
                                <span>4PS Beneficiary</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_senior" value="1" <?php echo $resident['is_senior'] ? 'checked' : ''; ?>>
                                <span>Senior Citizen</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_pwd" value="1" id="is_pwd_checkbox" <?php echo $resident['is_pwd'] ? 'checked' : ''; ?>>
                                <span>Person with Disability (PWD)</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-row" id="pwd_fields" style="<?php echo $resident['is_pwd'] ? '' : 'display: none;'; ?>">
                        <div class="form-group">
                            <label for="pwd_id">PWD ID Number</label>
                            <input type="text" id="pwd_id" name="pwd_id" 
                                   value="<?php echo htmlspecialchars($resident['pwd_id'] ?? ''); ?>" readonly style="background-color: #f3f4f6;">
                            <small>Auto-generated when PWD is checked</small>
                        </div>
                        <div class="form-group">
                            <label for="disability_type">Disability Type</label>
                            <select id="disability_type" name="disability_type">
                                <option value="">Select Disability Type</option>
                                <option value="Visual Impairment" <?php echo ($resident['disability_type'] ?? '') == 'Visual Impairment' ? 'selected' : ''; ?>>Visual Impairment</option>
                                <option value="Hearing Impairment" <?php echo ($resident['disability_type'] ?? '') == 'Hearing Impairment' ? 'selected' : ''; ?>>Hearing Impairment</option>
                                <option value="Physical Disability" <?php echo ($resident['disability_type'] ?? '') == 'Physical Disability' ? 'selected' : ''; ?>>Physical Disability</option>
                                <option value="Intellectual Disability" <?php echo ($resident['disability_type'] ?? '') == 'Intellectual Disability' ? 'selected' : ''; ?>>Intellectual Disability</option>
                                <option value="Psychosocial Disability" <?php echo ($resident['disability_type'] ?? '') == 'Psychosocial Disability' ? 'selected' : ''; ?>>Psychosocial Disability</option>
                                <option value="Speech Disability" <?php echo ($resident['disability_type'] ?? '') == 'Speech Disability' ? 'selected' : ''; ?>>Speech Disability</option>
                                <option value="Multiple Disabilities" <?php echo ($resident['disability_type'] ?? '') == 'Multiple Disabilities' ? 'selected' : ''; ?>>Multiple Disabilities</option>
                                <option value="Other" <?php echo ($resident['disability_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Resident</button>
                    <a href="residents.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Show/hide PWD fields based on checkbox
document.getElementById('is_pwd_checkbox').addEventListener('change', function() {
    const pwdFields = document.getElementById('pwd_fields');
    const pwdIdInput = document.getElementById('pwd_id');
    
    if (this.checked) {
        pwdFields.style.display = 'block';
        
        // Auto-generate PWD ID if empty
        if (!pwdIdInput.value) {
            const year = new Date().getFullYear();
            // Generate random 4-digit number for demo
            // In real app, this would come from server
            const randomNum = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
            pwdIdInput.value = `PWD-${year}-${randomNum}`;
        }
    } else {
        pwdFields.style.display = 'none';
        // Clear PWD ID when unchecked
        pwdIdInput.value = '';
    }
});

// Form validation
document.getElementById('editResidentForm').addEventListener('submit', function(e) {
    const birthdate = new Date(document.getElementById('birthdate').value);
    const today = new Date();
    
    // Validate birthdate is not in future
    if (birthdate > today) {
        e.preventDefault();
        alert('Birthdate cannot be in the future.');
        return false;
    }
    
    // Validate age (optional: add minimum age requirement)
    const ageInYears = calculateAgeFromDate(birthdate);
    if (ageInYears < 0) {
        e.preventDefault();
        alert('Age cannot be negative.');
        return false;
    }
    
    // Validate contact number
    const contactNumber = document.getElementById('contact_number').value;
    const phoneRegex = /^[0-9]{11}$/;
    if (contactNumber && !phoneRegex.test(contactNumber)) {
        e.preventDefault();
        alert('Please enter a valid 11-digit contact number.');
        return false;
    }
    
    return true;
});

// Recalculate age when birthdate changes
document.getElementById('birthdate').addEventListener('change', function() {
    const birthdate = new Date(this.value);
    const age = calculateAgeFromDate(birthdate);
    
    // Update the age display
    const ageInput = document.getElementById('age_display');
    if (ageInput) {
        ageInput.value = age + ' years old';
    }
    
    // Auto-check Senior Citizen if age >= 60
    const seniorCheckbox = document.querySelector('input[name="is_senior"]');
    if (age >= 60 && !seniorCheckbox.checked) {
        if (confirm('This resident appears to be a Senior Citizen (age 60+). Would you like to mark them as Senior Citizen?')) {
            seniorCheckbox.checked = true;
        }
    }
});

// Helper function to calculate age from date
function calculateAgeFromDate(birthdate) {
    const today = new Date();
    let age = today.getFullYear() - birthdate.getFullYear();
    const monthDiff = today.getMonth() - birthdate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
        age--;
    }
    
    return age;
}

// Generate PWD ID on page load if PWD is checked but ID is empty
window.addEventListener('load', function() {
    const isPwdChecked = document.getElementById('is_pwd_checkbox').checked;
    const pwdIdInput = document.getElementById('pwd_id');
    
    if (isPwdChecked && !pwdIdInput.value) {
        const year = new Date().getFullYear();
        const randomNum = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        pwdIdInput.value = `PWD-${year}-${randomNum}`;
    }
});
</script>

<style>
.form-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-section h3 {
    color: var(--dark);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary);
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
}

#pwd_fields {
    background: #f0f9ff;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #7dd3fc;
    margin-top: 1rem;
}

/* Age field styling */
input[readonly] {
    cursor: not-allowed;
    opacity: 0.8;
}

#pwd_id {
    font-weight: bold;
    color: var(--primary);
}
</style>

<?php include '../includes/footer.php'; ?>