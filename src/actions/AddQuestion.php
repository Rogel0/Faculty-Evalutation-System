<?php
session_start();
include('../config/database.php');

if (isset($_POST['submitQuestionBtn'])) {
    $criteria_id = $_POST['qn-criteria'];
    $questionText = $_POST['qn-question'];

    if (empty($criteria_id) || empty($questionText)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: ../router/admin.php?module=questionnaire');
        exit();
    }


    $insertQuestionQuery = "INSERT INTO questionnaires (criteria_id, question_text, created_at) VALUES ('$criteria_id', '$questionText', NOW())";

    if ($conn->query($insertQuestionQuery)) {
        $_SESSION['success'] = 'Question added successfully!';
        $_SESSION['eval_message'] = ['type' => 'success', 'text' => 'Question added successfully!'];
    } else {
        $_SESSION['error'] = 'Error adding question: ' . $conn->error;
        $_SESSION['eval_message'] = ['type' => 'error', 'text' => 'Error adding question: ' . $conn->error];
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
