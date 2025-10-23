<?php

include_once('../config/database.php');

// Ensure session is started for flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug logging removed for production - previous debug writes to start_eval_post_debug.log were deleted.

    $evaluation_type = isset($_POST['evaluation_type']) ? trim($_POST['evaluation_type']) : '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    // Fallback: if hidden evaluation_type is missing, try to read from the submit button value
    if (!$evaluation_type && isset($_POST['start_btn'])) {
        $evaluation_type = trim($_POST['start_btn']);
    }

    // Validate input
    if (!$evaluation_type || !$start_time || !$end_time) {
        // If evaluation_type is still missing, log POST for debugging
        if (!$evaluation_type) {
            $posted = print_r($_POST, true);
            // Debug logging removed. Details: $posted
            $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'Missing required fields. evaluation_type is blank. Debug logged.'];
        } else {
            $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'Missing required fields.'];
        }
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Ensure server timezone matches UI timezone to avoid date mismatches
    // Force timezone to Asia/Manila to match the admin UI expectations
    date_default_timezone_set('Asia/Manila');

    // Parse and validate datetimes
    try {
        $tz = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $tz);
        // datetime-local from browser is usually "Y-m-d\TH:i" (no seconds)
        // Parse explicitly with the Manila timezone first, fallback to generic parser with tz
        $startDt = DateTime::createFromFormat('Y-m-d\TH:i', $start_time, $tz) ?: new DateTime($start_time, $tz);
        $endDt = DateTime::createFromFormat('Y-m-d\TH:i', $end_time, $tz) ?: new DateTime($end_time, $tz);
    } catch (Exception $e) {
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'Invalid date/time format.'];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Start time cannot be in the past (allow a small grace window to account for clock drift/rounding)
    $toleranceSeconds = 60; // allow 60 seconds tolerance
    $diffSeconds = $startDt->getTimestamp() - $now->getTimestamp();
    if ($diffSeconds < -$toleranceSeconds) {
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'Start time cannot be in the past. Please choose a start time that is now or in the future.'];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Require the start date to match today's date (calendar date)
    // Compare the raw date part (YYYY-MM-DD) from the submitted datetime-local string
    $submittedDatePart = null;
    if (is_string($start_time) && strlen($start_time) >= 10) {
        $submittedDatePart = substr($start_time, 0, 10);
    }

    if ($submittedDatePart === null || $submittedDatePart !== $now->format('Y-m-d')) {
        $evalLabel = ucfirst($evaluation_type);
        // Provide debug info to help trace timezone/parse issues
        $raw = htmlspecialchars($start_time);
        $parsed = $startDt->format('Y-m-d H:i:s');
        $serverNow = $now->format('Y-m-d H:i:s');
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => "Start date must be today for $evalLabel evaluation. (submitted: $raw, parsed: $parsed, server now: $serverNow)"];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // End must be after start
    if ($endDt <= $startDt) {
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'End time must be after start time.'];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Get active school year
    $schoolYearQuery = "SELECT * FROM school_years WHERE is_active = 1 LIMIT 1";
    $schoolYearResult = mysqli_query($conn, $schoolYearQuery);
    $schoolYear = mysqli_fetch_assoc($schoolYearResult);

    if (!$schoolYear) {
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'No active school year found.'];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // For this example, we'll use the semester from the active school year
    $semester = $schoolYear['semester'];
    $school_year_id = $schoolYear['id'];

    $today = date('Y-m-d H:i:s');
    // Remove the check that blocks future start times
    if ($end_time < $today) {
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'End time is in the past.'];
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Check if a period already exists for this type, school year, and semester
    $check = "SELECT id FROM faculty_evaluation_periods WHERE school_year_id = $school_year_id AND semester = '" . mysqli_real_escape_string($conn, $semester) . "' AND evaluation_type = '" . mysqli_real_escape_string($conn, $evaluation_type) . "' LIMIT 1";
    $result = mysqli_query($conn, $check);
    // Determine if the period should be active right now
    $isActive = ($startDt <= $now && $endDt >= $now) ? 1 : 0;

    if (mysqli_num_rows($result) > 0) {
        // Update existing period
        $row = mysqli_fetch_assoc($result);
        $period_id = $row['id'];
        $update = "UPDATE faculty_evaluation_periods SET start_datetime = '" . mysqli_real_escape_string($conn, $start_time) . "', end_datetime = '" . mysqli_real_escape_string($conn, $end_time) . "', active = $isActive, updated_at = NOW() WHERE id = $period_id";
        mysqli_query($conn, $update);
        $_SESSION['eval_message'] = ['type' => 'success', 'text' => 'Evaluation period updated successfully.'];
    } else {
        // Insert new period
        $insert = "INSERT INTO faculty_evaluation_periods (school_year_id, semester, evaluation_type, start_datetime, end_datetime, active) VALUES ($school_year_id, '" . mysqli_real_escape_string($conn, $semester) . "', '" . mysqli_real_escape_string($conn, $evaluation_type) . "', '" . mysqli_real_escape_string($conn, $start_time) . "', '" . mysqli_real_escape_string($conn, $end_time) . "', $isActive)";
        mysqli_query($conn, $insert);
        $_SESSION['eval_message'] = ['type' => 'success', 'text' => 'Evaluation period created successfully.'];
    }

    // Recalculate active flags for all periods based on server NOW()
    // This ensures the admin UI sees the updated active state immediately
    $conn->query("UPDATE faculty_evaluation_periods SET active = 0 WHERE end_datetime < NOW()");
    $conn->query("UPDATE faculty_evaluation_periods SET active = 1 WHERE start_datetime <= NOW() AND end_datetime >= NOW()");

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
