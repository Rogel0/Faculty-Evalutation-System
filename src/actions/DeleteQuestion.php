<?php
session_start();
include_once('../config/database.php');

/*
If your `questionnaires` table doesn't have a `deleted` column, run this SQL once
in your database (phpMyAdmin / CLI):

ALTER TABLE questionnaires ADD COLUMN deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER created_at;

This enables soft-delete behavior implemented below.
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../router/admin.php'));
    exit;
}

$question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
if ($question_id <= 0) {
    $_SESSION['error'] = 'Invalid question id.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../router/admin.php'));
    exit;
}

// Fetch questionnaire and evaluator type via criteria
$stmt = $conn->prepare("SELECT q.id, q.criteria_id, c.evaluator_type FROM questionnaires q LEFT JOIN criteria c ON q.criteria_id = c.id WHERE q.id = ? LIMIT 1");
$stmt->bind_param('i', $question_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['error'] = 'Question not found.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../router/admin.php'));
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();

$evaluator_type = $row['evaluator_type'] ?? 'student';

// Map evaluator_type to faculty_evaluation_periods.evaluation_type
$period_type = ($evaluator_type === 'teacher') ? 'peer' : 'student';

// Prevent deletion when an evaluation period is currently active for this evaluator type
$periodCheck = $conn->prepare('SELECT id FROM faculty_evaluation_periods WHERE active = 1 AND evaluation_type = ? LIMIT 1');
$periodCheck->bind_param('s', $period_type);
$periodCheck->execute();
$periodRes = $periodCheck->get_result();
if ($periodRes && $periodRes->num_rows > 0) {
    $_SESSION['error'] = 'Cannot delete question while an evaluation period is active. Please stop the period first.';
    $periodCheck->close();
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../router/admin.php?module=' . ($period_type === 'peer' ? 'teacher_questionnaire' : 'student_questionnaire')));
    exit;
}
$periodCheck->close();

// Instead of hard-deleting, perform a soft-delete by setting `deleted = 1`.
// This preserves historical evaluations while hiding the question from new forms.
$upd = $conn->prepare('UPDATE questionnaires SET deleted = 1 WHERE id = ?');
$upd->bind_param('i', $question_id);
if ($upd->execute()) {
    // Provide a user-facing toast but avoid mentioning 'soft-delete' in the message
    $_SESSION['success'] = 'Question removed successfully.';
} else {
    $_SESSION['error'] = 'Failed to remove question: ' . $conn->error;
}
$upd->close();

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../router/admin.php?module=' . ($period_type === 'peer' ? 'teacher_questionnaire' : 'student_questionnaire')));
exit;
