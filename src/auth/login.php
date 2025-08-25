<?php
session_start();
include('../config/database.php');

$username = $_POST['username'];
$password = $_POST['password'];


$sql = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    // For now, check plain text password. Replace with password_verify if using hashes.
    if ($user['password'] === $password) {
        $_SESSION['userID'] = $user['id'];
        $_SESSION['username'] = $user['username'] ?? null;
        $_SESSION['user_type'] = $user['user_type'] ?? null;
        $_SESSION['firstname'] = $user['firstname'] ?? '';
        $_SESSION['lastname'] = $user['lastname'] ?? '';
        $firstLast = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
        $_SESSION['displayName'] = $firstLast !== '' ? $firstLast : ($user['username'] ?? '');
        $_SESSION['success'] = 'You have successfully logged in!';

        // Redirect based on user_type
        switch ($user['user_type']) {
            case 'admin':
                header('Location: ../router/admin.php?module=dashboard');
                break;
            case 'student':
                header('Location: ../router/student.php?module=dashboard');
                break;
            case 'teacher':
                header('Location: ../router/teacher.php?module=dashboard');
                break;
            case 'supervisor':
                header('Location: ../router/supervisor.php?module=dashboard');
                break;
            default:
                $_SESSION['error'] = 'Unknown user type!';
                header('Location: ../index.php');
                break;
        }
        exit();
    } else {
        $_SESSION['error'] = 'Invalid username or password!';
        header('Location: ../index.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'Invalid username or password!';
    header('Location: ../index.php');
    exit();
}
