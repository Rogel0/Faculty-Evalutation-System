<?php
session_start();

// Check if user is logged in and has teacher role
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

// Get the requested module from the URL, default to 'peer_evaluation'
$module = $_GET['module'] ?? 'peer_evaluation';
$content = "../views/teacher/{$module}.php";

// Check if the requested module file exists
if (!file_exists($content)) {
    $content = '../views/404.php';
}

$title = ucfirst($module);

include('../teacher_layout.php');
