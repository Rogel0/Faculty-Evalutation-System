<?php
session_start();
include('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = 'Missing id';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM school_years WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success'] = 'Academic year deleted.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting: ' . $e->getMessage();
}

 header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
