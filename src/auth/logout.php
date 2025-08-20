<?php
include('../config/database.php');
session_start();
session_destroy();
session_start();
// Clear any previous messages
unset($_SESSION['error']);
unset($_SESSION['success']);
$_SESSION['success'] = 'You have successfully logged out!';
header('Location: ../index.php');
exit;
