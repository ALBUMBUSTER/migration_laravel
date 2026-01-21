<?php
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['admin']); // Only admin can export logs

$database = new Database();
$db = $database->getConnection();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=system_logs_' . date('Y-m-d_H-i-s') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['ID', 'User ID', 'User Name', 'Action', 'Description', 'IP Address', 'Timestamp']);

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$action_type = isset($_GET['action_type']) ? $_GET['action_type'] : null;

// Build query
$sql = "SELECT l.*, u.username, u.full_name 
        FROM activity_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        WHERE 1=1";
$params = [];

// Add filters
if ($start_date) {
    $sql .= " AND DATE(l.created_at) >= ?";
    $params[] = $start_date;
}

if ($end_date) {
    $sql .= " AND DATE(l.created_at) <= ?";
    $params[] = $end_date;
}

if ($user_id) {
    $sql .= " AND l.user_id = ?";
    $params[] = $user_id;
}

if ($action_type && $action_type != 'all') {
    $sql .= " AND l.action = ?";
    $params[] = $action_type;
}

$sql .= " ORDER BY l.created_at DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Write data rows
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['id'],
            $log['user_id'],
            $log['full_name'] . ' (' . $log['username'] . ')',
            $log['action'],
            $log['description'],
            $log['ip_address'],
            $log['created_at']
        ]);
    }
    
    // Log the export activity
    Auth::logActivity($_SESSION['user_id'], 'Export Logs', 'Exported system logs to CSV');
    
} catch (Exception $e) {
    // If error occurs, output error message
    fclose($output);
    header('Content-Type: text/html; charset=utf-8');
    echo "Error exporting logs: " . $e->getMessage();
    exit();
}

fclose($output);
exit();
?>