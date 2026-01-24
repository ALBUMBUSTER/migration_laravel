<?php
$page_title = "Certificate Requests";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['secretary']);

$database = new Database();
$db = $database->getConnection();

// Handle print/reprint BEFORE other handlers
if (isset($_GET['print']) || isset($_GET['reprint'])) {
    $cert_id = isset($_GET['print']) ? $_GET['print'] : $_GET['reprint'];
    
    // Log reprint activity if it's a reprint
    if (isset($_GET['reprint'])) {
        $cert_query = "SELECT c.certificate_id, r.first_name, r.last_name 
                       FROM certificates c 
                       JOIN residents r ON c.resident_id = r.id 
                       WHERE c.id = ?";
        $cert_stmt = $db->prepare($cert_query);
        $cert_stmt->execute([$cert_id]);
        $certificate_info = $cert_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($certificate_info) {
            Auth::logActivity($_SESSION['user_id'], 'Reprint Certificate', 
                "Reprinted certificate: {$certificate_info['certificate_id']} for {$certificate_info['first_name']} {$certificate_info['last_name']}");
        }
    }
    
    // Fetch certificate details for printing
    $query = "SELECT c.*, r.*, u1.full_name as issued_by_name, u2.full_name as approved_by_name
              FROM certificates c 
              JOIN residents r ON c.resident_id = r.id 
              LEFT JOIN users u1 ON c.issued_by = u1.id 
              LEFT JOIN users u2 ON c.approved_by = u2.id
              WHERE c.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$cert_id]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($certificate) {
        // Determine which certificate template to use
        switch ($certificate['certificate_type']) {
            case 'Clearance':
                $template = 'print_clearance.php';
                break;
            case 'Indigency':
                $template = 'print_indigency.php';
                break;
            case 'Residency':
                $template = 'print_residency.php';
                break;
            default:
                $template = 'print_clearance.php';
        }
        
        // Redirect to print template
        header("Location: $template?id=" . $cert_id);
        exit();
    } else {
        $_SESSION['error'] = "Certificate not found.";
        header("Location: certificates.php");
        exit();
    }
}

