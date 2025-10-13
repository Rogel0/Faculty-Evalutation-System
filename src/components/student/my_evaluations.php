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

// Get all evaluations by the student grouped by school year and semester
$evaluationsData = [];
$totalEvaluations = 0;

// Get selected evaluation details if requested
$selectedEvaluation = null;
$selectedEvaluationDetails = [];
if (isset($_GET['eval_id']) && isset($_GET['teacher_id']) && isset($_GET['subject_id'])) {
    $eval_teacher_id = (int)$_GET['teacher_id'];
    $eval_subject_id = (int)$_GET['subject_id'];
    $eval_school_year_id = isset($_GET['school_year_id']) ? (int)$_GET['school_year_id'] : null;

    // Get detailed evaluation data
    $detailQuery = "
    SELECT 
        e.id, e.created_at, e.answer,
        q.question_text,
        c.name as criteria_name,
        u.firstname as teacher_firstname,
        u.lastname as teacher_lastname,
        s.subject_name, s.subject_code,
        sy.year as school_year, sy.semester,
        sy.start_date, sy.end_date
    FROM evaluations e
    LEFT JOIN questionnaires q ON e.questionnaire_id = q.id
    LEFT JOIN criteria c ON q.criteria_id = c.id
    LEFT JOIN users u ON e.teacher_id = u.id
    LEFT JOIN subjects s ON e.subject_id = s.id
    LEFT JOIN school_years sy ON e.school_year_id = sy.id
    WHERE e.evaluator_id = ? AND e.teacher_id = ? AND e.subject_id = ?
    " . ($eval_school_year_id ? "AND e.school_year_id = ?" : "AND e.school_year_id IS NULL") . "
    AND e.evaluator_type = 'student'
    ORDER BY c.name, q.id";

    $detailStmt = $conn->prepare($detailQuery);
    if ($eval_school_year_id) {
        $detailStmt->bind_param('iiii', $student_id, $eval_teacher_id, $eval_subject_id, $eval_school_year_id);
    } else {
        $detailStmt->bind_param('iii', $student_id, $eval_teacher_id, $eval_subject_id);
    }
    $detailStmt->execute();
    $detailResult = $detailStmt->get_result();

    while ($row = $detailResult->fetch_assoc()) {
        if (!$selectedEvaluation) {
            $selectedEvaluation = [
                'teacher_name' => $row['teacher_firstname'] . ' ' . $row['teacher_lastname'],
                'subject_name' => $row['subject_name'],
                'subject_code' => $row['subject_code'],
                'school_year' => $row['school_year'],
                'semester' => $row['semester'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'created_at' => $row['created_at']
            ];
        }

        if (!isset($selectedEvaluationDetails[$row['criteria_name']])) {
            $selectedEvaluationDetails[$row['criteria_name']] = [];
        }
        $selectedEvaluationDetails[$row['criteria_name']][] = [
            'question' => $row['question_text'],
            'answer' => $row['answer']
        ];
    }
    $detailStmt->close();
}

// Modified query to get evaluations with school year and semester information
$evaluationsQuery = "
SELECT 
    e.id as evaluation_id,
    e.created_at,
    e.teacher_id,
    e.subject_id,
    e.school_year_id,
    u.firstname as teacher_firstname,
    u.lastname as teacher_lastname,
    s.subject_name,
    s.subject_code,
    COALESCE(sy.year, 'Unknown Year') as school_year,
    COALESCE(sy.semester, 'Unknown Semester') as semester,
    sy.start_date,
    sy.end_date,
    COUNT(DISTINCT e2.questionnaire_id) as total_questions,
    AVG(CAST(e2.answer AS DECIMAL(3,2))) as average_rating
FROM evaluations e
LEFT JOIN users u ON e.teacher_id = u.id
LEFT JOIN subjects s ON e.subject_id = s.id
LEFT JOIN school_years sy ON e.school_year_id = sy.id
LEFT JOIN evaluations e2 ON e.evaluator_id = e2.evaluator_id 
    AND e.teacher_id = e2.teacher_id 
    AND e.subject_id = e2.subject_id 
    AND COALESCE(e.school_year_id, 0) = COALESCE(e2.school_year_id, 0)
WHERE e.evaluator_id = ? AND e.evaluator_type = 'student'
GROUP BY e.evaluator_id, e.teacher_id, e.subject_id, COALESCE(e.school_year_id, 0)
ORDER BY sy.start_date DESC, sy.semester DESC, e.created_at DESC";

$evaluationsStmt = $conn->prepare($evaluationsQuery);
$evaluationsStmt->bind_param('i', $student_id);
$evaluationsStmt->execute();
$evaluationsResult = $evaluationsStmt->get_result();

while ($row = $evaluationsResult->fetch_assoc()) {
    $schoolYear = $row['school_year'] ?? 'Unknown Year';
    $semester = $row['semester'] ?? 'Unknown Semester';

    // Create a more structured key to ensure proper separation
    $periodKey = $schoolYear . ' - ' . $semester;

    // Sort by school year first, then semester
    $sortKey = str_pad($schoolYear, 10, '0', STR_PAD_LEFT) . '_' . $semester;

    if (!isset($evaluationsData[$periodKey])) {
        $evaluationsData[$periodKey] = [
            'year' => $schoolYear,
            'semester' => $semester,
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'sort_key' => $sortKey,
            'evaluations' => []
        ];
    }

    $evaluationsData[$periodKey]['evaluations'][] = $row;
    $totalEvaluations++;
}
$evaluationsStmt->close();

// Sort periods by school year and semester (most recent first)
uasort($evaluationsData, function ($a, $b) {
    return strcmp($b['sort_key'], $a['sort_key']);
});

// Get summary statistics
$statsQuery = "
SELECT 
    COUNT(DISTINCT CONCAT(COALESCE(teacher_id, 0), '-', COALESCE(subject_id, 0), '-', COALESCE(school_year_id, 0))) as total_evaluations,
    COUNT(DISTINCT teacher_id) as total_teachers,
    COUNT(DISTINCT subject_id) as total_subjects,
    COALESCE(AVG(CAST(answer AS DECIMAL(3,2))), 0) as overall_average
FROM evaluations 
WHERE evaluator_id = ? AND evaluator_type = 'student'";

$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param('i', $student_id);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();
$statsStmt->close();
?>

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

    .rating-stars {
        display: flex;
        gap: 2px;
        align-items: center;
    }

    .star {
        width: 16px;
        height: 16px;
        fill: #e5e7eb;
        flex-shrink: 0;
    }

    .star.filled {
        fill: #fbbf24;
    }

    .evaluation-card {
        transition: all 0.2s ease;
        height: auto;
        min-height: 200px;
        display: flex;
        flex-direction: column;
    }

    .evaluation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
    }

    .card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Ensure proper visibility for all elements */
    .period-header {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        position: relative;
        z-index: 1;
    }

    .evaluation-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }

    /* Mobile responsive fixes */
    @media (max-width: 768px) {
        .mobile-card-padding {
            padding: 16px;
        }

        .evaluation-grid {
            grid-template-columns: 1fr;
        }

        .evaluation-card {
            min-height: 180px;
        }

        .rating-stars .star {
            width: 14px;
            height: 14px;
        }
    }

    @media (max-width: 640px) {
        .evaluation-card {
            margin-bottom: 1rem;
        }
    }

    /* Ensure text is visible */
    .text-visible {
        color: #374151 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .bg-visible {
        background-color: white !important;
        opacity: 1 !important;
    }
