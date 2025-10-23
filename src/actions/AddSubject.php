<?php
session_start();
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_subject.php'));
    exit();
}

$code = trim($_POST['subject_code'] ?? '');
$name = trim($_POST['subject_name'] ?? '');
$desc = trim($_POST['description'] ?? '');

if ($code === '' || $name === '') {
    $_SESSION['error'] = 'Subject code and name are required.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_subject.php'));
    exit();
}

// Basic validation: ensure unique subject_code
$chk = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ? LIMIT 1");
$chk->bind_param('s', $code);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    $_SESSION['error'] = 'Subject code already exists.';
    $chk->close();
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_subject.php'));
    exit();
}
$chk->close();

$ins = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, description, created_at) VALUES (?, ?, ?, NOW())");
$ins->bind_param('sss', $code, $name, $desc);
if (!$ins->execute()) {
    $_SESSION['error'] = 'Failed to add subject: ' . $conn->error;
    $ins->close();
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_subject.php'));
    exit();
}
$ins->close();

$_SESSION['success'] = 'Subject added successfully.';
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_subject.php'));
exit();
