<div class="p-2">
    <div class="overflow-x-auto">
        <?php
        require_once('../config/database.php');

        // Get evaluations received by this teacher (simplified query)
        $evaluations_query = "
            SELECT 
                pe.evaluation_date,
                pe.average_rating,
                u.firstname as evaluator_firstname,
                u.lastname as evaluator_lastname
            FROM peer_evaluations pe
            JOIN users u ON pe.evaluator_id = u.id
            WHERE pe.teacher_id = ?
            ORDER BY pe.evaluation_date DESC
        ";

        $stmt = $conn->prepare($evaluations_query);
        $stmt->bind_param('i', $_SESSION['userID']);
        $stmt->execute();
        $result = $stmt->get_result();
        $evaluations = [];
        while ($row = $result->fetch_assoc()) {
            $evaluations[] = $row;
        }
        ?>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <h1 class="text-xl font-bold text-gray-800 mb-1">📊 My Evaluations</h1>
            <p class="text-sm text-gray-600">View feedback from your peers</p>
        </div>

        <?php if (empty($evaluations)): ?>
            <!-- No evaluations -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                <div class="text-gray-400 mb-3">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">No Evaluations Yet</h3>
                <p class="text-sm text-gray-600">You haven't received any peer evaluations yet.</p>
            </div>
        <?php else: ?>
            <!-- Evaluations List -->
            <div class="space-y-3">
                <?php foreach ($evaluations as $evaluation): ?>
                    <div class="bg-white rounded-lg shadow-sm border p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">
                                    <?php echo strtoupper(substr($evaluation['evaluator_firstname'], 0, 1) . substr($evaluation['evaluator_lastname'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($evaluation['evaluator_firstname'] . ' ' . $evaluation['evaluator_lastname']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($evaluation['evaluation_date'])); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-2xl font-bold <?php
                                                                $rating = round($evaluation['average_rating'], 1);
                                                                echo $rating >= 4.0 ? 'text-green-600' : ($rating >= 3.0 ? 'text-blue-600' : ($rating >= 2.0 ? 'text-yellow-600' : 'text-red-600'));
                                                                ?>"><?php echo number_format($evaluation['average_rating'], 1); ?></div>
                                <div class="text-xs text-gray-500">out of 5.0</div>
                            </div>
                        </div>

                        <!-- Rating bar -->
                        <div class="mt-3">
                            <div class="bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all duration-300"
                                    style="width: <?php echo ($evaluation['average_rating'] / 5) * 100; ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>