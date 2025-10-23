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
$position = $_POST['position'] ?? '';

$chk = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'supervisor' LIMIT 1");
$chk->bind_param('i', $id);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    echo json_encode(['error' => 'Supervisor not found']);
    exit();
}
$chk->close();

$upd = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, middlename = ?, email = ?, department = ?, position = ? WHERE id = ? AND user_type = 'supervisor'");
$upd->bind_param('ssssssi', $firstname, $lastname, $middlename, $email, $department, $position, $id);
if (!$upd->execute()) {
    echo json_encode(['error' => 'Failed to update supervisor: ' . $conn->error]);
    exit();
}
$upd->close();

echo json_encode(['success' => true]);
