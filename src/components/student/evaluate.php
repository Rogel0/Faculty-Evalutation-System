<?php
include_once('../config/database.php');

// Get logged-in student id
$student_id = $_SESSION['userID'] ?? null;
if (!$student_id) {
    echo '<div class="p-8 text-center text-red-600">Not logged in.</div>';
    exit;
}

// Get active school year and semester
$syRes = $conn->query("SELECT id, semester FROM school_years WHERE is_active = 1 LIMIT 1");
$active_sy = $syRes ? $syRes->fetch_assoc() : null;

$evaluation_period_active = false;
if ($active_sy) {
    $periodRes = $conn->prepare("SELECT * FROM faculty_evaluation_periods WHERE school_year_id = ? AND semester = ? AND evaluation_type = 'student' AND active = 1 LIMIT 1");
    $periodRes->bind_param('is', $active_sy['id'], $active_sy['semester']);
    $periodRes->execute();
    $periodResult = $periodRes->get_result();
    $evaluation_period_active = $periodResult->num_rows > 0;
    $periodRes->close();
}

if (!$evaluation_period_active) {
    echo '<div class="p-8 text-center text-yellow-600 font-semibold">Evaluation period is not active. Please wait for the evaluation to start.</div>';
    exit;
}

// Get selected teacher and enrollment details
$selected_enrollment_id = $_GET['enrollment_id'] ?? null;
$selected_enrollment = null;

// Get student info
$studentRes = $conn->prepare("SELECT firstname, lastname, strand FROM users WHERE id = ? AND user_type = 'student' LIMIT 1");
$studentRes->bind_param('i', $student_id);
$studentRes->execute();
$studentRes->store_result();
if ($studentRes->num_rows === 0) {
    echo '<div class="p-8 text-center text-red-600">Student not found.</div>';
    exit;
}
$studentRes->bind_result($firstname, $lastname, $strand);
$studentRes->fetch();
$studentRes->close();

// Get enrolled subjects and teachers
$enrollments = [];

// First, check if student_enrollments table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'student_enrollments'");
if ($tableCheck->num_rows > 0) {
    $sql = "SELECT se.id, se.subject_id, se.teacher_id, s.subject_name, t.firstname, t.lastname
    FROM student_enrollments se
    JOIN subjects s ON se.subject_id = s.id
    JOIN users t ON se.teacher_id = t.id
    WHERE se.student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Check if this teacher-subject combination has already been evaluated
        $checkQuery = "SELECT COUNT(*) as count 
                       FROM evaluations 
                       WHERE evaluator_id = ? AND teacher_id = ? AND subject_id = ? AND evaluator_type = 'student'";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('iii', $student_id, $row['teacher_id'], $row['subject_id']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkRow = $checkResult->fetch_assoc();
        $row['already_evaluated'] = $checkRow['count'] > 0;
        $checkStmt->close();

        $enrollments[] = $row;
        if ($row['id'] == $selected_enrollment_id) {
            $selected_enrollment = $row;
        }
    }
    $stmt->close();

    // Check if student has already evaluated this teacher for this subject
    $alreadyEvaluated = false;
    $evaluationInfo = null;
    if ($selected_enrollment) {
        $checkQuery = "SELECT e.created_at, u.firstname, u.lastname, s.subject_name 
                       FROM evaluations e
                       LEFT JOIN users u ON e.teacher_id = u.id
                       LEFT JOIN subjects s ON e.subject_id = s.id
                       WHERE e.evaluator_id = ? AND e.teacher_id = ? AND e.subject_id = ? AND e.evaluator_type = 'student' 
                       LIMIT 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('iii', $student_id, $selected_enrollment['teacher_id'], $selected_enrollment['subject_id']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $alreadyEvaluated = true;
            $evaluationInfo = $checkResult->fetch_assoc();
        }
        $checkStmt->close();
    }
} else {
    // If table doesn't exist, show setup message
    echo '<div class="p-8 text-center text-orange-600">
        <h3 class="text-lg font-semibold mb-2">Database Setup Required</h3>
        <p class="mb-4">The evaluation system requires additional database tables.</p>
        <a href="setup_database.php" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            Run Database Setup
        </a>
    </div>';
    exit;
}

