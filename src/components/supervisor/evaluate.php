<?php
include_once(__DIR__ . '/../../config/database.php');

// Supervisor evaluator
$supervisor_id = $_SESSION['userID'] ?? null;
if (!$supervisor_id || ($_SESSION['user_type'] ?? '') !== 'supervisor') {
    echo '<div class="p-8 text-center text-red-600">Not authorized.</div>';
    exit;
}

// Active school year and supervisor evaluation period
$syRes = $conn->query("SELECT id, semester FROM school_years WHERE is_active = 1 LIMIT 1");
$active_sy = $syRes ? $syRes->fetch_assoc() : null;

$evaluation_period_active = false;
if ($active_sy) {
    $periodRes = $conn->prepare("SELECT * FROM faculty_evaluation_periods WHERE school_year_id = ? AND semester = ? AND evaluation_type = 'supervisor' AND active = 1 LIMIT 1");
    $periodRes->bind_param('is', $active_sy['id'], $active_sy['semester']);
    $periodRes->execute();
    $periodResult = $periodRes->get_result();
    $evaluation_period_active = $periodResult->num_rows > 0;
    $periodRes->close();
}

if (!$evaluation_period_active) {
    echo '<div class="p-8 text-center text-yellow-600 font-semibold">Supervisor evaluation period is not active.</div>';
    exit;
}

// Get selected teacher
$selected_teacher_id = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : null;
$selected_teacher = null;
if ($selected_teacher_id) {
    $tstmt = $conn->prepare("SELECT id, firstname, lastname, department FROM users WHERE id = ? AND user_type = 'teacher' LIMIT 1");
    $tstmt->bind_param('i', $selected_teacher_id);
    $tstmt->execute();
    $tres = $tstmt->get_result();
    if ($tres && $tres->num_rows > 0) {
        $selected_teacher = $tres->fetch_assoc();
    }
    $tstmt->close();
}

// Get list of teachers
$teachers = [];
$tres2 = $conn->query("SELECT id, firstname, lastname, department FROM users WHERE user_type = 'teacher' ORDER BY lastname, firstname");
if ($tres2) {
    while ($r = $tres2->fetch_assoc()) {
        $teachers[] = $r;
    }
}

// Determine which teachers this supervisor already evaluated for the active school year
$evaluatedTeachers = [];
if ($active_sy) {
    $etStmt = $conn->prepare("SELECT teacher_id FROM evaluations WHERE evaluator_type = 'supervisor' AND evaluator_id = ? AND school_year_id = ?");
    if ($etStmt) {
        $etStmt->bind_param('ii', $supervisor_id, $active_sy['id']);
        $etStmt->execute();
        $etRes = $etStmt->get_result();
        if ($etRes) {
            while ($row = $etRes->fetch_assoc()) {
                $evaluatedTeachers[] = (int)$row['teacher_id'];
            }
        }
        $etStmt->close();
    }
}

// Check if already evaluated by this supervisor for the current school year
$alreadyEvaluated = false;
$evaluationInfo = null;
if ($selected_teacher && $active_sy) {
    $checkQ = "SELECT COUNT(*) AS count, MAX(created_at) AS created_at FROM evaluations WHERE evaluator_id = ? AND teacher_id = ? AND evaluator_type = 'supervisor' AND school_year_id = ? LIMIT 1";
    $chk = $conn->prepare($checkQ);
    $chk->bind_param('iii', $supervisor_id, $selected_teacher_id, $active_sy['id']);
    $chk->execute();
    $cres = $chk->get_result();
    if ($cres && $crow = $cres->fetch_assoc()) {
        $alreadyEvaluated = $crow['count'] > 0;
        $evaluationInfo = $crow;
    }
    $chk->close();
}

