<?php
session_start();
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $position = $_POST['position'] ?? '';
    $department = $_POST['department'] ?? '';
    $created_at = date('Y-m-d H:i:s');

    // username uniqueness
    $ucheck = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $ucheck->bind_param('s', $username);
    $ucheck->execute();
    $ucheck->store_result();
    if ($ucheck->num_rows > 0) {
        $_SESSION['error'] = 'Username already exists.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    $ucheck->close();

    $insertUser = $conn->prepare("INSERT INTO users (username, password, firstname, lastname, middlename, email, position, department, user_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'supervisor', ?)");
    $insertUser->bind_param('sssssssss', $username, $password, $firstname, $lastname, $middlename, $email, $position, $department, $created_at);
    if (!$insertUser->execute()) {
        $_SESSION['error'] = 'Failed to create supervisor: ' . $conn->error;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $_SESSION['success'] = 'Supervisor added successfully.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
