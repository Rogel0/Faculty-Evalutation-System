<?php
session_start();
include('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

$id = $_POST['id'] ?? null;
$current_status = $_POST['current_status'] ?? null;

if (!$id || $current_status === null) {
    $_SESSION['error'] = 'Missing required data';
       header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Toggle the status (if currently active (1), make inactive (0), and vice versa)
$new_status = $current_status == '1' ? 0 : 1;

try {
    $stmt = $conn->prepare("UPDATE school_years SET is_active = ? WHERE id = ?");
    $stmt->bind_param('ii', $new_status, $id);
    $stmt->execute();
    $stmt->close();
    
    $status_text = $new_status ? 'activated' : 'deactivated';
    $_SESSION['success'] = "Academic year has been {$status_text} successfully.";
} catch (Exception $e) {
    $_SESSION['error'] = 'Error updating status: ' . $e->getMessage();
}

    header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