// Get supervisor-specific questions
$questionsData = [];
if ($selected_teacher) {
    $questionsQuery = "SELECT q.id, q.question_text, q.criteria_id, c.name FROM questionnaires q LEFT JOIN criteria c ON q.criteria_id = c.id WHERE c.evaluator_type = 'supervisor' ORDER BY c.name, q.id";
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

<div class="w-full h-auto p-0 md:p-4 overflow-y-hidden">
    <div class="mb-4 md:mb-6 px-2 md:px-0">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900">Supervisor — Evaluate Teachers</h1>
        <p class="text-gray-600 mt-1 text-sm md:text-base">Select a teacher and provide your evaluation.</p>
    </div>

    <?php if (!$selected_teacher): ?>
        <!-- Selection view: teachers list + supervisor info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 md:gap-6">
            <div class="bg-white rounded-lg shadow-md p-4 md:p-6 max-h-80 md:max-h-96 lg:max-h-[32rem] flex flex-col">
                <h2 class="text-lg font-semibold text-gray-900 mb-5 flex-shrink-0">Select Teacher to Evaluate</h2>
                <div class="flex-1 overflow-y-auto">
                    <?php if (empty($teachers)): ?>
                        <div class="text-center py-8 md:py-8 text-gray-500">
                            <p class="text-sm md:text-base">No teachers found. Contact administrator.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($teachers as $t): ?>
                                <?php $isEvaluated = in_array((int)$t['id'], $evaluatedTeachers, true); ?>
                                <div class="border border-gray-200 rounded-lg p-4 md:p-4 <?php echo $isEvaluated ? '' : 'hover:bg-gray-50'; ?> transition-colors">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                                        <div>
                                            <h3 class="font-medium text-gray-900 text-sm md:text-base"><?php echo htmlspecialchars($t['firstname'] . ' ' . $t['lastname']); ?></h3>
                                            <p class="text-sm md:text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($t['department'] ?? ''); ?></p>
                                        </div>
                                        <?php if ($isEvaluated): ?>
                                            <div class="w-full sm:w-auto text-center px-4 py-3 bg-gray-600 text-white font-semibold rounded-md text-sm cursor-not-allowed">Evaluated</div>
                                        <?php else: ?>
                                            <a href="?module=evaluation&teacher_id=<?php echo $t['id']; ?>" class="w-full sm:w-auto text-center px-4 py-3 bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold rounded-md transition-colors duration-200 shadow-sm text-sm">Evaluate</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-gray-100 rounded-lg p-4 md:p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800 mb-5">Supervisor Information</h2>
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 md:w-12 h-12 md:h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg"><?php echo strtoupper(substr($_SESSION['firstname'] ?? 'S', 0, 1)); ?></span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-base"><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . ($_SESSION['lastname'] ?? '')); ?></h3>
                            <p class="text-sm md:text-sm text-gray-600">Supervisor</p>
                        </div>
                    </div>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded">
                        <p class="text-sm md:text-sm text-yellow-700"><strong>Instructions:</strong> Select a teacher to evaluate and complete the questionnaire.</p>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <?php if ($alreadyEvaluated): ?>
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-6 md:p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Evaluation Already Completed</h1>
                    <p class="text-gray-600">You have already evaluated this teacher for the current school year.</p>
                    <div class="mt-6">
                        <a href="?module=evaluation" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">Back to Teachers</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Match student layout: sidebar + evaluation form -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-1 md:gap-6 px-1 md:px-0">
                <!-- Teacher Info Sidebar -->
                <div class="xl:col-span-1 bg-gray-100 rounded-lg p-3 md:p-6 shadow-sm mb-2 xl:mb-0">
                    <h2 class="text-lg font-semibold text-gray-800 mb-5">Evaluation Details</h2>
                    <div class="space-y-4">
                        <div class="bg-white rounded-lg p-4 md:p-4">
                            <h3 class="font-medium text-gray-900 mb-3">Teacher</h3>
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($selected_teacher['firstname'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm md:text-base"><?php echo htmlspecialchars($selected_teacher['firstname'] . ' ' . $selected_teacher['lastname']); ?></p>
                                    <p class="text-xs md:text-sm text-gray-600"><?php echo htmlspecialchars($selected_teacher['department'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 md:p-4">
                            <h3 class="font-medium text-gray-900 mb-3">Evaluator</h3>
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($_SESSION['firstname'] ?? '', 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm md:text-base"><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . ($_SESSION['lastname'] ?? '')); ?></p>
                                    <p class="text-xs md:text-sm text-gray-600">Supervisor</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 md:mt-6">
                        <a href="?module=evaluation" class="w-full flex items-center justify-center space-x-2 px-4 py-3 md:px-4 md:py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition-colors duration-200 text-sm">
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
                        <h2 class="text-lg md:text-xl font-semibold text-gray-900">Supervisor Evaluation Form</h2>
                        <div class="text-xs md:text-sm text-gray-500 bg-gray-100 p-3 rounded text-center">
                            <span class="block md:inline">Rating Scale:</span>
                            <span class="block md:inline md:ml-2">5=Excellent | 4=Good | 3=Fair | 2=Poor | 1=Very Poor</span>
                        </div>
                    </div>

                    <form action="../actions/SubmitSupervisorEvaluation.php" method="POST" class="space-y-5 md:space-y-6 w-full">
                        <input type="hidden" name="teacher_id" value="<?php echo $selected_teacher['id']; ?>">

                        <div class="max-h-[60vh] md:max-h-[70vh] overflow-y-auto no-scroll w-full">
                            <?php if (!empty($questionsData)): ?>
                                <?php $criteriaIndex = 1; ?>
                                <?php foreach ($questionsData as $criteriaName => $questions): ?>
                                    <div class="border border-gray-300 rounded-lg mb-5 md:mb-6 overflow-hidden w-full">
                                        <div class="bg-gray-800 text-white p-4 md:p-4">
                                            <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-2 md:space-y-0">
                                                <h3 class="font-medium text-sm md:text-base">Criteria 0<?php echo $criteriaIndex; ?>: <?php echo htmlspecialchars($criteriaName); ?></h3>
                                                <span class="text-xs bg-gray-600 px-2 py-1 rounded self-start md:self-auto"><?php echo count($questions); ?> questions</span>
                                            </div>
                                        </div>

                                        <div class="bg-white">
                                            <div class="hidden md:block">
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

                                                <?php foreach ($questions as $index => $question): ?>
                                                    <div class="grid grid-cols-12 items-center hover:bg-gray-50 border-b border-gray-200 last:border-b-0">
                                                        <div class="col-span-7 p-4 text-sm text-gray-700 border-r border-gray-300">
                                                            <div class="flex items-start"><span class="font-medium text-gray-900 mr-3 flex-shrink-0"><?php echo ($index + 1); ?>.</span><span class="leading-relaxed"><?php echo htmlspecialchars($question['question_text']); ?></span></div>
                                                        </div>
                                                        <?php for ($rating = 5; $rating >= 2; $rating--): ?>
                                                            <div class="col-span-1 p-2 text-center border-r border-gray-300 last:border-r-0">
                                                                <input type="radio" name="q_<?php echo $question['id']; ?>" value="<?php echo $rating; ?>" required class="w-4 h-4 text-yellow-500 focus:ring-yellow-500 focus:ring-2 cursor-pointer">
                                                            </div>
                                                        <?php endfor; ?>
                                                        <div class="col-span-1 p-2 text-center">
                                                            <input type="radio" name="q_<?php echo $question['id']; ?>" value="1" required class="w-4 h-4 text-yellow-500 focus:ring-yellow-500 focus:ring-2 cursor-pointer">
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="block md:hidden w-full">
                                                <?php foreach ($questions as $index => $question): ?>
                                                    <div class="mobile-question-spacing border-b border-gray-200 last:border-b-0 w-full mobile-question-container py-3">
                                                        <div class="mb-3 w-full px-1"><span class="font-medium text-gray-900 mr-2 text-sm"><?php echo ($index + 1); ?>.</span><span class="text-sm text-gray-700 leading-relaxed"><?php echo htmlspecialchars($question['question_text']); ?></span></div>
                                                        <div class="grid grid-cols-5 gap-0.5 mobile-rating-grid mt-2 w-full px-1">
                                                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                                                <label class="mobile-radio-label flex flex-col items-center p-1 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 min-h-[80px] justify-center w-full">
                                                                    <input type="radio" name="q_<?php echo $question['id']; ?>" value="<?php echo $rating; ?>" required class="mb-1 w-5 h-5 text-yellow-500 focus:ring-yellow-500 cursor-pointer">
                                                                    <span class="text-base font-medium text-gray-900 mb-1"><?php echo $rating; ?></span>
                                                                    <span class="text-xs text-gray-500 text-center leading-tight px-0.5"><?php echo $rating == 5 ? 'Excellent' : ($rating == 4 ? 'Good' : ($rating == 3 ? 'Fair' : ($rating == 2 ? 'Poor' : 'Very Poor'))); ?></span>
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

                                <div class="sticky bottom-0 bg-white border-t border-gray-200 pt-5">
                                    <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                                        <p class="text-sm md:text-sm text-gray-600 text-center md:text-left">Please ensure all questions are answered before submitting.</p>
                                        <button type="submit" class="w-full md:w-auto px-6 md:px-8 py-4 md:py-3 bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold rounded-md transition-colors duration-200 shadow-sm text-base">Submit Evaluation</button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 md:py-12 text-gray-500">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Questions Available</h3>
                                    <p class="text-sm md:text-base">The evaluation questionnaire is not yet available.</p>
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

    input[type="radio"] {
        transform: scale(1.3);
    }

    @media (max-width: 640px) {
        input[type="radio"] {
            transform: scale(1.5);
        }
    }

    @media (max-width: 768px) {
        .mobile-question-spacing {
            padding: 1.25rem;
        }

        .mobile-rating-grid {
            gap: 0.5rem;
        }
    }
</style>