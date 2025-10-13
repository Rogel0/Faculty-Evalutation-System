<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

// Load Composer autoload (project root /vendor)
$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    http_response_code(500);
    echo 'Dependencies not installed. Please run "composer install" in the project root.';
    exit;
}

use Dompdf\Dompdf;

// Only teachers can generate their PDF
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'teacher') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$teacherId = (int)$_SESSION['userID'];

// Optional school year
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
        $semLabel = '';
        if (isset($row['semester'])) {
            $s = $row['semester'];
            if ($s === '1' || $s === 1 || stripos($s, '1') !== false) $semLabel = '1st Semester';
            else if ($s === '2' || $s === 2 || stripos($s, '2') !== false) $semLabel = '2nd Semester';
            else $semLabel = htmlspecialchars($s);
        }
        $schoolYearLabel = trim(($semLabel ? $semLabel . ', ' : '') . 'School Year ' . $row['year']);
    }
} else {
    $syRes = $conn->query("SELECT id, year, semester FROM school_years WHERE is_active = 1 LIMIT 1");
    if ($syRes && $row = $syRes->fetch_assoc()) {
        $currentSchoolYear = (int)$row['id'];
        $semLabel = '';
        if (isset($row['semester'])) {
            $s = $row['semester'];
            if ($s === '1' || $s === 1 || stripos($s, '1') !== false) $semLabel = '1st Semester';
            else if ($s === '2' || $s === 2 || stripos($s, '2') !== false) $semLabel = '2nd Semester';
            else $semLabel = htmlspecialchars($s);
        }
        $schoolYearLabel = trim(($semLabel ? $semLabel . ', ' : '') . 'School Year ' . $row['year']);
    }
}

// Fetch teacher name
$tstmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ? LIMIT 1");
$tstmt->bind_param('i', $teacherId);
$tstmt->execute();
$tres = $tstmt->get_result();
$teacher = $tres->fetch_assoc() ?: ['firstname' => 'Teacher', 'lastname' => ''];
$teacherName = trim($teacher['firstname'] . ' ' . $teacher['lastname']);

// Fetch per-question aggregates (LEFT JOIN questionnaires/criteria so missing rows still show)
$q = "SELECT 
        COALESCE(c.id, 0) AS cid,
        COALESCE(c.name, 'Uncategorized') AS criteria_name,
        e.questionnaire_id AS qid,
        q.question_text,
        ROUND(AVG(e.answer),2) AS avg_rating,
        COUNT(DISTINCT e.evaluator_id) AS respondents
            FROM evaluations e
            LEFT JOIN questionnaires q ON e.questionnaire_id = q.id
            LEFT JOIN criteria c ON q.criteria_id = c.id
    WHERE e.teacher_id = ? AND e.evaluator_type = 'student'";
if ($currentSchoolYear) $q .= " AND e.school_year_id = ?";
// group by the questionnaire id (which may be null) and order by criteria then questionnaire id
$q .= " GROUP BY e.questionnaire_id ORDER BY cid, qid";

$stmt = $conn->prepare($q);
if ($currentSchoolYear) {
    $stmt->bind_param('ii', $teacherId, $currentSchoolYear);
} else {
    $stmt->bind_param('i', $teacherId);
}
$stmt->execute();
$res = $stmt->get_result();

$criteriaList = [];
$questionsByCriteria = [];
while ($r = $res->fetch_assoc()) {
    $cid = (int)$r['cid'];
    if (!isset($criteriaList[$cid])) {
        $criteriaList[$cid] = ['cid' => $cid, 'criteria_name' => $r['criteria_name'], 'criteria_avg' => 0, 'respondents' => 0, 'questions' => []];
    }
    $questionsByCriteria[$cid][] = $r;
}
// Compute section averages (from questionsByCriteria) to show per-criteria averages
foreach ($questionsByCriteria as $cid => $qs) {
    $sum = 0;
    $count = 0;
    foreach ($qs as $qq) {
        if ($qq['avg_rating'] !== null) {
            $sum += (float)$qq['avg_rating'];
            $count++;
        }
    }
    $avg = $count ? round($sum / $count, 2) : 0;
    $criteriaList[$cid]['criteria_avg'] = $avg;
}

// Compute final overall average and total respondents
$sumQ = $conn->prepare("SELECT COUNT(DISTINCT e.evaluator_id) AS respondents, ROUND(AVG(e.answer),2) AS overall_avg FROM evaluations e WHERE e.teacher_id = ? AND e.evaluator_type = 'student'" . ($currentSchoolYear ? " AND e.school_year_id = ?" : ""));
if ($currentSchoolYear) {
    $sumQ->bind_param('ii', $teacherId, $currentSchoolYear);
} else {
    $sumQ->bind_param('i', $teacherId);
}
$sumQ->execute();
$sres = $sumQ->get_result();
$summary = $sres->fetch_assoc() ?: ['respondents' => 0, 'overall_avg' => 0];

// final and remarks (compute early so header can use them)
$final = (float)$summary['overall_avg'];
$remarks = 'Needs Improvement';
if ($final >= 4.51) $remarks = 'Outstanding';
else if ($final >= 3.51) $remarks = 'Very Satisfactory';
else if ($final >= 2.51) $remarks = 'Satisfactory';
else if ($final >= 1.51) $remarks = 'Fair';

