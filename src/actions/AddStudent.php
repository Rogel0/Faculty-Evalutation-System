<?php
session_start();
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // gather form values
    $student_id_input = $_POST['student_id'] ?? ''; // optional student identifier
    // Server-side: allow digits and hyphens (e.g., 2021-001)
    if ($student_id_input !== '' && !preg_match('/^[0-9-]+$/', $student_id_input)) {
        $_SESSION['error'] = 'Student ID may contain only digits and hyphens (e.g., 2021-001).';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    // form field is named 'course' (Strand). Map it to $strand for DB column 'strand'.
    $course = $_POST['course'] ?? '';
    $strand = $course;
    $grade_level = $_POST['grade_level'] ?? '';
    $email = $_POST['email'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $subjects = $_POST['subject_id'] ?? [];
    $teachers = $_POST['teacher_id'] ?? [];
    $created_at = date('Y-m-d H:i:s');

    // If a student_id is provided and already exists, update that student instead of inserting a duplicate.
    $student_user_id = null;
    if ($student_id_input !== '') {
        $existStmt = $conn->prepare("SELECT id, username FROM users WHERE student_id = ? AND user_type = 'student' LIMIT 1");
        $existStmt->bind_param('s', $student_id_input);
        $existStmt->execute();
        $existRes = $existStmt->get_result();
        if ($existRes && $existRes->num_rows > 0) {
            $existRow = $existRes->fetch_assoc();
            $student_user_id = (int)$existRow['id'];
            // Update basic profile fields for existing student (do not overwrite username/password here)
            $up = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, middlename = ?, email = ?, birthdate = ?, strand = ?, grade_level = ? WHERE id = ? AND user_type = 'student'");
            $up->bind_param('sssssssi', $firstname, $lastname, $middlename, $email, $birthdate, $strand, $grade_level, $student_user_id);
            $up->execute();
            $up->close();
        }
        $existStmt->close();
    }

    // If we did not find an existing student by student_id, validate username uniqueness and create a new user.
    if ($student_user_id === null) {
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
        // Note: database column is `strand` (not `course`). Also include `student_id` so the form value is saved.
        $insertUser = $conn->prepare("INSERT INTO users (student_id, username, password, firstname, lastname, middlename, email, birthdate, strand, grade_level, user_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', ?)");
        $insertUser->bind_param('sssssssssss', $student_id_input, $username, $password, $firstname, $lastname, $middlename, $email, $birthdate, $strand, $grade_level, $created_at);

        if (!$insertUser->execute()) {
            $_SESSION['error'] = 'Failed to create student account: ' . $conn->error;
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $student_user_id = $conn->insert_id;
        $insertUser->close();
    }

    // If your `users` table doesn't have a `grade_level` column, run this SQL once:
    // ALTER TABLE users ADD COLUMN grade_level VARCHAR(10) NULL AFTER strand;

    // Determine active school year id
    $school_year_id = null;
    $syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
    if ($syRes && $row = $syRes->fetch_assoc()) {
        $school_year_id = (int)$row['id'];
    } else {
        $syRes2 = $conn->query("SELECT id FROM school_years ORDER BY id DESC LIMIT 1");
        if ($syRes2 && $row2 = $syRes2->fetch_assoc()) $school_year_id = (int)$row2['id'];
    }

    // Insert enrollments if we have pairs and a school year. For existing students, only add missing enrollments.
    if (!empty($subjects) && !empty($teachers) && $school_year_id !== null) {
        // prepared stmt to check existing enrollment
        $checkEnroll = $conn->prepare("SELECT COUNT(*) as cnt FROM student_enrollments WHERE student_id = ? AND subject_id = ? AND school_year_id = ?");
        $insEnroll = $conn->prepare("INSERT INTO student_enrollments (student_id, subject_id, teacher_id, school_year_id) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($subjects); $i++) {
            $sub = intval($subjects[$i]);
            $teach = intval($teachers[$i] ?? 0);
            if ($sub > 0 && $teach > 0) {
                // check duplicate for this school year and subject
                $checkEnroll->bind_param('iii', $student_user_id, $sub, $school_year_id);
                $checkEnroll->execute();
                $cres = $checkEnroll->get_result();
                $count = 0;
                if ($cres && $crow = $cres->fetch_assoc()) $count = (int)$crow['cnt'];
                if ($count === 0) {
                    $insEnroll->bind_param('iiii', $student_user_id, $sub, $teach, $school_year_id);
                    $insEnroll->execute();
                }
            }
        }
        $checkEnroll->close();
        $insEnroll->close();
    } else if ($school_year_id === null) {
        $_SESSION['warning'] = 'Student created/updated but no active school year found; enrollments were not created.';
    }

    $_SESSION['success'] = 'Student added successfully!';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
