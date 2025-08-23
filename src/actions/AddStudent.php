<?php
session_start();
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $course = $_POST['course'] ?? '';
    $email = $_POST['email'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $created_at = date('Y-m-d H:i:s');

    // Check if student_id already exists
    $checkStmt = $conn->prepare("SELECT id FROM tbluser_students WHERE student_id = ? LIMIT 1");
    $checkStmt->bind_param("s", $student_id);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        // Student ID exists
        $_SESSION['error'] = 'Student added failed!';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    $checkStmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute insert
    $stmt = $conn->prepare("INSERT INTO tbluser_students (student_id, firstname, lastname, middlename, course, email, birthdate, username, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisssss", $student_id, $firstname, $lastname, $middlename, $course, $email, $birthdate, $username, $hashed_password, $created_at);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Student added successfully!';
        header("Location: " . $_SERVER['HTTP_REFERER']);;
        exit();
    } else {
        $_SESSION['error'] = 'Student added failed!';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
