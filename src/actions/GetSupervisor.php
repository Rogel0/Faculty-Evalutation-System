<?php
header('Content-Type: application/json');
include_once('../config/database.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid supervisor id']);
    exit();
}

$stmt = $conn->prepare("SELECT id, username, firstname, lastname, middlename, email, department, position FROM users WHERE id = ? AND user_type = 'supervisor' LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['error' => 'Supervisor not found']);
    exit();
}
$sup = $res->fetch_assoc();
$stmt->close();

echo json_encode($sup);
