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

$student_id_input = $_POST['student_id'] ?? '';
if ($student_id_input !== '' && !preg_match('/^[0-9-]+$/', $student_id_input)) {
    echo json_encode(['error' => 'Student ID may contain only digits and hyphens.']);
    exit();
}

$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$email = $_POST['email'] ?? '';
$grade_level = $_POST['grade_level'] ?? '';
$strand = $_POST['course'] ?? '';

// Update users table
$upd = $conn->prepare("UPDATE users SET student_id = ?, firstname = ?, lastname = ?, email = ?, grade_level = ?, strand = ? WHERE id = ? AND user_type = 'student'");
$upd->bind_param('ssssssi', $student_id_input, $firstname, $lastname, $email, $grade_level, $strand, $id);
if (!$upd->execute()) {
    echo json_encode(['error' => 'Failed to update student: ' . $conn->error]);
    exit();
}
$upd->close();

// Replace enrollments for current active school year
$school_year_id = null;
$syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
if ($syRes && $r = $syRes->fetch_assoc()) $school_year_id = (int)$r['id'];
if ($school_year_id !== null) {
    // Delete existing enrollments for this student and school year
    $del = $conn->prepare("DELETE FROM student_enrollments WHERE student_id = ? AND school_year_id = ?");
    $del->bind_param('ii', $id, $school_year_id);
    $del->execute();
    $del->close();

    // Insert provided pairs
    $subjects = $_POST['subject_id'] ?? [];
    $teachers = $_POST['teacher_id'] ?? [];
    if (!empty($subjects) && !empty($teachers)) {
        $ins = $conn->prepare("INSERT INTO student_enrollments (student_id, subject_id, teacher_id, school_year_id) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($subjects); $i++) {
            $sub = intval($subjects[$i]);
            $teach = intval($teachers[$i] ?? 0);
            if ($sub > 0 && $teach > 0) {
                $ins->bind_param('iiii', $id, $sub, $teach, $school_year_id);
                $ins->execute();
            }
        }
        $ins->close();
    }
}

echo json_encode(['success' => true]);
