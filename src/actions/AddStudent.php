<?php
session_start();
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // gather form values
    $student_id_input = $_POST['student_id'] ?? ''; // optional student identifier
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $course = $_POST['course'] ?? '';
    $email = $_POST['email'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $subjects = $_POST['subject_id'] ?? [];
    $teachers = $_POST['teacher_id'] ?? [];
    $created_at = date('Y-m-d H:i:s');

    // Basic validation: username must be unique
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

    // Insert into users table as a student (using plain text password)
    $insertUser = $conn->prepare("INSERT INTO users (username, password, firstname, lastname, middlename, email, birthdate, course, user_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student', ?)");
    $insertUser->bind_param('sssssssss', $username, $password, $firstname, $lastname, $middlename, $email, $birthdate, $course, $created_at);

    if (!$insertUser->execute()) {
        $_SESSION['error'] = 'Failed to create student account: ' . $conn->error;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $student_user_id = $conn->insert_id;
    $insertUser->close();

    // Determine active school year id
    $school_year_id = null;
    $syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
    if ($syRes && $row = $syRes->fetch_assoc()) {
        $school_year_id = (int)$row['id'];
    } else {
        $syRes2 = $conn->query("SELECT id FROM school_years ORDER BY id DESC LIMIT 1");
        if ($syRes2 && $row2 = $syRes2->fetch_assoc()) $school_year_id = (int)$row2['id'];
    }

    // Insert enrollments if we have pairs and a school year
    if (!empty($subjects) && !empty($teachers) && $school_year_id !== null) {
        $enrollStmt = $conn->prepare("INSERT INTO student_enrollments (student_id, subject_id, teacher_id, school_year_id) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($subjects); $i++) {
            $sub = intval($subjects[$i]);
            $teach = intval($teachers[$i] ?? 0);
            if ($sub > 0 && $teach > 0) {
                $enrollStmt->bind_param('iiii', $student_user_id, $sub, $teach, $school_year_id);
                $enrollStmt->execute();
            }
        }
        $enrollStmt->close();
    } else if ($school_year_id === null) {
        $_SESSION['warning'] = 'Student created but no active school year found; enrollments were not created.';
    }

    $_SESSION['success'] = 'Student added successfully!';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
