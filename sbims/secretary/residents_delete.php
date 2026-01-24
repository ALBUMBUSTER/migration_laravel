<?php
require_once '../config/auth.php';
require_once '../config/connection.php';

Auth::checkAuth();
Auth::checkRole(['secretary']);

$id = $_GET['id'] ?? 0;

if ($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    // First, get resident info for logging
    $stmt = $db->prepare("SELECT first_name, last_name FROM residents WHERE id = ?");
    $stmt->execute([$id]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete resident
    $delete_stmt = $db->prepare("DELETE FROM residents WHERE id = ?");
    
    if ($delete_stmt->execute([$id])) {
        // Log activity
        if ($resident) {
            Auth::logActivity($_SESSION['user_id'], 'Delete Resident', 
                "Deleted resident: {$resident['first_name']} {$resident['last_name']} (ID: {$id})");
        }
        $_SESSION['success'] = "Resident deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete resident.";
    }
}

header("Location: residents.php");
exit();
?>