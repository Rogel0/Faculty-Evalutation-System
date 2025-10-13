<?php
session_start();
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use Dompdf\Dompdf;

// Only students can generate their own evaluation PDFs
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'student') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$studentId = (int)$_SESSION['userID'];

$teacherId = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
$subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$schoolYearId = isset($_GET['school_year_id']) && $_GET['school_year_id'] !== '' ? (int)$_GET['school_year_id'] : null;

if (!$teacherId || !$subjectId) {
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

// Validate that this student has evaluations for the given teacher/subject/(school year)
$checkSql = "SELECT COUNT(*) as cnt FROM evaluations WHERE evaluator_id = ? AND evaluator_type = 'student' AND teacher_id = ? AND subject_id = ?";
if ($schoolYearId) $checkSql .= " AND school_year_id = ?";

$checkStmt = $conn->prepare($checkSql);
if ($schoolYearId) {
    $checkStmt->bind_param('iiii', $studentId, $teacherId, $subjectId, $schoolYearId);
} else {
    $checkStmt->bind_param('iii', $studentId, $teacherId, $subjectId);
}
$checkStmt->execute();
$cres = $checkStmt->get_result();
$countRow = $cres->fetch_assoc();
if (empty($countRow) || (int)$countRow['cnt'] === 0) {
    http_response_code(403);
    echo 'No evaluation found for this student.';
    exit;
}

// Fetch details and answers
$q = "SELECT e.answer, q.question_text, c.name AS criteria_name, u.firstname AS teacher_firstname, u.lastname AS teacher_lastname, s.subject_name, s.subject_code, sy.year AS school_year, sy.semester, e.created_at
      FROM evaluations e
      LEFT JOIN questionnaires q ON e.questionnaire_id = q.id
      LEFT JOIN criteria c ON q.criteria_id = c.id
      LEFT JOIN users u ON e.teacher_id = u.id
      LEFT JOIN subjects s ON e.subject_id = s.id
      LEFT JOIN school_years sy ON e.school_year_id = sy.id
      WHERE e.evaluator_id = ? AND e.evaluator_type = 'student' AND e.teacher_id = ? AND e.subject_id = ?";
if ($schoolYearId) $q .= " AND e.school_year_id = ?";
$q .= " ORDER BY c.name, q.id";

$stmt = $conn->prepare($q);
if ($schoolYearId) {
    $stmt->bind_param('iiii', $studentId, $teacherId, $subjectId, $schoolYearId);
} else {
    $stmt->bind_param('iii', $studentId, $teacherId, $subjectId);
}
$stmt->execute();
$res = $stmt->get_result();

$teacherName = '';
$subjectName = '';
$subjectCode = '';
$schoolYearLabel = 'All Time';
$answersByCriteria = [];
$createdAt = null;
while ($row = $res->fetch_assoc()) {
    $teacherName = trim(($row['teacher_firstname'] ?? '') . ' ' . ($row['teacher_lastname'] ?? '')) ?: $teacherName;
    $subjectName = $row['subject_name'] ?? $subjectName;
    $subjectCode = $row['subject_code'] ?? $subjectCode;
    if ($row['school_year']) {
        $schoolYearLabel = $row['school_year'] . ' ' . ($row['semester'] ?? '');
    }
    $createdAt = $row['created_at'] ?? $createdAt;
    $criteria = $row['criteria_name'] ?? 'General';
    $answersByCriteria[$criteria][] = ['question' => $row['question_text'], 'answer' => $row['answer']];
}

// Compute overall average
$avgStmtSql = "SELECT ROUND(AVG(answer),2) AS overall_avg, COUNT(*) AS total_answers FROM evaluations WHERE evaluator_id = ? AND evaluator_type = 'student' AND teacher_id = ? AND subject_id = ?";
if ($schoolYearId) $avgStmtSql .= " AND school_year_id = ?";
$avgStmt = $conn->prepare($avgStmtSql);
if ($schoolYearId) {
    $avgStmt->bind_param('iiii', $studentId, $teacherId, $subjectId, $schoolYearId);
} else {
    $avgStmt->bind_param('iii', $studentId, $teacherId, $subjectId);
}
$avgStmt->execute();
$ars = $avgStmt->get_result();
$avgRow = $ars->fetch_assoc() ?: ['overall_avg' => 0, 'total_answers' => 0];

// Build HTML similar to reference but with student's answers
$html = '<!doctype html><html><head><meta charset="utf-8"><title>Evaluation Result</title>';
$html .= '<style>body{font-family: Arial, Helvetica, sans-serif; font-size:12px;color:#111} .header{display:flex;justify-content:space-between;align-items:center} .title{font-size:16px;font-weight:bold} table{width:100%;border-collapse:collapse;margin-top:10px} th,td{padding:6px;border:1px solid #ddd} .section{margin-top:12px} .right{text-align:right} .big{font-size:20px;font-weight:bold} .muted{font-size:11px;color:#666}</style></head><body>';

$html .= '<div class="header"><div><strong>LYCEUM OF ALABANG</strong></div><div class="title">FACULTY PERFORMANCE EVALUATION<br><span class="muted">Student Evaluation Result</span></div></div><hr>';
$html .= '<div style="margin-top:8px"><strong>Student:</strong> ' . htmlspecialchars($_SESSION['username'] ?? ($_SESSION['user'] ?? 'Student')) . '</div>';
$html .= '<div style="margin-top:4px"><strong>Teacher:</strong> ' . htmlspecialchars($teacherName) . '</div>';
$html .= '<div style="margin-top:4px"><strong>Subject:</strong> ' . htmlspecialchars($subjectName) . ' (' . htmlspecialchars($subjectCode) . ')</div>';
$html .= '<div style="margin-top:4px"><strong>School Year:</strong> ' . htmlspecialchars($schoolYearLabel) . '</div>';

foreach ($answersByCriteria as $crit => $questions) {
    $html .= '<div class="section"><strong>' . htmlspecialchars($crit) . '</strong><table><tbody>';
    $i = 1;
    foreach ($questions as $qrow) {
        $html .= '<tr><td style="width:8%;text-align:right">' . $i . '</td><td>' . htmlspecialchars($qrow['question']) . '</td><td style="width:12%;text-align:right">' . htmlspecialchars($qrow['answer']) . '</td></tr>';
        $i++;
    }
    $html .= '</tbody></table></div>';
}

$html .= '<div style="margin-top:12px; float:right; text-align:right; width:220px">';
$html .= '<div class="muted">Total Questions</div>';
$html .= '<div class="big">' . (int)$avgRow['total_answers'] . '</div>';
$html .= '<div class="muted" style="margin-top:6px">Average Score</div>';
$html .= '<div class="big">' . number_format((float)$avgRow['overall_avg'], 2) . '</div>';
$html .= '</div>';

$html .= '<div style="clear:both"></div>';
$html .= '<div style="margin-top:20px; font-size:10px; color:#444">Interpretation of Data<br>5.0 - 4.51 = Outstanding<br>4.5 - 3.51 = Very Satisfactory<br>3.5 - 2.51 = Satisfactory<br>2.5 - 1.51 = Fair<br>1.5 - 1.00 = Needs Improvement</div>';

$html .= '</body></html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'student_evaluation_' . $studentId . '_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => 0]);
exit;
