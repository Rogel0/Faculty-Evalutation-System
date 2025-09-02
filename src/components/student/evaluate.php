<?php
include_once('../config/database.php');

// Get logged-in student id
$student_id = $_SESSION['userID'] ?? null;
if (!$student_id) {
    echo '<div class="p-8 text-center text-red-600">Not logged in.</div>';
    exit;
}

// Get student info
$studentRes = $conn->prepare("SELECT firstname, lastname, course FROM users WHERE id = ? AND user_type = 'student' LIMIT 1");
$studentRes->bind_param('i', $student_id);
$studentRes->execute();
$studentRes->store_result();
if ($studentRes->num_rows === 0) {
    echo '<div class="p-8 text-center text-red-600">Student not found.</div>';
    exit;
}
$studentRes->bind_result($firstname, $lastname, $course);
$studentRes->fetch();
$studentRes->close();

// Get enrolled subjects and teachers
$enrollments = [];
$sql = "SELECT se.id, s.subject_name, t.firstname, t.lastname
FROM student_enrollments se
JOIN subjects s ON se.subject_id = s.id
JOIN users t ON se.teacher_id = t.id
WHERE se.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $enrollments[] = $row;
}
$stmt->close();
?>

<div class="max-w-3xl mx-auto mt-8">
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome, <?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h2>
        <p class="text-gray-600 mb-1">Course: <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($course); ?></span></p>
        <p class="text-gray-500 text-sm">You can evaluate your subjects and teachers below.</p>
    </div>

    <div class="bg-gray-50 rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Your Subjects & Teachers</h3>
        <?php if (empty($enrollments)): ?>
            <div class="text-center text-gray-400 py-8">No subjects assigned yet.</div>
        <?php else: ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($enrollments as $enroll): ?>
                    <li class="py-4 flex items-center justify-between">
                        <div>
                            <span class="font-medium text-gray-900 text-base"><?php echo htmlspecialchars($enroll['subject_name']); ?></span>
                            <span class="ml-2 text-sm text-gray-500">Teacher: <?php echo htmlspecialchars($enroll['lastname'] . ', ' . $enroll['firstname']); ?></span>
                        </div>
                        <a href="#" class="px-4 py-2 bg-yellow-500 text-white rounded-lg shadow hover:bg-yellow-600 text-sm font-semibold">Evaluate</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>