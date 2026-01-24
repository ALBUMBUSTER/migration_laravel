<?php
require_once '../config/auth.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

Auth::checkAuth();

$user_id = $_SESSION['user_id'];

if (markAllAsRead($user_id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
}
?>