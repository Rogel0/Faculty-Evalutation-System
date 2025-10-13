<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

// Only teachers can download their own reports
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'teacher') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$teacherId = (int)$_SESSION['userID'];

// Optional: scope to active school year
$requestedSY = isset($_GET['school_year_id']) && $_GET['school_year_id'] !== '' ? (int)$_GET['school_year_id'] : null;
$currentSchoolYear = null;
$schoolYearLabel = 'All Time';
if ($requestedSY) {
    $sStmt = $conn->prepare("SELECT id, year, semester FROM school_years WHERE id = ? LIMIT 1");
    $sStmt->bind_param('i', $requestedSY);
    $sStmt->execute();
    $sRes = $sStmt->get_result();
    if ($sRes && $row = $sRes->fetch_assoc()) {
        $currentSchoolYear = (int)$row['id'];
        $schoolYearLabel = $row['year'] . ' ' . ($row['semester'] ?? '');
    }
} else {
    $syRes = $conn->query("SELECT id, year, semester FROM school_years WHERE is_active = 1 LIMIT 1");
    if ($syRes && $row = $syRes->fetch_assoc()) {
        $currentSchoolYear = (int)$row['id'];
        $schoolYearLabel = $row['year'] . ' ' . ($row['semester'] ?? '');
    }
}

// Fetch per-question aggregates
$q = "SELECT q.id AS qid, q.question_text, ROUND(AVG(e.answer),2) AS avg_rating, COUNT(DISTINCT e.evaluator_id) AS respondents, COUNT(e.id) AS total_answers
    FROM evaluations e
    JOIN questionnaires q ON e.questionnaire_id = q.id
      WHERE e.teacher_id = ? AND e.evaluator_type = 'student'";
if ($currentSchoolYear) $q .= " AND e.school_year_id = ?";
$q .= " GROUP BY q.id ORDER BY q.id";

$stmt = $conn->prepare($q);
if ($currentSchoolYear) {
    $stmt->bind_param('ii', $teacherId, $currentSchoolYear);
} else {
    $stmt->bind_param('i', $teacherId);
}
$stmt->execute();
$res = $stmt->get_result();

// Header for CSV download
$filename = 'teacher_report_' . $teacherId . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
// Title rows
fputcsv($output, ["Teacher Report"]);
fputcsv($output, ["Teacher ID", $teacherId]);
fputcsv($output, ["School Year", $schoolYearLabel]);
fputcsv($output, []);

// Column headers (show question text only, not numeric id)
fputcsv($output, ['Question', 'Average Rating', 'Distinct Respondents', 'Total Answers']);

while ($row = $res->fetch_assoc()) {
    fputcsv($output, [$row['question_text'], $row['avg_rating'], $row['respondents'], $row['total_answers']]);
}

fclose($output);
exit;