// Build HTML with per-criteria sections and averages
$html = '<!doctype html><html><head><meta charset="utf-8"><title>Faculty Performance Evaluation</title>';
$html .= '<style>body{font-family: Arial, Helvetica, sans-serif; font-size:12px; color:#111} .pdf-header{display:flex;justify-content:space-between;align-items:center} .logo{font-weight:bold} .title{font-size:16px;font-weight:bold;text-align:center} .sub{font-size:11px;color:#444;text-align:center} table{width:100%;border-collapse:collapse;margin-top:8px} th,td{padding:6px;border:1px solid #ddd} .section{margin-top:12px} .right{text-align:right} .big{font-size:18px;font-weight:bold} .muted{font-size:11px;color:#666} .box{border:2px solid #111;padding:6px;display:inline-block;min-width:60px;text-align:center;font-weight:bold} .criteria-header{background:#f3f4f6;padding:6px;border:1px solid #ddd;display:flex;justify-content:space-between;align-items:center} .small{font-size:11px}</style></head><body>';
$html .= '<div class="pdf-header">';
$html .= '<div class="logo">';
$html .= '<img src="" alt="logo" style="height:60px"/>'; // placeholder - add src if available
$html .= '</div>';
$html .= '<div style="flex:1; margin-left:8px">';
$html .= '<div class="title">FACULTY PERFORMANCE EVALUATION</div>';
$html .= '<div class="sub">STUDENTS PRE-EVALUATION</div>';
$html .= '<div class="sub">' . htmlspecialchars($schoolYearLabel) . '</div>';
$html .= '</div>';
$html .= '<div style="text-align:right">';
$html .= '<div class="small"><strong>Employee No</strong><div>' . htmlspecialchars($teacherId) . '</div></div>';
$html .= '<div class="small" style="margin-top:6px"><strong>Rating</strong><div>' . number_format($final, 2) . '</div></div>';
$html .= '<div class="small" style="margin-top:6px"><strong>Remarks</strong><div>' . htmlspecialchars($remarks) . '</div></div>';
$html .= '</div>';
$html .= '</div>';
$html .= '<hr style="margin-top:8px;margin-bottom:8px">';
$html .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">';
$html .= '<div><strong>Employee Name:</strong> ' . htmlspecialchars($teacherName) . '</div>';
$html .= '<div></div>';
$html .= '</div>';

foreach ($criteriaList as $cid => $crit) {
    $html .= '<div class="section">';
    $html .= '<div class="criteria-header"><strong>' . htmlspecialchars($crit['criteria_name']) . '</strong>';
    $html .= '<div style="border:1px solid #111;padding:4px 8px;font-weight:bold;">' . number_format((float)$crit['criteria_avg'], 2) . '</div>';
    $html .= '</div>';

    // Questions list: two-column style with question on left and rating on right
    $html .= '<table style="margin-top:6px"><tbody>';
    $i = 1;
    if (isset($questionsByCriteria[$cid])) {
        foreach ($questionsByCriteria[$cid] as $qq) {
            $qtext = $qq['question_text'] ?: 'Question text unavailable'; // fallback - do not print numeric id
            $rating = $qq['avg_rating'] !== null ? number_format((float)$qq['avg_rating'], 2) : '-';
            $html .= '<tr>';
            $html .= '<td style="padding:6px; vertical-align:top">' . $i . '. ' . htmlspecialchars($qtext) . '</td>';
            $html .= '<td style="width:80px; padding:6px; vertical-align:top; text-align:right">' . $rating . '</td>';
            $html .= '</tr>';
            $i++;
        }
    }
    $html .= '</tbody></table>';
    $html .= '</div>';
}


// compute remarks based on final average
$final = (float)$summary['overall_avg'];
$remarks = 'Needs Improvement';
if ($final >= 4.51) $remarks = 'Outstanding';
else if ($final >= 3.51) $remarks = 'Very Satisfactory';
else if ($final >= 2.51) $remarks = 'Satisfactory';
else if ($final >= 1.51) $remarks = 'Fair';

$html .= '<div style="margin-top:12px; float:right; text-align:right; width:260px">';
$html .= '<div class="muted">TOTAL RESPONDENTS</div>';
$html .= '<div class="big">' . (int)$summary['respondents'] . '</div>';
$html .= '<div class="muted" style="margin-top:6px">FINAL RATING</div>';
$html .= '<div class="box" style="font-size:16px">' . number_format($final, 2) . '</div>';
$html .= '<div style="margin-top:6px">Remarks: <strong>' . $remarks . '</strong></div>';
$html .= '</div>';

$html .= '<div style="clear:both"></div>';
$html .= '<div style="margin-top:18px; font-size:10px; color:#444">Interpretation of Data<br>5.0 - 4.51 = Outstanding<br>4.5 - 3.51 = Very Satisfactory<br>3.5 - 2.51 = Satisfactory<br>2.5 - 1.51 = Fair<br>1.5 - 1.00 = Needs Improvement</div>';

$html .= '</body></html>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'teacher_report_' . $teacherId . '_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => 0]); // open in browser; set Attachment=>1 to force download

exit;
