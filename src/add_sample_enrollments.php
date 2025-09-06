<?php
// Sample data insertion script for testing the evaluation system
include('config/database.php');

echo "<h2>Add Sample Enrollments</h2>";

// Check if we have students and teachers
$studentsQuery = "SELECT id, firstname, lastname FROM users WHERE user_type = 'student' LIMIT 5";
$teachersQuery = "SELECT id, firstname, lastname FROM users WHERE user_type = 'teacher' LIMIT 5"; 
$subjectsQuery = "SELECT id, subject_name FROM subjects LIMIT 5";

$students = $conn->query($studentsQuery)->fetch_all(MYSQLI_ASSOC);
$teachers = $conn->query($teachersQuery)->fetch_all(MYSQLI_ASSOC);
$subjects = $conn->query($subjectsQuery)->fetch_all(MYSQLI_ASSOC);

if (empty($students)) {
    echo "<p style='color: red;'>No students found. Please add students first through the admin panel.</p>";
    echo "<p><a href='admin_layout.php?module=add_student'>← Add Students</a></p>";
    exit;
}

if (empty($teachers)) {
    echo "<p style='color: red;'>No teachers found. Please create teacher accounts first.</p>";
    echo "<p>Create users with user_type = 'teacher' in your database.</p>";
    exit;
}

if (empty($subjects)) {
    echo "<p style='color: red;'>No subjects found. Please run the database setup first.</p>";
    echo "<p><a href='setup_database.php'>← Run Database Setup</a></p>";
    exit;
}

// Create sample enrollments
echo "<h3>Creating Sample Enrollments:</h3>";

$enrollmentCount = 0;
foreach ($students as $student) {
    foreach ($subjects as $index => $subject) {
        if (isset($teachers[$index % count($teachers)])) {
            $teacher = $teachers[$index % count($teachers)];
            
            // Check if enrollment already exists
            $checkStmt = $conn->prepare("SELECT id FROM student_enrollments WHERE student_id = ? AND teacher_id = ? AND subject_id = ?");
            $checkStmt->bind_param('iii', $student['id'], $teacher['id'], $subject['id']);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                $insertStmt = $conn->prepare("INSERT INTO student_enrollments (student_id, teacher_id, subject_id) VALUES (?, ?, ?)");
                $insertStmt->bind_param('iii', $student['id'], $teacher['id'], $subject['id']);
                
                if ($insertStmt->execute()) {
                    echo "<p style='color: green;'>✓ Enrolled {$student['firstname']} {$student['lastname']} with {$teacher['firstname']} {$teacher['lastname']} for {$subject['subject_name']}</p>";
                    $enrollmentCount++;
                } else {
                    echo "<p style='color: red;'>✗ Failed to enroll {$student['firstname']} {$student['lastname']}</p>";
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        }
    }
}

echo "<h3>Summary:</h3>";
echo "<p>Created $enrollmentCount new enrollments.</p>";
echo "<p>Students can now access the evaluation system!</p>";

echo "<p><a href='student_layout.php?module=evaluate_teacher'>← Go to Student Evaluation</a></p>";
echo "<p><a href='index.php'>← Back to Login</a></p>";
?>
