<?php

include_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluation_type = $_POST['evaluation_type'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    // Validate input
    if (!$evaluation_type || !$start_time || !$end_time) {
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'Missing required fields.'];
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
    if (mysqli_num_rows($result) > 0) {
        // Update existing period
        $row = mysqli_fetch_assoc($result);
        $period_id = $row['id'];
        $update = "UPDATE faculty_evaluation_periods SET start_datetime = '" . mysqli_real_escape_string($conn, $start_time) . "', end_datetime = '" . mysqli_real_escape_string($conn, $end_time) . "', updated_at = NOW() WHERE id = $period_id";
        mysqli_query($conn, $update);
        $_SESSION['eval_message'] = ['type' => 'success', 'text' => 'Evaluation period updated successfully.'];
    } else {
        // Insert new period
        $insert = "INSERT INTO faculty_evaluation_periods (school_year_id, semester, evaluation_type, start_datetime, end_datetime) VALUES ($school_year_id, '" . mysqli_real_escape_string($conn, $semester) . "', '" . mysqli_real_escape_string($conn, $evaluation_type) . "', '" . mysqli_real_escape_string($conn, $start_time) . "', '" . mysqli_real_escape_string($conn, $end_time) . "')";
        mysqli_query($conn, $insert);
        $_SESSION['eval_message'] = ['type' => 'success', 'text' => 'Evaluation period created successfully.'];
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
