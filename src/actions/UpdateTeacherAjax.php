<?php
session_start();
header('Content-Type: application/json');
include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid id']);
    exit();
}

$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$middlename = $_POST['middlename'] ?? '';
$email = $_POST['email'] ?? '';
$department = $_POST['department'] ?? '';
$subject_ids = isset($_POST['subject_ids']) ? (array)$_POST['subject_ids'] : [];

// basic user check
$chk = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'teacher' LIMIT 1");
$chk->bind_param('i', $id);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    echo json_encode(['error' => 'Teacher not found']);
    exit();
}
$chk->close();

$upd = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, middlename = ?, email = ?, department = ? WHERE id = ? AND user_type = 'teacher'");
$upd->bind_param('sssssi', $firstname, $lastname, $middlename, $email, $department, $id);
if (!$upd->execute()) {
    echo json_encode(['error' => 'Failed to update teacher: ' . $conn->error]);
    exit();
}
$upd->close();

// Update teacher_subjects same as UpdateTeacher.php
$school_year_id = null;
$syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
if ($syRes && $r = $syRes->fetch_assoc()) $school_year_id = (int)$r['id'];
if ($school_year_id !== null) {
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
}

echo json_encode(['success' => true]);
