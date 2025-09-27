<?php
// if (!isset($_SESSION['userID'])) {
//     $_SESSION['error'] = 'You must log in to access this page!';
//     header('Location: ../index.php');
//     exit();
// }
if (isset($_SESSION['userID'])) {
    $_SESSION['errorLogin'] = 'You are already logged in!';
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: router/admin.php?module=dashboard');
    } elseif ($_SESSION['user_type'] === 'student') {
        header('Location: router/student.php?module=dashboard');
    } elseif ($_SESSION['user_type'] === 'teacher') {
        header('Location: router/teacher.php?module=peer_evaluation');
    } else {
        header('Location: router/main.php?module=home');
    }
    exit();
}
