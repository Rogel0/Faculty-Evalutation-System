<?php
session_start();
include('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin/academic_year.php');
    exit;
}

$id = $_POST['id'] ?? null;
$year = $_POST['year'] ?? '';
$semester = $_POST['semester'] ?? '';
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
// Default new academic years to inactive (0) - can be toggled later
$is_active = 0;

if (empty($year) || empty($semester) || empty($start_date) || empty($end_date)) {
    $_SESSION['error'] = 'All fields are required.';
     header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

try {
    if (!empty($id)) {
        // When editing, don't change the is_active status - use toggle button for that
        $stmt = $conn->prepare("UPDATE school_years SET year = ?, semester = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $year, $semester, $start_date, $end_date, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success'] = 'Academic year updated.';
    } else {
        $stmt = $conn->prepare("INSERT INTO school_years (year, semester, start_date, end_date, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssssi', $year, $semester, $start_date, $end_date, $is_active);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success'] = 'Academic year added.';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

 header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
