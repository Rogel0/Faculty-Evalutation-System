<?php
include('../config/database.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id']);
    exit;
}

$stmt = $conn->prepare("SELECT id, year, semester, start_date, end_date, is_active FROM school_years WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

$row = $res->fetch_assoc();
header('Content-Type: application/json');
echo json_encode($row);
exit;
?>
