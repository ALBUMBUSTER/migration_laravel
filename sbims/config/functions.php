<?php
function generateCaseID($db) {
    $year = date('Y');
    $query = "SELECT COUNT(*) as count FROM blotters WHERE YEAR(created_at) = :year";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':year', $year);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $sequence = $result['count'] + 1;
    return "BL-$year-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
}

function generateCertificateID($db, $type) {
    $year = date('Y');
    $prefix = '';
    switch ($type) {
        case 'Clearance': $prefix = 'CLC'; break;
        case 'Indigency': $prefix = 'IND'; break;
        case 'Residency': $prefix = 'RES'; break;
    }
    
    $query = "SELECT COUNT(*) as count FROM certificates WHERE certificate_type = :type AND YEAR(created_at) = :year";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':year', $year);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $sequence = $result['count'] + 1;
    return "$prefix-$year-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}
function calculateAge($birthdate) {
    $birthDate = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birthDate);
    return $age->y;
}
function createNotification($user_id, $title, $message, $type = 'info', $link = null) {
    try {
        require_once 'connection.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO notifications (user_id, title, message, type, link) 
                 VALUES (:user_id, :title, :message, :type, :link)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':link', $link);
        
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}

function getUnreadNotifications($user_id) {
    try {
        require_once 'connection.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM notifications 
                 WHERE user_id = :user_id AND is_read = 0 
                 ORDER BY created_at DESC 
                 LIMIT 10";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get notifications error: " . $e->getMessage());
        return [];
    }
}

function getAllNotifications($user_id, $limit = 20) {
    try {
        require_once 'connection.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM notifications 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC 
                 LIMIT :limit";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function markAsRead($notification_id, $user_id) {
    try {
        require_once 'connection.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE notifications SET is_read = 1 
                 WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $notification_id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function markAllAsRead($user_id) {
    try {
        require_once 'connection.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE notifications SET is_read = 1 
                 WHERE user_id = :user_id AND is_read = 0";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function getNotificationCount($user_id) {
    try {
        require_once 'connection.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM notifications 
                 WHERE user_id = :user_id AND is_read = 0";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (Exception $e) {
        return 0;
    }
}
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>