<?php

/**
 * Submit Peer Evaluation Action
 * Handles teacher-to-teacher evaluation submissions
 */

session_start();
header('Content-Type: application/json');

include_once('../config/database.php');



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in and is a teacher
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$evaluator_id = $_SESSION['userID'];
$teacher_id = $_POST['teacher_id'] ?? '';
$evaluator_type = $_POST['evaluator_type'] ?? '';

// Get current active school year
$current_school_year = 1; // Default value
$schoolYearQuery = "SELECT id FROM school_years WHERE is_active = 1 LIMIT 1";
$schoolYearResult = $conn->query($schoolYearQuery);
if ($schoolYearResult && $schoolYearResult->num_rows > 0) {
    $schoolYearRow = $schoolYearResult->fetch_assoc();
    $current_school_year = $schoolYearRow['id'];
}
$school_year_id = $_POST['school_year_id'] ?? $current_school_year;

// Get a dummy subject ID for peer evaluations (use the first available subject)
$dummy_subject_id = 1; // Default fallback
$subjectQuery = "SELECT id FROM subjects ORDER BY id ASC LIMIT 1";
$subjectResult = $conn->query($subjectQuery);
if ($subjectResult && $subjectResult->num_rows > 0) {
    $subjectRow = $subjectResult->fetch_assoc();
    $dummy_subject_id = $subjectRow['id'];
}

// Validate required fields
if (empty($teacher_id) || $evaluator_type !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Verify that both evaluator and evaluated teacher are in the same department
$department_check = "
    SELECT 
        e1.department as evaluator_dept,
        e2.department as teacher_dept
    FROM users e1, users e2 
    WHERE e1.id = ? AND e2.id = ? 
    AND e1.user_type = 'teacher' AND e2.user_type = 'teacher'
";

$stmt = $conn->prepare($department_check);
$stmt->bind_param('ii', $evaluator_id, $teacher_id);
$stmt->execute();
$dept_result = $stmt->get_result()->fetch_assoc();

if (!$dept_result) {
    echo json_encode(['success' => false, 'message' => 'Invalid teacher information']);
    exit;
}

if ($dept_result['evaluator_dept'] !== $dept_result['teacher_dept']) {
    echo json_encode(['success' => false, 'message' => 'You can only evaluate teachers in your department']);
    exit;
}

// Check if evaluation already exists (peer evaluation - check for any subject with teacher evaluator_type)
$existing_check = "
    SELECT id FROM evaluations 
    WHERE evaluator_id = ? AND teacher_id = ? 
    AND evaluator_type = 'teacher' AND school_year_id = ?
    AND subject_id = ?
";

$stmt = $conn->prepare($existing_check);
$stmt->bind_param('iiii', $evaluator_id, $teacher_id, $school_year_id, $dummy_subject_id);
$stmt->execute();
$existing = $stmt->get_result();

if ($existing->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already evaluated this colleague']);
    exit;
}

// Get all teacher questionnaire IDs
$questionnaire_query = "SELECT id FROM questionnaires WHERE evaluator_type = 'teacher' ORDER BY id";
$questionnaire_result = $conn->query($questionnaire_query);
$questionnaire_ids = [];
while ($row = $questionnaire_result->fetch_assoc()) {
    $questionnaire_ids[] = $row['id'];
}

if (empty($questionnaire_ids)) {
    echo json_encode(['success' => false, 'message' => 'No questionnaire found for teacher evaluation']);
    exit;
}



// Start transaction
$conn->begin_transaction();

try {
    $created_at = date('Y-m-d H:i:s');

    // Insert each question's answer as a separate evaluation record (peer evaluation uses dummy subject_id)
    $insert_stmt = $conn->prepare("
        INSERT INTO evaluations (evaluator_id, evaluator_type, teacher_id, subject_id, school_year_id, questionnaire_id, answer, created_at) 
        VALUES (?, 'teacher', ?, ?, ?, ?, ?, ?)
    ");

    foreach ($questionnaire_ids as $questionnaire_id) {
        $question_key = 'q_' . $questionnaire_id;

        if (!isset($_POST[$question_key])) {
            throw new Exception("Missing answer for question ID: $questionnaire_id");
        }

        $answer = intval($_POST[$question_key]);

        // Validate rating (1-5)
        if ($answer < 1 || $answer > 5) {
            throw new Exception("Invalid rating for question ID: $questionnaire_id");
        }

        $insert_stmt->bind_param(
            'iiiiiis',
            $evaluator_id,
            $teacher_id,
            $dummy_subject_id,
            $school_year_id,
            $questionnaire_id,
            $answer,
            $created_at
        );

        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to insert evaluation for question ID: $questionnaire_id");
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Peer evaluation submitted successfully',
        'evaluations_count' => count($questionnaire_ids)
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
