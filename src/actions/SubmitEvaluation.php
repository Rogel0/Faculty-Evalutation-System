<?php
session_start();
include('../config/database.php');

// Check if user is logged in as student
if (!isset($_SESSION['userID']) || $_SESSION['user_type'] !== 'student') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['userID'];
    $enrollment_id = $_POST['enrollment_id'] ?? null;
    $teacher_id = $_POST['teacher_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;

    // Validate required fields
    if (!$enrollment_id || !$teacher_id || !$subject_id) {
        $_SESSION['error'] = 'Missing required information.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Check if evaluation already exists for this student, teacher, and subject
    $checkQuery = "SELECT e.evaluator_id, u.firstname, u.lastname, s.subject_name 
                   FROM evaluations e
                   LEFT JOIN users u ON e.teacher_id = u.id
                   LEFT JOIN subjects s ON e.subject_id = s.id
                   WHERE e.evaluator_id = ? AND e.teacher_id = ? AND e.subject_id = ? AND e.evaluator_type = 'student' 
                   LIMIT 1";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('iii', $student_id, $teacher_id, $subject_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $existingEvaluation = $checkResult->fetch_assoc();
        $teacherName = $existingEvaluation['firstname'] . ' ' . $existingEvaluation['lastname'];
        $subjectName = $existingEvaluation['subject_name'];
        $_SESSION['error'] = "You have already evaluated $teacherName for $subjectName. You can only evaluate each teacher once per subject.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
    $checkStmt->close();

    // Verify that a student evaluation period is active for the current school year and semester
    $syRes = $conn->query("SELECT id, semester FROM school_years WHERE is_active = 1 LIMIT 1");
    $activeSy = $syRes ? $syRes->fetch_assoc() : null;
    if (!$activeSy) {
        $_SESSION['error'] = 'No active school year found.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
    $periodChk = $conn->prepare("SELECT id FROM faculty_evaluation_periods WHERE school_year_id = ? AND semester = ? AND evaluation_type = 'student' AND active = 1 LIMIT 1");
    $periodChk->bind_param('is', $activeSy['id'], $activeSy['semester']);
    $periodChk->execute();
    $periodRes = $periodChk->get_result();
    if (!$periodRes || $periodRes->num_rows === 0) {
        $_SESSION['error'] = 'Student evaluation period is not active.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
    $periodChk->close();

    // Get all questions for validation
    $questionsQuery = "SELECT q.id FROM questionnaires q 
                       LEFT JOIN criteria c ON q.criteria_id = c.id 
                       WHERE c.evaluator_type = 'student'";
    $questionsResult = $conn->query($questionsQuery);
    $questionIds = [];
    while ($row = $questionsResult->fetch_assoc()) {
        $questionIds[] = $row['id'];
    }

    // Debug: Check if we have any questions
    if (empty($questionIds)) {
        $_SESSION['error'] = 'No questions found for student evaluation.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Validate that all questions are answered
    $responses = [];
    $missingAnswers = [];
    foreach ($questionIds as $questionId) {
        if (!isset($_POST["q_$questionId"]) || empty($_POST["q_$questionId"])) {
            $missingAnswers[] = $questionId;
        } else {
            $responses["q_$questionId"] = (int)$_POST["q_$questionId"];
        }
    }

    if (!empty($missingAnswers)) {
        $_SESSION['error'] = 'Please answer all questions before submitting.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Get current school year (or set to 1 as default)
        $currentSchoolYear = 1; // Default value
        $schoolYearQuery = "SELECT id FROM school_years WHERE is_active = 1 LIMIT 1";
        $schoolYearResult = $conn->query($schoolYearQuery);
        if ($schoolYearResult && $schoolYearResult->num_rows > 0) {
            $schoolYearRow = $schoolYearResult->fetch_assoc();
            $currentSchoolYear = $schoolYearRow['id'];
        }

        // Insert individual question responses only (no main evaluation record)
        foreach ($questionIds as $questionId) {
            if (isset($_POST["q_$questionId"])) {
                $rating = $_POST["q_$questionId"];
                if ($rating >= 1 && $rating <= 5) {
                    // Insert each answer as a separate evaluation record
                    $answerQuery = "INSERT INTO evaluations (evaluator_id, evaluator_type, teacher_id, subject_id, questionnaire_id, answer, school_year_id, created_at) 
                                   VALUES (?, 'student', ?, ?, ?, ?, ?, NOW())";
                    $answerStmt = $conn->prepare($answerQuery);
                    if (!$answerStmt) {
                        throw new Exception("Answer prepare failed: " . $conn->error);
                    }
                    $answerStmt->bind_param('iiiisi', $student_id, $teacher_id, $subject_id, $questionId, $rating, $currentSchoolYear);
                    if (!$answerStmt->execute()) {
                        throw new Exception("Answer execute failed: " . $answerStmt->error);
                    }
                    $answerStmt->close();
                }
            }
        }

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = 'Evaluation submitted successfully!';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (Exception $e) {
        // Rollback transaction onHey. Hi.  error
        $conn->rollback();
        $_SESSION['error'] = 'Failed to submit evaluation: ' . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    $_SESSION['error'] = 'Invalid request method.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
