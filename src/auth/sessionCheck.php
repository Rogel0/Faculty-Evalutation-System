<?php
// Include payment protection
require_once(__DIR__ . '/../config/payment_protection.php');

// Check payment protection first (applies to all pages)
if (PaymentProtection::isLocked()) {
    PaymentProtection::showPaymentMessage();
}

// if (!isset($_SESSION['userID'])) {
//     $_SESSION['error'] = 'You must log in to access this page!';
//     header('Location: ../index.php');
//     exit();
// }
if (isset($_SESSION['userID'])) {
    $_SESSION['errorLogin'] = 'You are already logged in!';
    if ($_SESSION['position'] === 'admin') {
        header('Location: router/admin.php?module=dashboard');
    } elseif ($_SESSION['position'] === 'student') {
        header('Location: router/student.php?module=dashboard');
    } else {
        header('Location: router/main.php?module=home');
    }
    exit();
}
