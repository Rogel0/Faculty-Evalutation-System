<?php
include_once('../config/database.php');
// Toastify for session messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['eval_message'])) {
    $msg = $_SESSION['eval_message'];
    echo '<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>';
    echo '<script>window.addEventListener("DOMContentLoaded",function(){
        Toastify({
            text: "' . addslashes($msg['text']) . '",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "' . ($msg['type'] === 'success' ? '#22c55e' : '#ef4444') . '",
            close: true
        }).showToast();
    });</script>';
    unset($_SESSION['eval_message']);
}

date_default_timezone_set('Asia/Manila'); // Set your timezone
$now = date('Y-m-d H:i:s');
// Automatically update period active status
$conn->query("UPDATE faculty_evaluation_periods SET active = 0 WHERE end_datetime < '$now' AND active = 1");
$conn->query("UPDATE faculty_evaluation_periods SET active = 1 WHERE start_datetime <= '$now' AND end_datetime >= '$now'");

// Check for active student evaluation period
$active_sy = $conn->query("SELECT id, semester FROM school_years WHERE is_active = 1 LIMIT 1");
$active_sy = $active_sy ? $active_sy->fetch_assoc() : null;
$student_period_active = false;
if ($active_sy) {
    $periodRes = $conn->prepare("SELECT id FROM faculty_evaluation_periods WHERE school_year_id = ? AND semester = ? AND evaluation_type = 'student' AND active = 1 LIMIT 1");
    $periodRes->bind_param('is', $active_sy['id'], $active_sy['semester']);
    $periodRes->execute();
    $periodResult = $periodRes->get_result();
    $student_period_active = $periodResult->num_rows > 0;
    $periodRes->close();
}
?>

<!-- Evaluation Trigger UI -->
<div class="flex flex-col items-center justify-center h-full ">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Start Evaluation</h2>
    <div class="flex flex-col md:flex-row gap-10 w-full w-full h-[60vh]">
        <!-- Student Evaluation Card -->
        <form method="POST" action="../actions/StartEvaluation.php" class="flex-2 bg-white rounded-xl shadow-lg p-6 flex flex-col items-center">
            <h3 class="text-lg font-semibold mb-4 text-blue-700">Student Evaluation</h3>
            <div class="flex flex-col w-full">
                <label class="block mb-2 text-sm font-medium text-gray-700">Start Time</label>
                <input name="start_time" type="datetime-local" class="mb-4 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 w-full" required <?php if ($student_period_active) echo 'disabled'; ?> />
                <label class="block mt-12 mb-2 text-sm font-medium text-gray-700">End Time</label>
                <input name="end_time" type="datetime-local" class="mb-6 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 w-full" required <?php if ($student_period_active) echo 'disabled'; ?> />
                <input type="hidden" name="evaluation_type" value="student" />
                <button type="submit"
                    class="px-8 mt-12 py-3 rounded-lg shadow-md transition-all text-lg font-semibold flex items-center gap-2 w-full justify-center
                    <?php echo $student_period_active ? 'bg-gray-400 text-gray-200 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700'; ?>"
                    <?php if ($student_period_active) echo 'disabled'; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.75a12.083 12.083 0 01-6.16-11.172L12 14z" />
                    </svg>
                    <?php echo $student_period_active ? 'Ongoing Evaluation' : 'Start Student Evaluation'; ?>
                </button>
            </div>
        </form>
        <!-- Peer to Peer Evaluation Card -->
        <form method="POST" action="../actions/StartEvaluation.php" class="flex-2 bg-white rounded-xl shadow-lg p-6 flex flex-col items-center">
            <h3 class="text-lg font-semibold mb-4 text-green-700">Peer to Peer Evaluation</h3>
            <label class="block mb-2 text-sm font-medium text-gray-700">Start Time</label>
            <input name="start_time" type="datetime-local" class="mb-4 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 w-full" required />
            <label class="block mb-2 mt-12 text-sm font-medium text-gray-700">End Time</label>
            <input name="end_time" type="datetime-local" class="mb-6 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 w-full" required />
            <input type="hidden" name="evaluation_type" value="peer" />
            <button type="submit" class="px-8 py-3 mt-12 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition-all text-lg font-semibold flex items-center gap-2 w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"></button>
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1" /></svg>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20H4v-2a4 4 0 014-4h1" />
            <circle cx="9" cy="7" r="4" />
            <circle cx="17" cy="7" r="4" />
            </svg>
            Start Peer Evaluation
            </button>
        </form>
    </div>
</div>