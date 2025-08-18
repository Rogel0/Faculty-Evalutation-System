<?php
session_start();
include('../config/database.php');

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM tblusers WHERE username = ? AND password = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    // Populate session for display and role based on actual columns
    $_SESSION['userID'] = $user['id'];
    $_SESSION['username'] = $user['username'] ?? null;
    $_SESSION['position'] = $user['position'] ?? null;
    $_SESSION['firstname'] = $user['firstname'] ?? '';
    $_SESSION['lastname'] = $user['lastname'] ?? '';
    // Build a display name: firstname lastname -> username
    $firstLast = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    $_SESSION['displayName'] = $firstLast !== '' ? $firstLast : ($user['username'] ?? '');
    $_SESSION['success'] = 'You have successfully logged in!';

    if ($user['position'] === 'admin') {
        header('Location: ../router/admin.php?module=dashboard');
    } elseif ($user['position'] === 'student') {
        header('Location: ../router/student.php?module=dashboard');
    }
    exit();
} else {
    $_SESSION['error'] = 'Invalid username or password!';
    header('Location: ../index.php');
    exit();
}
