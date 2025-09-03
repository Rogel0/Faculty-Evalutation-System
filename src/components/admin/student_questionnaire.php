<?php
// Get criteria for dropdown
$criteriaOptions = [];
$criteriaQuery = "SELECT id, name FROM criteria WHERE evaluator_type = 'student' ORDER BY name";
$criteriaResult = $conn->query($criteriaQuery);
if ($criteriaResult) {
    while ($row = $criteriaResult->fetch_assoc()) {
        $criteriaOptions[] = $row;
    }
}

// Get existing questions grouped by criteria
$questionsData = [];
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
?>

<!-- Include Toastify CSS and JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="../../scripts/toast.js"></script>

<div class="w-full h-auto p-4 overflow-y-hidden">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Manage Questionnaire</h1>
        <p class="text-gray-600 mt-1">Create and manage evaluation questions</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Question Form -->
        <div class="bg-gray-100 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Question Form</h2>

            <form action="../actions/AddQuestion.php" method="POST" class="space-y-6">
                <!-- Criteria Selection -->
                <div>
                    <label for="qn-criteria" class="block text-sm font-medium text-gray-700 mb-2">Criteria</label>
                    <div class="relative flex items-center">
                        <select name="qn-criteria" id="qn-criteria" required
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors appearance-none">
                            <option value="">Please select here</option>
                            <?php foreach ($criteriaOptions as $criteria): ?>
                                <option value="<?php echo $criteria['id']; ?>">
                                    <?php echo htmlspecialchars($criteria['name']); ?>
                                    <?php
                                    $questionCount = isset($questionsData[$criteria['name']]) ? count($questionsData[$criteria['name']]) : 0;
                                    echo " ({$questionCount} questions)";
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="document.getElementById('addCriteriaModal').style.display='block'" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs" title="Add Criteria">+
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Select existing criteria to add more questions to it</p>
                </div>


                <!-- Question Input -->
                <div>
                    <label for="qn-question" class="block text-sm font-medium text-gray-700 mb-2">Question</label>
                    <textarea name="qn-question" id="qn-question" rows="8" required
                        placeholder="Input here..."
                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors resize-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">This question will be added to the selected criteria</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submitQuestionBtn"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold py-3 px-6 rounded-md transition-colors duration-200 shadow-sm">
                    Add Question
                </button>
            </form>
        </div>

        <?php include('modal/add_criteria_student_modal.php') ?>

        <!-- Evaluation Questionnaire Preview -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6 h-[75vh]">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Evaluation Questionnaire</h2>

            <div class="space-y-6 max-h-[90%] overflow-y-auto no-scroll">
                <?php if (!empty($questionsData)): ?>
                    <?php $criteriaIndex = 1; ?>
                    <?php foreach ($questionsData as $criteriaName => $questions): ?>
                        <div class="border-b border-gray-200 pb-4">
                            <!-- Criteria Header -->
                            <div class="bg-gray-800 text-white p-3 rounded-t-lg">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-medium">
                                        Criteria 0<?php echo $criteriaIndex; ?>: <?php echo htmlspecialchars($criteriaName); ?>
                                    </h3>
                                    <span class="text-xs bg-gray-600 px-2 py-1 rounded">
                                        <?php echo count($questions); ?> questions
                                    </span>
                                </div>
                            </div>

                            <!-- Questions Table -->
                            <div class="border border-t-0 border-gray-300 rounded-b-lg ">
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

                                <!-- Questions -->
                                <?php foreach ($questions as $index => $question): ?>
                                    <div class="grid grid-cols-12 items-center hover:bg-gray-50 border-b border-gray-200 last:border-b-0">
                                        <div class="col-span-7 p-4 text-sm text-gray-700 border-r border-gray-300">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-start">
                                                    <span class="font-medium text-gray-900 mr-3 flex-shrink-0"><?php echo ($index + 1); ?>.</span>
                                                    <span class="leading-relaxed"><?php echo htmlspecialchars($question['question_text']); ?></span>
                                                </div>
                                                <form action="../actions/DeleteQuestion.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                    <button type="submit" class="ml-4 p-2 bg-red-500 hover:bg-red-600 text-white rounded transition-colors duration-150 flex items-center" title="Delete">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m5 0H4" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                            <div class="col-span-1 p-2 text-center border-r border-gray-300 last:border-r-0">
                                                <input type="radio" name="q_<?php echo $question['id']; ?>" value="<?php echo $rating; ?>"
                                                    class="w-3.5 h-3.5 text-yellow-500 focus:ring-yellow-500 focus:ring-2">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $criteriaIndex++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>No questions found. Start by adding your first question.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>