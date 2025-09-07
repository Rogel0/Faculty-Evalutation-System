<?php
// Payment protection check
require_once(__DIR__ . '/../config/payment_protection.php');
if (PaymentProtection::isLocked()) {
    PaymentProtection::showPaymentMessage();
}

// include('../auth/sessionCheck.php');

// Get the requested module from the URL, default to 'home'
$module = $_GET['module'] ?? 'dashboard';
$content = "../views/admin/{$module}.php";

// Check if the requested module file exists
if (!file_exists($content)) {
    $content = '../views/404.php';
}


$title = ucfirst($module);


include('../admin_layout.php');
