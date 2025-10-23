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
    $department = $_POST['department'] ?? '';
    $subject_ids = isset($_POST['subject_ids']) ? (array)$_POST['subject_ids'] : [];
    $teacher_code = $_POST['teacher_code'] ?? null;
    $position = 'Teacher'; // enforce
    $created_at = date('Y-m-d H:i:s');

    // teacher_code is required
    if (empty($teacher_code)) {
        $_SESSION['error'] = 'Teacher ID is required.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Try reuse existing teacher by teacher_code or username
    $found_teacher_id = null;
    if (!empty($teacher_code)) {
        $chk = $conn->prepare("SELECT id FROM users WHERE user_type = 'teacher' AND teacher_code = ? LIMIT 1");
        $chk->bind_param('s', $teacher_code);
        $chk->execute();
        $chk->bind_result($found_id);
        if ($chk->fetch()) $found_teacher_id = $found_id;
        $chk->close();
    }

    if ($found_teacher_id === null && !empty($username)) {
        $chk2 = $conn->prepare("SELECT id FROM users WHERE user_type = 'teacher' AND username = ? LIMIT 1");
        $chk2->bind_param('s', $username);
        $chk2->execute();
        $chk2->bind_result($found_id2);
        if ($chk2->fetch()) $found_teacher_id = $found_id2;
        $chk2->close();
    }

    // validate uniqueness of teacher_code (if creating) or ensure it's not used by another teacher when updating
    if ($found_teacher_id === null) {
        $codeChk = $conn->prepare("SELECT id FROM users WHERE teacher_code = ? LIMIT 1");
        $codeChk->bind_param('s', $teacher_code);
        $codeChk->execute();
        $codeChk->store_result();
        if ($codeChk->num_rows > 0) {
            $_SESSION['error'] = 'Teacher ID already in use by another account.';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
        $codeChk->close();
    } else {
        // if updating, ensure teacher_code doesn't belong to someone else
        $codeChk = $conn->prepare("SELECT id FROM users WHERE teacher_code = ? AND id != ? LIMIT 1");
        $codeChk->bind_param('si', $teacher_code, $found_teacher_id);
        $codeChk->execute();
        $codeChk->store_result();
        if ($codeChk->num_rows > 0) {
            $_SESSION['error'] = 'Teacher ID already in use by another account.';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
        $codeChk->close();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    if ($found_teacher_id !== null) {
        // update existing teacher
        $upd = $conn->prepare("UPDATE users SET username = ?, password = ?, firstname = ?, lastname = ?, middlename = ?, email = ?, position = ?, department = ?, teacher_code = ? WHERE id = ? AND user_type = 'teacher'");
        $upd->bind_param('sssssssssi', $username, $hashed, $firstname, $lastname, $middlename, $email, $position, $department, $teacher_code, $found_teacher_id);
        if (!$upd->execute()) {
            $_SESSION['error'] = 'Failed to update teacher: ' . $conn->error;
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
        $teacher_id = $found_teacher_id;
        $upd->close();
    } else {
        // create new teacher
        $insertUser = $conn->prepare("INSERT INTO users (username, password, firstname, lastname, middlename, email, position, department, user_type, teacher_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'teacher', ?, ?)");
        $insertUser->bind_param('ssssssssss', $username, $hashed, $firstname, $lastname, $middlename, $email, $position, $department, $teacher_code, $created_at);
        if (!$insertUser->execute()) {
            $_SESSION['error'] = 'Failed to create teacher: ' . $conn->error;
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
        $teacher_id = $conn->insert_id;
        $insertUser->close();
    }

    // assign subjects if provided and active school year exists
    $school_year_id = null;
    $syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
    if ($syRes && $row = $syRes->fetch_assoc()) $school_year_id = (int)$row['id'];

    if ($school_year_id !== null && !empty($subject_ids)) {
        $ins = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, school_year_id) VALUES (?, ?, ?)");
        foreach ($subject_ids as $sid) {
            $sid = intval($sid);
            if ($sid > 0) {
                $ins->bind_param('iii', $teacher_id, $sid, $school_year_id);
                $ins->execute();
            }
        }
        $ins->close();
    }

    $_SESSION['success'] = 'Teacher added successfully.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