</style>

<div class="w-full max-h-[92vh] p-0">
    <!-- Header -->
    <div class="mb-4 md:mb-6 px-2 md:px-0">
        <?php if ($selectedEvaluation): ?>
            <!-- Breadcrumb Navigation -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="?module=my-evaluations" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            My Evaluations
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                <?php echo htmlspecialchars($selectedEvaluation['school_year']) . ' - ' . htmlspecialchars($selectedEvaluation['semester']); ?>
                            </span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                <?php echo htmlspecialchars($selectedEvaluation['subject_name']); ?>
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
        <?php endif; ?>

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">
                    <?php if ($selectedEvaluation): ?>
                        Evaluation Details
                    <?php else: ?>
                        My Evaluations
                    <?php endif; ?>
                </h1>
                <p class="text-gray-600 mt-1 text-sm md:text-base">
                    <?php if ($selectedEvaluation): ?>
                        Detailed breakdown of your evaluation responses
                    <?php else: ?>
                        View your complete evaluation history organized by academic period
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($selectedEvaluation): ?>
                <a href="?module=my-evaluations" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Overview
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($selectedEvaluation): ?>
        <!-- Detailed Evaluation View -->
        <div class="px-2 md:px-0">
            <!-- Evaluation Header -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Evaluation Details</h2>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold"><?php echo strtoupper(substr($selectedEvaluation['teacher_name'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($selectedEvaluation['teacher_name']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($selectedEvaluation['subject_name']); ?></p>
                                </div>
                            </div>
                            <?php if ($selectedEvaluation['subject_code']): ?>
                                <div>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($selectedEvaluation['subject_code']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Academic Period</h3>
                        <div class="space-y-2">
                            <p class="text-gray-700">
                                <span class="font-medium">School Year:</span>
                                <?php echo htmlspecialchars($selectedEvaluation['school_year'] ?? 'N/A'); ?>
                            </p>
                            <p class="text-gray-700">
                                <span class="font-medium">Semester:</span>
                                <?php echo htmlspecialchars($selectedEvaluation['semester'] ?? 'N/A'); ?>
                            </p>
                            <?php if ($selectedEvaluation['start_date'] && $selectedEvaluation['end_date']): ?>
                                <p class="text-gray-700">
                                    <span class="font-medium">Period:</span>
                                    <?php echo date('M Y', strtotime($selectedEvaluation['start_date'])); ?> - <?php echo date('M Y', strtotime($selectedEvaluation['end_date'])); ?>
                                </p>
                            <?php endif; ?>
                            <p class="text-gray-700">
                                <span class="font-medium">Completed:</span>
                                <?php echo date('F j, Y \a\t g:i A', strtotime($selectedEvaluation['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Responses -->
            <div class="space-y-6">
                <?php if (!empty($selectedEvaluationDetails)): ?>
                    <?php $criteriaIndex = 1; ?>
                    <?php foreach ($selectedEvaluationDetails as $criteriaName => $questions): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gray-800 text-white p-4">
                                <h3 class="text-lg font-semibold">
                                    Criteria <?php echo $criteriaIndex; ?>: <?php echo htmlspecialchars($criteriaName); ?>
                                </h3>
                                <p class="text-gray-300 text-sm"><?php echo count($questions); ?> questions</p>
                            </div>
                            <div class="p-4">
                                <div class="space-y-4">
                                    <?php foreach ($questions as $index => $question): ?>
                                        <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                            <div class="flex justify-between items-start mb-2">
                                                <p class="text-gray-800 text-sm font-medium flex-1 mr-4">
                                                    <?php echo ($index + 1); ?>. <?php echo htmlspecialchars($question['question']); ?>
                                                </p>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-lg font-bold text-blue-600">
                                                        <?php echo $question['answer']; ?>
                                                    </span>
                                                    <span class="text-sm text-gray-500">/5</span>
                                                </div>
                                            </div>
                                            <div class="rating-stars">
                                                <?php
                                                $rating = (int)$question['answer'];
                                                for ($i = 1; $i <= 5; $i++):
                                                ?>
                                                    <svg class="star <?php echo $i <= $rating ? 'filled' : ''; ?>" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php $criteriaIndex++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white rounded-lg p-8 text-center">
                        <p class="text-gray-500">No detailed evaluation data found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>


        <?php if (empty($evaluationsData)): ?>
            <!-- No Evaluations State -->
            <div class="text-center py-12 bg-white rounded-lg shadow-md mx-2 md:mx-0">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Evaluations Yet</h3>
                <p class="text-gray-600 mb-6">You haven't completed any evaluations yet.</p>
                <a href="?module=evaluate_teacher" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Start Evaluating
                </a>
            </div>
        <?php else: ?>
            <!-- Evaluations History -->
            <div class="space-y-6 px-2 md:px-0">
                <?php foreach ($evaluationsData as $periodKey => $periodData): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="period-header text-white p-4 md:p-6">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                                <div>
                                    <h2 class="text-lg md:text-xl font-bold text-white">
                                        School Year <?php echo htmlspecialchars($periodData['year']); ?>
                                    </h2>
                                    <p class="text-gray-200 text-sm md:text-base">
                                        <?php echo htmlspecialchars($periodData['semester']); ?> Semester
                                        <?php if ($periodData['start_date'] && $periodData['end_date']): ?>
                                            • <?php echo date('M Y', strtotime($periodData['start_date'])); ?> - <?php echo date('M Y', strtotime($periodData['end_date'])); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Evaluations Table -->
                        <div class="p-4 md:p-6 bg-gray-50">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Avg Rating</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Questions</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($periodData['evaluations'] as $evaluation): ?>
                                            <tr>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($evaluation['teacher_firstname'] . ' ' . $evaluation['teacher_lastname']); ?></div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($evaluation['subject_name']); ?><?php if ($evaluation['subject_code']): ?> <span class="ml-2 inline-block text-xs text-gray-400">(<?php echo htmlspecialchars($evaluation['subject_code']); ?>)</span><?php endif; ?></td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900"><?php echo number_format($evaluation['average_rating'] ?? 0, 2); ?>/5</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600"><?php echo (int)$evaluation['total_questions']; ?></td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600"><?php echo date('M j, Y', strtotime($evaluation['created_at'])); ?></td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                    <?php
                                                    $school_year_param = !empty($evaluation['school_year_id'])
                                                        ? '&school_year_id=' . (int)$evaluation['school_year_id']
                                                        : '';
                                                    $pdfUrl = '/src/actions/GenerateStudentEvaluationPdf.php?teacher_id=' . (int)$evaluation['teacher_id'] . '&subject_id=' . (int)$evaluation['subject_id'] . $school_year_param;
                                                    $viewUrl = '?module=my-evaluations&eval_id=' . (int)$evaluation['evaluation_id'] . '&teacher_id=' . (int)$evaluation['teacher_id'] . '&subject_id=' . (int)$evaluation['subject_id'] . $school_year_param;
                                                    ?>
                                                    <a href="<?php echo $viewUrl; ?>" class="text-blue-600 hover:text-blue-800 mr-3">View</a>
                                                    <a href="#" onclick="window.open('<?php echo $pdfUrl; ?>','_blank'); return false;" class="text-gray-800 bg-gray-100 px-2 py-1 rounded text-sm">PDF</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 text-center px-2 md:px-0">
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Continue Evaluating</h3>
                    <p class="text-gray-600 mb-6">Help improve the quality of education by providing more feedback.</p>
                    <a href="?module=evaluate_teacher" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Evaluate More Teachers
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    // Ensure all elements are properly visible and add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure all elements are visible first
        const allCards = document.querySelectorAll('.evaluation-card');
        const allTexts = document.querySelectorAll('.text-visible');
        const allBackgrounds = document.querySelectorAll('.bg-visible');

        // Force visibility
        allCards.forEach(card => {
            card.style.visibility = 'visible';
            card.style.display = 'flex';
            card.style.opacity = '1';
        });

        allTexts.forEach(text => {
            text.style.color = '#374151';
            text.style.opacity = '1';
            text.style.visibility = 'visible';
        });

        allBackgrounds.forEach(bg => {
            bg.style.backgroundColor = 'white';
            bg.style.opacity = '1';
        });

        // Add fade in animation after ensuring visibility
        setTimeout(() => {
            allCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }, 100);

        // Ensure rating stars are properly displayed
        const stars = document.querySelectorAll('.star');
        stars.forEach(star => {
            star.style.display = 'block';
            star.style.visibility = 'visible';
        });
    });
</script>