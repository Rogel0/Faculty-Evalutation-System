<?php
header('Content-Type: application/json');
include_once('../config/database.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid student id']);
    exit();
}

$stmt = $conn->prepare("SELECT id, student_id, firstname, lastname, middlename, email, birthdate, strand, grade_level FROM users WHERE id = ? AND user_type = 'student' LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['error' => 'Student not found']);
    exit();
}
$student = $res->fetch_assoc();
$stmt->close();

$enrolls = [];
$es = $conn->prepare("SELECT subject_id, teacher_id FROM student_enrollments WHERE student_id = ?");
$es->bind_param('i', $id);
$es->execute();
$er = $es->get_result();
if ($er) {
    while ($row = $er->fetch_assoc()) {
        $enrolls[] = $row;
    }
}
$es->close();

$student['enrollments'] = $enrolls;
echo json_encode($student);
