<?php
session_start();
include('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// redirect target
$ref = $_SERVER['HTTP_REFERER'] ?? '../components/admin/student_questionnaire.php';

// convenience: ensure session container exists
if (!isset($_SESSION['SESSION']) || !is_array($_SESSION['SESSION'])) {
    $_SESSION['SESSION'] = [];
}

// collect and sanitize input
$name = trim(filter_input(INPUT_POST, 'criteria-name', FILTER_SANITIZE_STRING) ?? '');
$description = trim(filter_input(INPUT_POST, 'criteria-description', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
$evaluator_type = trim(filter_input(INPUT_POST, 'evaluator_type', FILTER_SANITIZE_STRING) ?? 'student');

if ($name === '') {
    $_SESSION['SESSION']['error'] = 'Criteria name is required.';
    header('Location: ' . $ref);
    exit();
}

if (!in_array($evaluator_type, ['student', 'teacher'], true)) {
    $evaluator_type = 'student';
}

// check duplicate
$sql = 'SELECT id FROM criteria WHERE name = ? AND evaluator_type = ? LIMIT 1';
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('ss', $name, $evaluator_type);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['SESSION']['error'] = 'A criteria with that name already exists for this evaluator type.';
        header('Location: ' . $ref);
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['SESSION']['error'] = 'Database error.';
    header('Location: ' . $ref);
    exit();
}

// insert
$created_at = date('Y-m-d H:i:s');
$sql = 'INSERT INTO criteria (name, description, evaluator_type, created_at) VALUES (?, ?, ?, ?)';
if ($ins = $conn->prepare($sql)) {
    $ins->bind_param('ssss', $name, $description, $evaluator_type, $created_at);
    if ($ins->execute()) {
        $ins->close();
        $_SESSION['SESSION']['success'] = 'Criteria added successfully.';
        header('Location: ' . $ref);
        exit();
    }
    $err = $ins->error ?: $conn->error;
    $ins->close();
    $_SESSION['SESSION']['error'] = 'Failed to add criteria: ' . $err;
    header('Location: ' . $ref);
    exit();
} else {
    $_SESSION['SESSION']['error'] = 'Database error preparing insert.';
    header('Location: ' . $ref);
    exit();
}
