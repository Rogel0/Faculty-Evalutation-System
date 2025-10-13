<div class="p-2">
    <div class="overflow-x-auto">
        <?php
        require_once __DIR__ . '/../../config/database.php';

        // School year selection: use ?school_year_id= on the URL or fall back to active school year
        $requestedSY = isset($_GET['school_year_id']) && $_GET['school_year_id'] !== '' ? (int)$_GET['school_year_id'] : null;
        $currentSchoolYear = null;
        $schoolYearLabel = 'All Time';

        if ($requestedSY) {
            // Validate it exists
            $sStmt = $conn->prepare("SELECT id, year, semester FROM school_years WHERE id = ? LIMIT 1");
            $sStmt->bind_param('i', $requestedSY);
            $sStmt->execute();
            $sRes = $sStmt->get_result();
            if ($sRes && $row = $sRes->fetch_assoc()) {
                $currentSchoolYear = (int)$row['id'];
                $schoolYearLabel = $row['year'] . ' ' . ($row['semester'] ?? '');
            }
        } else {
            $syRes = $conn->query("SELECT id, year, semester FROM school_years WHERE is_active = 1 LIMIT 1");
            if ($syRes && $row = $syRes->fetch_assoc()) {
                $currentSchoolYear = (int)$row['id'];
                $schoolYearLabel = $row['year'] . ' ' . ($row['semester'] ?? '');
            }
        }

        // Load all school years for selector
        $syList = [];
        $allSyRes = $conn->query("SELECT id, year, semester FROM school_years ORDER BY year DESC, id DESC");
        if ($allSyRes) {
            while ($r = $allSyRes->fetch_assoc()) $syList[] = $r;
        }

        $teacherId = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;

        // Overall summary: total respondents (distinct students) and overall average
        $summarySql = "SELECT COUNT(DISTINCT e.evaluator_id) AS respondents, ROUND(AVG(CAST(e.answer AS DECIMAL)),2) AS overall_avg FROM evaluations e WHERE e.teacher_id = ? AND e.evaluator_type = 'student'";
        if ($currentSchoolYear) {
            $summarySql .= " AND e.school_year_id = ?";
            $summaryStmt = $conn->prepare($summarySql);
            $summaryStmt->bind_param('ii', $teacherId, $currentSchoolYear);
        } else {
            $summaryStmt = $conn->prepare($summarySql);
            $summaryStmt->bind_param('i', $teacherId);
        }
        $summaryStmt->execute();
        $summaryRes = $summaryStmt->get_result();
        $summary = $summaryRes->fetch_assoc();
        $respondents = $summary['respondents'] ?? 0;
        $overall_avg = $summary['overall_avg'] ?? 0;

        // Per-question averages (questionnaire entries)
        // Only include questions that exist in `questionnaires`. This hides any
        // evaluations that reference missing questionnaire IDs.
        $questionsQryStr = "
SELECT 
  q.id AS qid,
  q.question_text AS question_text,
  ROUND(AVG(e.answer),2) AS avg_rating,
  COUNT(DISTINCT e.evaluator_id) AS respondents,
  COUNT(e.id) AS total_answers
FROM evaluations e
JOIN questionnaires q ON e.questionnaire_id = q.id
WHERE e.teacher_id = ? AND e.evaluator_type = 'student'";
        if ($currentSchoolYear) $questionsQryStr .= " AND e.school_year_id = ?";
        $questionsQryStr .= " GROUP BY q.id ORDER BY q.id";

        $questionsStmt = $conn->prepare($questionsQryStr);
        if ($currentSchoolYear) {
            $questionsStmt->bind_param('ii', $teacherId, $currentSchoolYear);
        } else {
            $questionsStmt->bind_param('i', $teacherId);
        }
        $questionsStmt->execute();
        $qRes = $questionsStmt->get_result();
        $questions = [];
        while ($r = $qRes->fetch_assoc()) $questions[] = $r;
        ?>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800 mb-1">📊 My Evaluations</h1>
                <p class="text-sm text-gray-600">Student-submitted evaluation summary</p>
            </div>
            <div></div>
        </div>



        <!-- Per-school-year results table -->
        <?php
        // Compute metrics per school year for this teacher (student evaluators)
        $perYearStmt = $conn->prepare("SELECT COUNT(DISTINCT e.evaluator_id) AS respondents, ROUND(AVG(e.answer),2) AS overall_avg FROM evaluations e WHERE e.teacher_id = ? AND e.evaluator_type = 'student' AND e.school_year_id = ?");
        ?>

        <div class="bg-white rounded-lg shadow-sm p-4 mb-4 max-h-[760px] overflow-y-auto">
            <h2 class="text-lg font-semibold mb-3">Evaluation Results</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">School Year</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Respondents</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Overall Average</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- All Time row -->
                        <tr>
                            <td class="px-4 py-3">All Time</td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3"><?php echo (int)$respondents; ?></td>
                            <td class="px-4 py-3"><?php echo number_format((float)$overall_avg, 2); ?> / 5</td>
                            <td class="px-4 py-3">
                                <button data-sy="" class="view-pdf inline-flex items-center px-3 py-1 bg-gray-800 text-white rounded text-sm">PDF</button>
                            </td>
                        </tr>
                        <?php foreach ($syList as $syRow):
                            // fetch metrics for this school year
                            $syId = (int)$syRow['id'];
                            $perYearStmt->bind_param('ii', $teacherId, $syId);
                            $perYearStmt->execute();
                            $perYearRes = $perYearStmt->get_result();
                            $perMetrics = $perYearRes->fetch_assoc();
                            $pRespondents = $perMetrics['respondents'] ?? 0;
                            $pAvg = $perMetrics['overall_avg'] ?? 0;
                        ?>
                            <tr>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($syRow['year']); ?></td>
                                <td class="px-4 py-3"><?php
                                                        $sem = $syRow['semester'] ?? '';
                                                        if ($sem === '1' || $sem === 1) echo 'First Semester';
                                                        else if ($sem === '2' || $sem === 2) echo 'Second Semester';
                                                        else echo htmlspecialchars($sem);
                                                        ?></td>
                                <td class="px-4 py-3"><?php echo (int)$pRespondents; ?></td>
                                <td class="px-4 py-3"><?php echo number_format((float)$pAvg, 2); ?> / 5</td>
                                <td class="px-4 py-3">
                                    <button data-sy="<?php echo $syId; ?>" class="view-pdf inline-flex items-center px-3 py-1 bg-gray-800 text-white rounded text-sm">PDF</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Per-question breakdown -->
        <!-- Evaluation Details table removed. PDF buttons remain. -->
        <script>
            // Delegate handlers for per-row PDF buttons in the table
            document.addEventListener('click', function(e) {
                var pdfBtn = e.target.closest('.view-pdf');
                if (pdfBtn) {
                    var sy2 = pdfBtn.getAttribute('data-sy');
                    // Build absolute URL using the project folder to avoid resolving to server root /src
                    var url2 = window.location.origin + '/faculty_evaluation/src/actions/GenerateTeacherReportPdf.php';
                    if (sy2) url2 += '?school_year_id=' + encodeURIComponent(sy2);
                    window.open(url2, '_blank');
                    return;
                }
            });
        </script>
    </div>
</div>