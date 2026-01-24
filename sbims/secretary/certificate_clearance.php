<?php
$page_title = "Barangay Clearance";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['secretary']);

$database = new Database();
$db = $database->getConnection();

// Get residents for selection
$residents_query = "SELECT id, resident_id, first_name, last_name, address, purok FROM residents ORDER BY first_name, last_name";
$residents_stmt = $db->prepare($residents_query);
$residents_stmt->execute();
$residents = $residents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get barangay info
$barangay_query = "SELECT * FROM barangay_info LIMIT 1";
$barangay_stmt = $db->prepare($barangay_query);
$barangay_stmt->execute();
$barangay_info = $barangay_stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resident_id = $_POST['resident_id'];
    $purpose = sanitizeInput($_POST['purpose']);
    $or_number = sanitizeInput($_POST['or_number']);
    $amount_paid = $_POST['amount_paid'];
    
    // Generate certificate ID
    $certificate_id = generateCertificateID($db, 'Clearance');
    $issued_by = $_SESSION['user_id'];
    
    $query = "INSERT INTO certificates (certificate_id, resident_id, certificate_type, purpose, or_number, amount_paid, issued_by, status) 
              VALUES (:certificate_id, :resident_id, 'Clearance', :purpose, :or_number, :amount_paid, :issued_by, 'Pending')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':certificate_id', $certificate_id);
    $stmt->bindParam(':resident_id', $resident_id);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':or_number', $or_number);
    $stmt->bindParam(':amount_paid', $amount_paid);
    $stmt->bindParam(':issued_by', $issued_by);
    
    try {
        if ($stmt->execute()) {
            // Get resident info for notification
            $resident_query = "SELECT first_name, last_name FROM residents WHERE id = ?";
            $resident_stmt = $db->prepare($resident_query);
            $resident_stmt->execute([$resident_id]);
            $resident = $resident_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resident) {
                $first_name = $resident['first_name'];
                $last_name = $resident['last_name'];
                
                // Create notification for Captain
                $captain_query = "SELECT id FROM users WHERE role = 'captain' LIMIT 1";
                $captain_stmt = $db->prepare($captain_query);
                $captain_stmt->execute();
                $captain = $captain_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($captain) {
                    // Make sure createNotification function exists in your functions.php
                    createNotification(
                        $captain['id'],
                        'New Certificate Request',
                        "{$first_name} {$last_name} requested Barangay Clearance",
                        'warning',
                        'captain/approvals.php'
                    );
                }
                
                // Log activity
                Auth::logActivity($_SESSION['user_id'], 'Issue Certificate', "Issued Barangay Clearance: $certificate_id");
                
                $_SESSION['success'] = "Barangay Clearance generated successfully!";
                header("Location: certificates.php");
                exit();
            }
        } else {
            $error = "Failed to generate certificate. Please try again.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// If editing existing certificate
$edit_certificate = null;
if (isset($_GET['id'])) {
    $cert_id = $_GET['id'];
    $edit_query = "SELECT c.*, r.first_name, r.last_name, r.address, r.purok, r.birthdate, r.civil_status 
                   FROM certificates c 
                   JOIN residents r ON c.resident_id = r.id 
                   WHERE c.id = :id";
    $edit_stmt = $db->prepare($edit_query);
    $edit_stmt->bindParam(':id', $cert_id);
    $edit_stmt->execute();
    $edit_certificate = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Word export request
if (isset($_GET['export_word']) && $edit_certificate) {
    exportToWord($edit_certificate, $barangay_info);
    exit();
}

// Function to export to Word
function exportToWord($certificate, $barangay_info) {
    $filename = "Barangay_Clearance_" . str_replace(' ', '_', $certificate['first_name'] . '_' . $certificate['last_name']) . ".doc";
    
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Barangay Clearance</title>
        <style>
            body {
                font-family: "Times New Roman", serif;
                margin: 2.5cm;
                font-size: 12pt;
                line-height: 1.5;
            }
            .certificate-header {
                text-align: center;
                margin-bottom: 2cm;
                border-bottom: 2px solid #000;
                padding-bottom: 0.5cm;
            }
            .certificate-header h1 {
                font-size: 24pt;
                margin-bottom: 0.5cm;
                color: #000;
            }
            .to-whom {
                font-weight: bold;
                margin-bottom: 1cm;
                text-align: center;
            }
            .certificate-text {
                text-align: justify;
                margin-bottom: 0.5cm;
                text-indent: 1cm;
            }
            .certificate-footer {
                margin-top: 3cm;
                display: flex;
                justify-content: space-between;
            }
            .signature-area {
                text-align: center;
                width: 50%;
            }
            .signature-line {
                width: 8cm;
                border-bottom: 1px solid #000;
                margin: 0 auto 0.5cm auto;
            }
            .certificate-number {
                text-align: right;
                font-size: 10pt;
                color: #666;
            }
            strong {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="certificate-header">
            <h1>BARANGAY CLEARANCE</h1>
            <p>Republic of the Philippines</p>
            <p>Province of Leyte</p>
            <p>Municipality of Isabel</p>
            <p><strong>BARANGAY LIBERTAD</strong></p>
        </div>
        
        <div class="to-whom">TO WHOM IT MAY CONCERN:</div>
        
        <div class="certificate-text">
            This is to certify that <strong>' . htmlspecialchars($certificate['first_name'] . ' ' . $certificate['last_name']) . '</strong>, 
            of legal age, <strong>' . htmlspecialchars($certificate['civil_status'] ?? '') . '</strong>, 
            and a resident of <strong>' . htmlspecialchars($certificate['address'] ?? '') . '</strong>, 
            Purok <strong>' . htmlspecialchars($certificate['purok'] ?? '') . '</strong>, Barangay Libertad, Isabel, Leyte, 
            is known to me to be a person of good moral character and a law-abiding citizen.
        </div>
        
        <div class="certificate-text">
            This certification is issued upon the request of the above-mentioned person for <strong>' . htmlspecialchars($certificate['purpose'] ?? '') . '</strong>.
        </div>
        
        <div class="certificate-text">
            Issued this <strong>' . date('jS') . '</strong> day of <strong>' . date('F Y') . '</strong> at Barangay Libertad, Isabel, Leyte.
        </div>
        
        <div class="certificate-footer">
            <div class="signature-area">
                <div class="signature-line"></div>
                <p><strong>' . (isset($barangay_info['barangay_captain']) && $barangay_info['barangay_captain'] ? htmlspecialchars($barangay_info['barangay_captain']) : 'BARANGAY CAPTAIN') . '</strong></p>
                <p>Punong Barangay</p>
            </div>
            
            <div class="certificate-number">
                <p>Certificate ID: <strong>' . htmlspecialchars($certificate['certificate_id'] ?? '') . '</strong></p>';
                
                if (isset($certificate['or_number']) && !empty($certificate['or_number'])) {
                    $html .= '<p>OR Number: <strong>' . htmlspecialchars($certificate['or_number']) . '</strong></p>';
                }
                
    $html .= '</div>
        </div>
    </body>
    </html>';
    
    echo $html;
    exit();
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <h1>Barangay Clearance</h1>
            <p>Issue barangay clearance certificate</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <h3>Resident Information</h3>
                <div class="form-group">
                    <label for="resident_id">Select Resident *</label>
                    <select id="resident_id" name="resident_id" required <?php echo $edit_certificate ? 'disabled' : ''; ?>>
                        <option value="">Select Resident</option>
                        <?php foreach ($residents as $resident): ?>
                            <option value="<?php echo $resident['id']; ?>" 
                                <?php echo $edit_certificate && $edit_certificate['resident_id'] == $resident['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name'] . ' - ' . $resident['purok']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($edit_certificate): ?>
                        <input type="hidden" name="resident_id" value="<?php echo $edit_certificate['resident_id']; ?>">
                    <?php endif; ?>
                </div>

                <?php if ($edit_certificate): ?>
                <div class="resident-details">
                    <h4>Resident Details:</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($edit_certificate['first_name'] . ' ' . $edit_certificate['last_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Address:</label>
                            <span><?php echo htmlspecialchars($edit_certificate['address'] ?? ''); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Purok:</label>
                            <span><?php echo htmlspecialchars($edit_certificate['purok'] ?? ''); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Civil Status:</label>
                            <span><?php echo htmlspecialchars($edit_certificate['civil_status'] ?? ''); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <h3>Certificate Details</h3>
                <div class="form-group">
                    <label for="purpose">Purpose *</label>
                    <textarea id="purpose" name="purpose" rows="3" placeholder="State the purpose for this barangay clearance..." required><?php echo $edit_certificate ? htmlspecialchars($edit_certificate['purpose'] ?? '') : ''; ?></textarea>
                </div>

                <h3>Payment Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="or_number">OR Number</label>
                        <input type="text" id="or_number" name="or_number" value="<?php echo $edit_certificate ? htmlspecialchars($edit_certificate['or_number'] ?? '') : ''; ?>" placeholder="Optional">
                    </div>
                    
                    <div class="form-group">
                        <label for="amount_paid">Amount Paid</label>
                        <input type="number" id="amount_paid" name="amount_paid" step="0.01" value="<?php echo $edit_certificate ? htmlspecialchars($edit_certificate['amount_paid'] ?? '0') : '0'; ?>" placeholder="0.00">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_certificate ? 'Update Certificate' : 'Generate Certificate'; ?>
                    </button>
                    <a href="certificates.php" class="btn btn-outline">Cancel</a>
                    
                    <?php if ($edit_certificate && ($edit_certificate['status'] ?? '') == 'Approved'): ?>
                        <button type="button" class="btn btn-success" onclick="printCertificate()">Print Certificate</button>
                        <a href="?id=<?php echo $_GET['id']; ?>&export_word=1" class="btn btn-info">Export to Word</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Certificate Preview -->
        <?php if ($edit_certificate): ?>
        <div class="certificate-preview" id="certificatePreview">
            <div class="certificate-header">
    <div class="barangay-logo">
        <?php if (file_exists('../assets/img/barangay-logo1.png')): ?>
            <img src="../assets/img/barangay-logo1.png" alt="Barangay Libertad Seal" class="certificate-logo-img">
        <?php elseif (file_exists('../assets/img/logo1.png')): ?>
            <img src="../assets/img/logo1.png" alt="Barangay Libertad Seal" class="certificate-logo-img">
        <?php else: ?>
            <!-- Fallback to text if no logo -->
            <div class="barangay-logo-text">BL</div>
        <?php endif; ?>
    </div>
    <div class="barangay-info">
        <h2>BARANGAY CLEARANCE</h2>
        <p>Republic of the Philippines</p>
        <p>Province of Leyte</p>
        <p>Municipality of Isabel</p>
        <p><strong>BARANGAY LIBERTAD</strong></p>
    </div>
</div>
            <div class="certificate-body">
                <p class="to-whom">TO WHOM IT MAY CONCERN:</p>
                
                <p class="certificate-text">
                    This is to certify that <strong><?php echo htmlspecialchars($edit_certificate['first_name'] . ' ' . $edit_certificate['last_name']); ?></strong>, 
                    of legal age, <strong><?php echo htmlspecialchars($edit_certificate['civil_status'] ?? ''); ?></strong>, 
                    and a resident of <strong><?php echo htmlspecialchars($edit_certificate['address'] ?? ''); ?></strong>, 
                    Purok <strong><?php echo htmlspecialchars($edit_certificate['purok'] ?? ''); ?></strong>, Barangay Libertad, Isabel, Leyte, 
                    is known to me to be a person of good moral character and a law-abiding citizen.
                </p>
                
                <p class="certificate-text">
                    This certification is issued upon the request of the above-mentioned person for <strong><?php echo htmlspecialchars($edit_certificate['purpose'] ?? ''); ?></strong>.
                </p>
                
                <p class="certificate-text">
                    Issued this <strong><?php echo date('jS'); ?></strong> day of <strong><?php echo date('F Y'); ?></strong> at Barangay Libertad, Isabel, Leyte.
                </p>
            </div>

            <div class="certificate-footer">
                <div class="signature-area">
                    <div class="signature-line"></div>
                    <p><strong><?php echo isset($barangay_info['barangay_captain']) && $barangay_info['barangay_captain'] ? htmlspecialchars($barangay_info['barangay_captain']) : 'BARANGAY CAPTAIN'; ?></strong></p>
                    <p>Punong Barangay</p>
                </div>
                
                <div class="certificate-number">
                    <p>Certificate ID: <strong><?php echo htmlspecialchars($edit_certificate['certificate_id'] ?? ''); ?></strong></p>
                    <?php if (isset($edit_certificate['or_number']) && !empty($edit_certificate['or_number'])): ?>
                        <p>OR Number: <strong><?php echo htmlspecialchars($edit_certificate['or_number']); ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<style>
/* Screen styling */
.certificate-preview {
    background: white;
    border: 2px solid #1e40af;
    padding: 2rem;
    margin-top: 2rem;
    font-family: 'Times New Roman', serif;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 800px; /* A4 width is 210mm ≈ 800px */
    margin-left: auto;
    margin-right: auto;
}

/* PRINT-SPECIFIC STYLING */
@media print {
    body * {
        visibility: hidden;
    }
    
    .certificate-preview,
    .certificate-preview * {
        visibility: visible;
    }
    
    .certificate-preview {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 1.5cm; /* A4 paper margins */
        border: none;
        box-shadow: none;
        background: white;
        page-break-after: always;
    }
    
    /* Hide unnecessary elements for print */
    .no-print {
        display: none !important;
    }
    
    /* Adjust font sizes for print */
    .barangay-info h2 {
        font-size: 24pt;
    }
    
    .barangay-info p {
        font-size: 12pt;
    }
    
    .certificate-text {
        font-size: 12pt;
        line-height: 1.8;
    }
    
    /* Ensure proper spacing */
    .certificate-header {
        margin-bottom: 1.5cm;
    }
    
    .certificate-body {
        margin: 1.5cm 0;
    }
    
    .certificate-footer {
        margin-top: 2cm;
        position: relative;
    }
}

.certificate-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    text-align: center;
    border-bottom: 2px solid #1e40af;
    padding-bottom: 1.5rem;
}

.barangay-logo {
    width: 90px;
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.certificate-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 50%;
    border: 2px solid #1e40af;
    background: white;
    padding: 5px;
}

.barangay-info h2 {
    color: #1e40af;
    margin: 0;
    font-size: 1.8rem;
}

.barangay-info p {
    margin: 0.2rem 0;
    font-size: 0.95rem;
}

.to-whom {
    font-weight: bold;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}

.certificate-text {
    text-align: justify;
    line-height: 1.6;
    margin-bottom: 1rem;
    text-indent: 2rem;
    font-size: 1rem;
}

.certificate-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 3rem;
}

.signature-area {
    text-align: center;
    flex: 1;
}

.signature-line {
    width: 250px;
    border-bottom: 1px solid #000;
    margin-bottom: 0.5rem;
    margin-left: auto;
    margin-right: auto;
}

.certificate-number {
    text-align: right;
    font-size: 0.9rem;
}

.resident-details {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.resident-details h4 {
    margin-bottom: 1rem;
    color: var(--dark);
}

/* Export button styling */
.btn-info {
    background: #0ea5e9;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
}

.btn-info:hover {
    background: #0284c7;
}
</style>

<script>
function printCertificate() {
    // Store original content
    var originalContent = document.body.innerHTML;
    
    // Get certificate content
    var certificateContent = document.getElementById('certificatePreview').innerHTML;
    
    // Create print window
    var printWindow = window.open('', '_blank', 'width=800,height=600');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Barangay Clearance - Print</title>
            <style>
                @media print {
                    @page {
                        size: A4;
                        margin: 1.5cm;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: 'Times New Roman', serif;
                    }
                    .certificate-container {
                        width: 100%;
                        height: 100%;
                        padding: 1.5cm;
                    }
                    .barangay-info h2 {
                        font-size: 24pt;
                        color: #000;
                    }
                    .certificate-text {
                        font-size: 12pt;
                        line-height: 1.8;
                    }
                    .signature-line {
                        width: 250px;
                        border-bottom: 1px solid #000;
                        margin: 20px auto;
                    }
                }
                @media screen {
                    .certificate-container {
                        width: 210mm;
                        min-height: 297mm;
                        margin: 20px auto;
                        padding: 20mm;
                        border: 1px solid #ddd;
                        font-family: 'Times New Roman', serif;
                    }
                }
            </style>
        </head>
        <body>
            <div class="certificate-container">
                ${certificateContent}
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() {
                        window.close();
                    }, 1000);
                };
            <\/script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}
</script>

<?php include '../includes/footer.php'; ?>