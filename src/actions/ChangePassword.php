<?php
session_start();
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

$userId = $_SESSION['userID'] ?? null;
if (!$userId) {
    $_SESSION['error'] = 'Not logged in.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) {
    $_SESSION['error'] = 'New password and confirmation do not match.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

if (strlen($new) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

// Fetch current password (plain-text in this app)
$stmt = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['error'] = 'User not found.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();

if ($row['password'] !== $current) {
    $_SESSION['error'] = 'Current password is incorrect.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

// Update password (plain text to match current app behavior)
$up = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
$up->bind_param('si', $new, $userId);
if ($up->execute()) {
    $_SESSION['success'] = 'Password changed successfully.';
} else {
    $_SESSION['error'] = 'Failed to change password.';
}
$up->close();

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
exit;
