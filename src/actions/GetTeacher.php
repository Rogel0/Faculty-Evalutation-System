<?php
header('Content-Type: application/json');
include_once('../config/database.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid teacher id']);
    exit();
}

$stmt = $conn->prepare("SELECT id, teacher_code, firstname, lastname, middlename, email, department FROM users WHERE id = ? AND user_type = 'teacher' LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['error' => 'Teacher not found']);
    exit();
}
$teacher = $res->fetch_assoc();
$stmt->close();

$assignments = [];
$school_year_id = null;
$syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
if ($syRes && $r = $syRes->fetch_assoc()) $school_year_id = (int)$r['id'];
if ($school_year_id !== null) {
    $ts = $conn->prepare("SELECT subject_id FROM teacher_subjects WHERE teacher_id = ? AND school_year_id = ?");
    $ts->bind_param('ii', $id, $school_year_id);
    $ts->execute();
    $tr = $ts->get_result();
    if ($tr) while ($row = $tr->fetch_assoc()) $assignments[] = $row['subject_id'];
    $ts->close();
}

$teacher['subject_ids'] = $assignments;
echo json_encode($teacher);
