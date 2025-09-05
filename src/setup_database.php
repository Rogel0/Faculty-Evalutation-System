<?php
// Simple setup script to create necessary tables for the evaluation system
include('config/database.php');

echo "<h2>Faculty Evaluation System - Database Setup</h2>";
echo "<p>This will create the necessary tables for the evaluation system.</p>";

// Create student_enrollments table if it doesn't exist (matching your schema)
$createEnrollmentsTable = "
CREATE TABLE IF NOT EXISTS student_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year_id INT DEFAULT NULL
)";

// Create subjects table if it doesn't exist (matching your schema)
$createSubjectsTable = "
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_code VARCHAR(20),
    subject_name VARCHAR(100),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// Note: Your evaluations table already exists in your database
// We'll work with the existing structure

$tables = [
    'student_enrollments' => $createEnrollmentsTable,
    'subjects' => $createSubjectsTable
];

foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Table '$tableName' created successfully (or already exists)</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating table '$tableName': " . $conn->error . "</p>";
    }
}

// Add some sample subjects (matching your schema)
$sampleSubjects = [
    ['MATH101', 'Mathematics', 'Basic Mathematics'],
    ['PHYS101', 'Physics', 'Introduction to Physics'],
    ['CHEM101', 'Chemistry', 'General Chemistry'],
    ['BIOL101', 'Biology', 'General Biology'],
    ['ENG101', 'English', 'English Composition'],
    ['CS101', 'Computer Science', 'Introduction to Programming']
];

echo "<h3>Adding Sample Subjects:</h3>";
$insertSubject = "INSERT IGNORE INTO subjects (subject_code, subject_name, description) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insertSubject);

foreach ($sampleSubjects as $subject) {
    $stmt->bind_param('sss', $subject[0], $subject[1], $subject[2]);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Subject '{$subject[1]}' added</p>";
    }
}

echo "<h3>Setup Complete!</h3>";
echo "<p>You can now use the evaluation system. Make sure to:</p>";
echo "<ul>";
echo "<li>Add students through the admin panel</li>";
echo "<li>Create teacher accounts with user_type = 'teacher'</li>";
echo "<li>Create student enrollments linking students to teachers and subjects</li>";
echo "<li>Add evaluation criteria and questions through the questionnaire system</li>";
echo "</ul>";

echo "<p><a href='index.php'>← Back to Login</a></p>";
?>
