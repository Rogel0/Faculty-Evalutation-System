<?php
session_start();
include('../config/database.php');

// Authorization: supervisor only
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'supervisor') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$supervisor_id = $_SESSION['userID'];
$teacher_id = $_POST['teacher_id'] ?? null;

if (!$teacher_id) {
    $_SESSION['error'] = 'Missing teacher selection.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Ensure the teacher exists
$tstmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'teacher' LIMIT 1");
$tstmt->bind_param('i', $teacher_id);
$tstmt->execute();
$tres = $tstmt->get_result();
if (!$tres || $tres->num_rows === 0) {
    $_SESSION['error'] = 'Selected teacher not found.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
$tstmt->close();

// Check active school year and supervisor evaluation period
$syRes = $conn->query("SELECT id, semester FROM school_years WHERE is_active = 1 LIMIT 1");
$active_sy = $syRes ? $syRes->fetch_assoc() : null;
if (!$active_sy) {
    $_SESSION['error'] = 'No active school year found.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$periodChk = $conn->prepare("SELECT id FROM faculty_evaluation_periods WHERE school_year_id = ? AND semester = ? AND evaluation_type = 'supervisor' AND active = 1 LIMIT 1");
$periodChk->bind_param('is', $active_sy['id'], $active_sy['semester']);
$periodChk->execute();
$periodRes = $periodChk->get_result();
if (!$periodRes || $periodRes->num_rows === 0) {
    $_SESSION['error'] = 'Supervisor evaluation period is not active.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
$periodChk->close();

// Get questions for supervisor
$qRes = $conn->query("SELECT q.id FROM questionnaires q LEFT JOIN criteria c ON q.criteria_id = c.id WHERE c.evaluator_type = 'supervisor'");
$questionIds = [];
if ($qRes) {
    while ($r = $qRes->fetch_assoc()) $questionIds[] = $r['id'];
}
if (empty($questionIds)) {
    $_SESSION['error'] = 'No questions found for supervisor evaluation.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Validate responses
$missing = [];
$responses = [];
foreach ($questionIds as $qid) {
    $key = "q_$qid";
    if (!isset($_POST[$key]) || $_POST[$key] === '') {
        $missing[] = $qid;
    } else {
        $responses[$qid] = (int)$_POST[$key];
    }
}
if (!empty($missing)) {
    $_SESSION['error'] = 'Please answer all questions.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Check if supervisor already evaluated this teacher this school year
$checkQ = "SELECT COUNT(*) as count FROM evaluations WHERE evaluator_id = ? AND teacher_id = ? AND evaluator_type = 'supervisor' AND school_year_id = ?";
$chk = $conn->prepare($checkQ);
$chk->bind_param('iii', $supervisor_id, $teacher_id, $active_sy['id']);
$chk->execute();
$cres = $chk->get_result();
$already = false;
if ($cres && $crow = $cres->fetch_assoc()) {
    $already = $crow['count'] > 0;
}
$chk->close();
if ($already) {
    $_SESSION['error'] = 'You have already evaluated this teacher for the current school year.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Insert responses with defensive checks
try {
    $conn->begin_transaction();
    $inserted = 0;
    foreach ($responses as $qid => $val) {
        $ins = $conn->prepare("INSERT INTO evaluations (evaluator_id, evaluator_type, teacher_id, questionnaire_id, answer, school_year_id, created_at) VALUES (?, 'supervisor', ?, ?, ?, ?, NOW())");
        if (!$ins) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        // Five integer placeholders: evaluator_id, teacher_id, questionnaire_id, answer, school_year_id
        if (!@$ins->bind_param('iiiii', $supervisor_id, $teacher_id, $qid, $val, $active_sy['id'])) {
            $err = $ins->error ?: $conn->error;
            $ins->close();
            throw new Exception('bind_param failed: ' . $err);
        }
        if (!@$ins->execute()) {
            $err = $ins->error ?: $conn->error;
            $ins->close();
            throw new Exception('Execute failed: ' . $err);
        }
        $inserted++;
        $ins->close();
    }
    $conn->commit();
    // (Optional) remove development log file if present
    $devLog = __DIR__ . '/submit_supervisor_eval.log';
    if (file_exists($devLog)) {
        @unlink($devLog);
    }
    $_SESSION['success'] = 'Supervisor evaluation submitted successfully.';
    header('Location: ../router/supervisor.php?module=evaluation');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    // Do not write development logs to disk. Surface error to session for admin/diagnostic use.
    $_SESSION['error'] = 'Failed to submit evaluation: ' . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
