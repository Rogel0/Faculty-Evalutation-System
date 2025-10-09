<?php
session_start();
if (isset($_SESSION['upload_warnings'])) unset($_SESSION['upload_warnings']);
// also clear the short warning message
if (isset($_SESSION['warning'])) unset($_SESSION['warning']);
// set a small success message
$_SESSION['success'] = 'Upload warnings cleared.';
// redirect back to referrer or admin dashboard
$redirect = isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/faculty_evaluation/src/';
header('Location: ' . $redirect);
exit;
