<?php
session_start();
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_teacher.php'));
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid teacher id.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_teacher.php'));
    exit();
}

$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$middlename = $_POST['middlename'] ?? '';
$email = $_POST['email'] ?? '';
$department = $_POST['department'] ?? '';
$position = 'Teacher'; // enforce
$subject_ids = isset($_POST['subject_ids']) ? (array)$_POST['subject_ids'] : [];

// ensure the user exists and is a teacher
$chk = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'teacher' LIMIT 1");
$chk->bind_param('i', $id);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $_SESSION['error'] = 'Teacher not found.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_teacher.php'));
    exit();
}
$chk->close();

// Update users table
$upd = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, middlename = ?, email = ?, department = ?, position = ? WHERE id = ? AND user_type = 'teacher'");
$upd->bind_param('ssssssi', $firstname, $lastname, $middlename, $email, $department, $position, $id);
if (!$upd->execute()) {
    $_SESSION['error'] = 'Failed to update teacher: ' . $conn->error;
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_teacher.php'));
    exit();
}
$upd->close();

// Replace teacher_subjects for active school year
$school_year_id = null;
$syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
if ($syRes && $r = $syRes->fetch_assoc()) $school_year_id = (int)$r['id'];

if ($school_year_id !== null) {
    // delete existing assignments for this teacher and school year
    $del = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ? AND school_year_id = ?");
    $del->bind_param('ii', $id, $school_year_id);
    $del->execute();
    $del->close();

    if (!empty($subject_ids)) {
        $ins = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, school_year_id) VALUES (?, ?, ?)");
        foreach ($subject_ids as $sid) {
            $sid = intval($sid);
            if ($sid > 0) {
                $ins->bind_param('iii', $id, $sid, $school_year_id);
                $ins->execute();
            }
        }
        $ins->close();
    }

    // propagate teacher assignment to student_enrollments for affected subjects
    if (!empty($subject_ids)) {
        // check if student_enrollments has teacher_id column
        $colRes = $conn->query("SHOW COLUMNS FROM student_enrollments LIKE 'teacher_id'");
        if ($colRes && $colRes->num_rows > 0) {
            $updateStmt = $conn->prepare("UPDATE student_enrollments SET teacher_id = ? WHERE subject_id = ? AND school_year_id = ?");
            foreach ($subject_ids as $sid) {
                $sid = intval($sid);
                if ($sid > 0) {
                    $updateStmt->bind_param('iii', $id, $sid, $school_year_id);
                    $updateStmt->execute();
                }
            }
            $updateStmt->close();
        } else {
            // column missing — skip propagation but notify admin
            $_SESSION['warning'] = 'Note: student_enrollments.teacher_id column not found; student assignments were not updated.';
        }
    }
}

$_SESSION['success'] = 'Teacher updated successfully.';
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/admin/add_teacher.php'));
exit();
