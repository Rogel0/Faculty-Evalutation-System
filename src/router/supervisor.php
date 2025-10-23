<?php
session_start();

// Check if user is logged in and has supervisor role
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'supervisor') {
    header('Location: ../auth/login.php');
    exit();
}

// Get the requested module, default to 'dashboard'
$module = $_GET['module'] ?? 'evaluation';
$content = "../views/supervisor/{$module}.php";

// If view file doesn't exist, show 404
if (!file_exists($content)) {
    $content = '../views/404.php';
}

$title = ucfirst($module);

include('../supervisor_layout.php');
