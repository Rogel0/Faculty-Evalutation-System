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

// Create teacher_subjects table if it doesn't exist
$createTeacherSubjects = "
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year_id INT DEFAULT NULL
)";

// Note: Your evaluations table already exists in your database
// We'll work with the existing structure

$tables = [
    'student_enrollments' => $createEnrollmentsTable,
    'subjects' => $createSubjectsTable,
    'teacher_subjects' => $createTeacherSubjects
];

foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Table '$tableName' created successfully (or already exists)</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating table '$tableName': " . $conn->error . "</p>";
    }
}

// Ensure student_enrollments has a teacher_id column (safe ALTER)
$colRes = $conn->query("SHOW COLUMNS FROM student_enrollments LIKE 'teacher_id'");
if ($colRes && $colRes->num_rows === 0) {
    if ($conn->query("ALTER TABLE student_enrollments ADD COLUMN teacher_id INT DEFAULT NULL")) {
        echo "<p style='color: green;'>✓ Column 'teacher_id' added to student_enrollments</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to add teacher_id column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: gray;'>Column 'teacher_id' already exists in student_enrollments</p>";
}

// Ensure users has teacher_code column
$colRes2 = $conn->query("SHOW COLUMNS FROM users LIKE 'teacher_code'");
if ($colRes2 && $colRes2->num_rows === 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN teacher_code VARCHAR(50) NULL, ADD UNIQUE KEY ux_users_teacher_code (teacher_code)")) {
        echo "<p style='color: green;'>✓ Column 'teacher_code' added to users</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to add teacher_code column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: gray;'>Column 'teacher_code' already exists in users</p>";
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