// Get evaluation questions if a teacher is selected
$questionsData = [];
if ($selected_enrollment) {
    $questionsQuery = "SELECT q.id, q.question_text, q.criteria_id, c.name 
                       FROM questionnaires q 
                       LEFT JOIN criteria c ON q.criteria_id = c.id 
                       WHERE c.evaluator_type = 'student'
                       ORDER BY c.name, q.id";
    $questionsResult = $conn->query($questionsQuery);
    if ($questionsResult) {
        while ($row = $questionsResult->fetch_assoc()) {
            $questionsData[$row['name']][] = $row;
        }
    }
}
?>

<!-- Include Toastify CSS and JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="../../scripts/toast.js"></script>

<style>
    @media (max-width: 768px) {
        .mobile-form-wide {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
        }

        .mobile-rating-grid {
            width: 100% !important;
            gap: 2px !important;
        }

        .mobile-question-container {
            width: 100% !important;
            padding-left: 0px !important;
            padding-right: 0px !important;
            margin: 0 !important;
        }

        .mobile-radio-label {
            width: 100% !important;
            min-width: 0 !important;
            padding: 8px 2px !important;
        }

        .evaluation-form-container {
            padding: 4px !important;
            margin: 0 !important;
        }
    }
</style>

<div class="w-full h-auto p-0 md:p-4 overflow-y-hidden">
    <!-- Header -->
    <div class="mb-4 md:mb-6 px-2 md:px-0">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900">Teacher Evaluation</h1>
        <p class="text-gray-600 mt-1 text-sm md:text-base">Evaluate your teachers and provide valuable feedback</p>
    </div>

    <?php if (!$selected_enrollment): ?>
        <!-- Teacher Selection View -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 md:gap-6">
            <!-- Student Info Card -->
            <div class="bg-gray-100 rounded-lg p-4 md:p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800 mb-5">Student Information</h2>
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 md:w-12 h-12 md:h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg md:text-xl"><?php echo strtoupper(substr($firstname, 0, 1)); ?></span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-base md:text-base"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h3>
                            <p class="text-sm md:text-sm text-gray-600">Course: <?php echo htmlspecialchars($strand); ?></p>
                        </div>
                    </div>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded">
                        <p class="text-sm md:text-sm text-yellow-700">
                            <strong>Instructions:</strong> Select a teacher from your enrolled subjects to begin the evaluation process.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Teacher Selection -->
            <div class="bg-white rounded-lg shadow-md p-4 md:p-6 max-h-80 md:max-h-96 lg:max-h-[32rem] flex flex-col">
                <h2 class="text-lg font-semibold text-gray-900 mb-5 flex-shrink-0">Select Teacher to Evaluate</h2>

                <div class="flex-1 overflow-y-auto">
                    <?php if (empty($enrollments)): ?>
                        <div class="text-center py-8 md:py-8 text-gray-500">
                            <svg class="w-12 md:w-12 h-12 md:h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <p class="text-sm md:text-base">No subjects assigned yet.</p>
                            <p class="text-xs md:text-sm text-gray-400 mt-1">Contact your administrator to enroll in subjects.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($enrollments as $enroll): ?>
                                <div class="border border-gray-200 rounded-lg p-4 md:p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                                        <div class="flex-1">
                                            <h3 class="font-medium text-gray-900 text-sm md:text-base"><?php echo htmlspecialchars($enroll['subject_name']); ?></h3>
                                            <p class="text-sm md:text-sm text-gray-600 mt-1">
                                                Teacher: <?php echo htmlspecialchars($enroll['firstname'] . ' ' . $enroll['lastname']); ?>
                                            </p>
                                        </div>
                                        <?php if ($enroll['already_evaluated']): ?>
                                            <div class="w-full sm:w-auto text-center px-4 md:px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-md transition-colors duration-200 shadow-sm text-sm cursor-not-allowed">
                                                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Evaluated
                                            </div>
                                        <?php else: ?>
                                            <a href="?module=evaluate_teacher&enrollment_id=<?php echo $enroll['id']; ?>"
                                                class="w-full sm:w-auto text-center px-4 md:px-4 py-3 bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold rounded-md transition-colors duration-200 shadow-sm text-sm">
                                                Evaluate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php if ($alreadyEvaluated): ?>
            <!-- Already Evaluated Message -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
                    <!-- Success Header -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Evaluation Already Completed</h1>
                        <p class="text-gray-600">You have successfully evaluated this teacher.</p>
                    </div>

                    <!-- Evaluation Details Card -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Teacher Info -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Teacher Evaluated
                                </h3>
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($evaluationInfo['firstname'], 0, 1)); ?></span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($evaluationInfo['firstname'] . ' ' . $evaluationInfo['lastname']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($evaluationInfo['subject_name']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Evaluation Date -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Evaluation Date
                                </h3>
                                <p class="text-gray-900 font-medium">
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($evaluationInfo['created_at'])); ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php
                                    $days_ago = floor((time() - strtotime($evaluationInfo['created_at'])) / (60 * 60 * 24));
                                    if ($days_ago == 0) {
                                        echo "Completed today";
                                    } elseif ($days_ago == 1) {
                                        echo "Completed 1 day ago";
                                    } else {
                                        echo "Completed $days_ago days ago";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Information Notice -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Note:</strong> You can only evaluate each teacher once per subject. Your feedback has been recorded and will be used to improve teaching quality.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="?module=evaluate_teacher"
                            class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200 text-center">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Teachers List
                        </a>
                        <a href="?module=my-evaluations"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200 text-center">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            View My Evaluations
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Evaluation Form View -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-1 md:gap-6 px-1 md:px-0">
                <!-- Teacher Info Sidebar -->
                <div class="xl:col-span-1 bg-gray-100 rounded-lg p-3 md:p-6 shadow-sm mb-2 xl:mb-0">
                    <h2 class="text-lg font-semibold text-gray-800 mb-5">Evaluation Details</h2>

                    <!-- Teacher Info -->
                    <div class="space-y-4">
                        <div class="bg-white rounded-lg p-4 md:p-4">
                            <h3 class="font-medium text-gray-900 mb-3">Teacher</h3>
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($selected_enrollment['firstname'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm md:text-base"><?php echo htmlspecialchars($selected_enrollment['firstname'] . ' ' . $selected_enrollment['lastname']); ?></p>
                                    <p class="text-xs md:text-sm text-gray-600"><?php echo htmlspecialchars($selected_enrollment['subject_name']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Student Info -->
                        <div class="bg-white rounded-lg p-4 md:p-4">
                            <h3 class="font-medium text-gray-900 mb-3">Evaluator</h3>
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($firstname, 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm md:text-base"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></p>
                                    <p class="text-xs md:text-sm text-gray-600"><?php echo htmlspecialchars($strand); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-5 md:mt-6">
                        <a href="?module=evaluate_teacher"
                            class="w-full flex items-center justify-center space-x-2 px-4 py-3 md:px-4 md:py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition-colors duration-200 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span>Back to Teachers</span>
                        </a>
                    </div>
                </div>

                <!-- Evaluation Form -->
                <div class="xl:col-span-3 bg-white rounded-lg shadow-md p-1 md:p-5 w-full min-w-0 evaluation-form-container">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-3 md:mb-6 space-y-3 md:space-y-0 w-full px-1 md:px-0">
                        <h2 class="text-lg md:text-xl font-semibold text-gray-900">Teacher Evaluation Form</h2>
                        <div class="text-xs md:text-sm text-gray-500 bg-gray-100 p-3 rounded text-center">
                            <span class="block md:inline">Rating Scale:</span>
                            <span class="block md:inline md:ml-2">5=Excellent | 4=Good | 3=Fair | 2=Poor | 1=Very Poor</span>
                        </div>
                    </div>

                    <form action="../actions/SubmitEvaluation.php" method="POST" class="space-y-5 md:space-y-6 w-full mobile-form-wide">
                        <input type="hidden" name="enrollment_id" value="<?php echo $selected_enrollment['id']; ?>">
                        <input type="hidden" name="teacher_id" value="<?php echo $selected_enrollment['teacher_id']; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo $selected_enrollment['subject_id']; ?>">

                        <div class="max-h-[60vh] md:max-h-[70vh] overflow-y-auto no-scroll w-full mobile-form-wide">
                            <?php if (!empty($questionsData)): ?>
                                <?php $criteriaIndex = 1; ?>
                                <?php foreach ($questionsData as $criteriaName => $questions): ?>
                                    <div class="border border-gray-300 rounded-lg mb-5 md:mb-6 overflow-hidden w-full">
                                        <!-- Criteria Header -->
                                        <div class="bg-gray-800 text-white p-4 md:p-4">
                                            <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-2 md:space-y-0">
                                                <h3 class="font-medium text-sm md:text-base">
                                                    Criteria 0<?php echo $criteriaIndex; ?>: <?php echo htmlspecialchars($criteriaName); ?>
                                                </h3>
                                                <span class="text-xs bg-gray-600 px-2 py-1 rounded self-start md:self-auto">
                                                    <?php echo count($questions); ?> questions
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Questions - Mobile Optimized -->
                                        <div class="bg-white">
                                            <!-- Desktop Table View -->
                                            <div class="hidden md:block">
                                                <!-- Rating Headers -->
                                                <div class="bg-gray-100 grid grid-cols-12 text-xs font-medium text-gray-700 border-b border-gray-300">
                                                    <div class="col-span-7 p-3 text-left border-r border-gray-300">Question</div>
                                                    <div class="col-span-1 p-2 text-center border-r border-gray-300">
                                                        <span class="block">5</span>
                                                        <span class="text-[10px] text-gray-500">Excellent</span>
                                                    </div>
                                                    <div class="col-span-1 p-2 text-center border-r border-gray-300">
                                                        <span class="block">4</span>
                                                        <span class="text-[10px] text-gray-500">Good</span>
                                                    </div>
                                                    <div class="col-span-1 p-2 text-center border-r border-gray-300">
                                                        <span class="block">3</span>
                                                        <span class="text-[10px] text-gray-500">Fair</span>
                                                    </div>
                                                    <div class="col-span-1 p-2 text-center border-r border-gray-300">
                                                        <span class="block">2</span>
                                                        <span class="text-[10px] text-gray-500">Poor</span>
                                                    </div>
                                                    <div class="col-span-1 p-2 text-center">
                                                        <span class="block">1</span>
                                                        <span class="text-[10px] text-gray-500">Very Poor</span>
                                                    </div>
                                                </div>

                                                <!-- Desktop Questions -->
                                                <?php foreach ($questions as $index => $question): ?>
                                                    <div class="grid grid-cols-12 items-center hover:bg-gray-50 border-b border-gray-200 last:border-b-0">
                                                        <div class="col-span-7 p-4 text-sm text-gray-700 border-r border-gray-300">
                                                            <div class="flex items-start">
                                                                <span class="font-medium text-gray-900 mr-3 flex-shrink-0"><?php echo ($index + 1); ?>.</span>
                                                                <span class="leading-relaxed"><?php echo htmlspecialchars($question['question_text']); ?></span>
                                                            </div>
                                                        </div>
                                                        <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                                            <div class="col-span-1 p-2 text-center border-r border-gray-300 last:border-r-0">
                                                                <input type="radio" name="q_<?php echo $question['id']; ?>" value="<?php echo $rating; ?>" required
                                                                    class="w-4 h-4 text-yellow-500 focus:ring-yellow-500 focus:ring-2 cursor-pointer">
                                                            </div>
                                                        <?php endfor; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <!-- Mobile Card View -->
                                            <div class="block md:hidden w-full mobile-form-wide">
                                                <?php foreach ($questions as $index => $question): ?>
                                                    <div class="mobile-question-spacing border-b border-gray-200 last:border-b-0 w-full mobile-question-container py-3">
                                                        <!-- Question -->
                                                        <div class="mb-3 w-full px-1">
                                                            <span class="font-medium text-gray-900 mr-2 text-sm"><?php echo ($index + 1); ?>.</span>
                                                            <span class="text-sm text-gray-700 leading-relaxed"><?php echo htmlspecialchars($question['question_text']); ?></span>
                                                        </div>

                                                        <!-- Rating Options - Mobile Friendly -->
                                                        <div class="grid grid-cols-5 gap-0.5 mobile-rating-grid mt-2 w-full px-1">
                                                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                                                <label class="mobile-radio-label flex flex-col items-center p-1 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 min-h-[80px] justify-center w-full">
                                                                    <input type="radio" name="q_<?php echo $question['id']; ?>" value="<?php echo $rating; ?>" required
                                                                        class="mb-1 w-5 h-5 text-yellow-500 focus:ring-yellow-500 cursor-pointer">
                                                                    <span class="text-base font-medium text-gray-900 mb-1"><?php echo $rating; ?></span>
                                                                    <span class="text-xs text-gray-500 text-center leading-tight px-0.5">
                                                                        <?php
                                                                        echo $rating == 5 ? 'Excellent' : ($rating == 4 ? 'Good' : ($rating == 3 ? 'Fair' : ($rating == 2 ? 'Poor' : 'Very Poor')));
                                                                        ?>
                                                                    </span>
                                                                </label>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $criteriaIndex++; ?>
                                <?php endforeach; ?>

                                <!-- Submit Button -->
                                <div class="sticky bottom-0 bg-white border-t border-gray-200 pt-5">
                                    <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                                        <p class="text-sm md:text-sm text-gray-600 text-center md:text-left">
                                            Please ensure all questions are answered before submitting.
                                        </p>
                                        <button type="submit"
                                            class="w-full md:w-auto px-6 md:px-8 py-4 md:py-3 bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold rounded-md transition-colors duration-200 shadow-sm text-base">
                                            Submit Evaluation
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 md:py-12 text-gray-500">
                                    <svg class="w-12 md:w-16 h-12 md:h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Questions Available</h3>
                                    <p class="text-sm md:text-base">The evaluation questionnaire is not yet available.</p>
                                    <p class="text-xs md:text-sm text-gray-400 mt-1">Please contact your administrator.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .no-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .no-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .no-scroll::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .no-scroll::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Mobile-specific enhancements */
    @media (max-width: 768px) {
        .mobile-radio-label:has(input:checked) {
            background-color: #fef3c7;
            border-color: #f59e0b;
            border-width: 2px;
        }

        .mobile-radio-label {
            transition: all 0.2s ease;
            min-height: 90px;
        }

        .mobile-radio-label:active {
            transform: scale(0.98);
        }

        .mobile-radio-label:hover {
            background-color: #f3f4f6;
        }
    }

    /* Touch-friendly radio buttons */
    input[type="radio"] {
        transform: scale(1.3);
    }

    @media (max-width: 640px) {
        input[type="radio"] {
            transform: scale(1.5);
        }
    }

    /* Better spacing for mobile */
    @media (max-width: 768px) {
        .mobile-question-spacing {
            padding: 1.25rem;
        }

        .mobile-rating-grid {
            gap: 0.5rem;
        }
    }
</style>