// Handle approval BEFORE querying certificates
if (isset($_GET['approve'])) {
    $cert_id = $_GET['approve'];
    
    // Get certificate info for logging
    $cert_query = "SELECT c.certificate_id, r.first_name, r.last_name 
                   FROM certificates c 
                   JOIN residents r ON c.resident_id = r.id 
                   WHERE c.id = ?";
    $cert_stmt = $db->prepare($cert_query);
    $cert_stmt->execute([$cert_id]);
    $certificate_info = $cert_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($certificate_info) {
        $approve_query = "UPDATE certificates SET status = 'Approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
        $approve_stmt = $db->prepare($approve_query);
        
        if ($approve_stmt->execute([$_SESSION['user_id'], $cert_id])) {
            // Log activity
            Auth::logActivity($_SESSION['user_id'], 'Approve Certificate', 
                "Approved certificate: {$certificate_info['certificate_id']} for {$certificate_info['first_name']} {$certificate_info['last_name']}");
            
            $_SESSION['success'] = "Certificate approved successfully!";
            header("Location: certificates.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to approve certificate.";
        }
    } else {
        $_SESSION['error'] = "Certificate not found.";
    }
    
    header("Location: certificates.php");
    exit();
}

// Search and filter
$search = $_GET['search'] ?? '';
$certificate_type = $_GET['certificate_type'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$query = "SELECT c.*, r.first_name, r.last_name, r.address, r.purok, 
                 u1.full_name as issued_by_name, u2.full_name as approved_by_name
          FROM certificates c 
          JOIN residents r ON c.resident_id = r.id 
          LEFT JOIN users u1 ON c.issued_by = u1.id 
          LEFT JOIN users u2 ON c.approved_by = u2.id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (c.certificate_id LIKE :search OR r.first_name LIKE :search OR r.last_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($certificate_type)) {
    $query .= " AND c.certificate_type = :certificate_type";
    $params[':certificate_type'] = $certificate_type;
}

if (!empty($status)) {
    $query .= " AND c.status = :status";
    $params[':status'] = $status;
}

if (!empty($date_from)) {
    $query .= " AND DATE(c.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(c.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<style>
    /* Button visibility fix for "Mark as Released" */
    .btn-info {
        background-color: #0ea5e9 !important;
        color: white !important;
        border-color: #0ea5e9 !important;
    }
    
    .btn-info:hover {
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
    }
    
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 13px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        margin: 1px;
    }
    
    .btn-outline {
        background-color: transparent;
        border-color: #d1d5db;
        color: #374151;
    }
    
    .btn-outline:hover {
        background-color: #f9fafb;
        border-color: #9ca3af;
    }
    
    .btn-primary {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .btn-primary:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    
    .btn-success {
        background-color: #10b981;
        color: white;
        border-color: #10b981;
    }
    
    .btn-success:hover {
        background-color: #059669;
        border-color: #059669;
    }
    
    .btn-danger {
        background-color: #ef4444;
        color: white;
        border-color: #ef4444;
    }
    
    .btn-danger:hover {
        background-color: #dc2626;
        border-color: #dc2626;
    }
    
    /* Status badges */
    .status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fbbf24;
    }
    
    .status-approved {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    
    .status-released {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #3b82f6;
    }
    
    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
</style>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Certificate Requests</h1>
                <p>Manage barangay certificate issuance</p>
            </div>
            <div class="page-actions">
                <a href="certificate_clearance.php" class="btn btn-primary">Issue Certificate</a>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="data-table">
            <div class="table-header">
                <h3>Certificate Requests</h3>
                <div class="table-actions">
                    <form method="GET" class="search-form" style="display: flex; gap: 0.5rem; align-items: center;">
                        <select name="certificate_type" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="Clearance" <?php echo $certificate_type == 'Clearance' ? 'selected' : ''; ?>>Clearance</option>
                            <option value="Indigency" <?php echo $certificate_type == 'Indigency' ? 'selected' : ''; ?>>Indigency</option>
                            <option value="Residency" <?php echo $certificate_type == 'Residency' ? 'selected' : ''; ?>>Residency</option>
                        </select>
                        
                        <select name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo $status == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Released" <?php echo $status == 'Released' ? 'selected' : ''; ?>>Released</option>
                            <option value="Rejected" <?php echo $status == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                        
                        <input type="date" name="date_from" placeholder="From Date" value="<?php echo $date_from; ?>">
                        <input type="date" name="date_to" placeholder="To Date" value="<?php echo $date_to; ?>">
                        
                        <input type="text" name="search" placeholder="Search certificates..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-outline">Search</button>
                        <a href="certificates.php" class="btn btn-outline">Reset</a>
                    </form>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Certificate ID</th>
                        <th>Resident</th>
                        <th>Type</th>
                        <th>Purpose</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th>Approved By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($certificates) > 0): ?>
                        <?php foreach ($certificates as $cert): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cert['certificate_id']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($cert['purok']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($cert['certificate_type']); ?></td>
                            <td><?php echo htmlspecialchars($cert['purpose']); ?></td>
                            <td><?php echo formatDate($cert['created_at']); ?></td>
                            <td>
                                <span class="status status-<?php echo strtolower($cert['status']); ?>">
                                    <?php echo $cert['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $cert['approved_by_name'] ? htmlspecialchars($cert['approved_by_name']) : '-'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($cert['certificate_type'] == 'Clearance'): ?>
                                        <a href="certificate_clearance.php?id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <?php elseif ($cert['certificate_type'] == 'Indigency'): ?>
                                        <a href="certificate_indigency.php?id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <?php elseif ($cert['certificate_type'] == 'Residency'): ?>
                                        <a href="certificate_residency.php?id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($cert['status'] == 'Pending'): ?>
                                        <a href="certificates.php?approve=<?php echo $cert['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this certificate?')">Approve</a>
                                        <a href="certificates.php?reject=<?php echo $cert['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject this certificate?')">Reject</a>
                                    <?php elseif ($cert['status'] == 'Approved'): ?>
                                        <a href="certificates.php?release=<?php echo $cert['id']; ?>" class="btn btn-sm btn-info" onclick="return confirm('Mark as Released?')">Mark as Released</a>
                                    <?php elseif ($cert['status'] == 'Released'): ?>
                                        <a href="certificates.php?print=<?php echo $cert['id']; ?>" class="btn btn-sm btn-primary" target="_blank" onclick="openPrintWindow(this.href); return false;">Print</a>
                                        <a href="certificates.php?reprint=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline" target="_blank" onclick="openPrintWindow(this.href); return false;">Re-print</a>
                                    <?php elseif ($cert['status'] == 'Rejected'): ?>
                                        <a href="certificates.php?review=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline">Review</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b;">
                                No certificate requests found. <a href="certificate_clearance.php">Issue the first certificate</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php
// Handle reject
if (isset($_GET['reject'])) {
    $cert_id = $_GET['reject'];
    
    // Get certificate info for logging
    $cert_query = "SELECT c.certificate_id, r.first_name, r.last_name 
                   FROM certificates c 
                   JOIN residents r ON c.resident_id = r.id 
                   WHERE c.id = ?";
    $cert_stmt = $db->prepare($cert_query);
    $cert_stmt->execute([$cert_id]);
    $certificate_info = $cert_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($certificate_info) {
        $reject_query = "UPDATE certificates SET status = 'Rejected', approved_by = ?, approved_at = NOW() WHERE id = ?";
        $reject_stmt = $db->prepare($reject_query);
        
        if ($reject_stmt->execute([$_SESSION['user_id'], $cert_id])) {
            // Log activity
            Auth::logActivity($_SESSION['user_id'], 'Reject Certificate', 
                "Rejected certificate: {$certificate_info['certificate_id']} for {$certificate_info['first_name']} {$certificate_info['last_name']}");
            
            $_SESSION['success'] = "Certificate rejected successfully!";
            header("Location: certificates.php");
            exit();
        }
    }
}

// Handle release
if (isset($_GET['release'])) {
    $cert_id = $_GET['release'];
    
    // Get certificate info for logging
    $cert_query = "SELECT c.certificate_id, r.first_name, r.last_name 
                   FROM certificates c 
                   JOIN residents r ON c.resident_id = r.id 
                   WHERE c.id = ?";
    $cert_stmt = $db->prepare($cert_query);
    $cert_stmt->execute([$cert_id]);
    $certificate_info = $cert_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($certificate_info) {
        // Update without released_by column
        $release_query = "UPDATE certificates SET status = 'Released' WHERE id = ?";
        $release_stmt = $db->prepare($release_query);
        
        if ($release_stmt->execute([$cert_id])) {
            // Log activity
            Auth::logActivity($_SESSION['user_id'], 'Release Certificate', 
                "Released certificate: {$certificate_info['certificate_id']} for {$certificate_info['first_name']} {$certificate_info['last_name']}");
            
            $_SESSION['success'] = "Certificate marked as released!";
            header("Location: certificates.php");
            exit();
        }
    }
}
?>

<script>
// Function to open print window with clean settings
function openPrintWindow(url) {
    // Add autoprint parameter
    const printUrl = url + '&autoprint=1';
    
    // Open in new window without browser UI
    const printWindow = window.open(printUrl, '_blank', 
        'width=1024,height=768,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes');
    
    if (printWindow) {
        printWindow.focus();
    }
}
</script>

<?php include '../includes/footer.php'; ?>