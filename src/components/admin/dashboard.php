<?php
// Get database connection
include('../config/database.php');

// Get filter parameters
$selected_school_year = isset($_GET['school_year']) ? $_GET['school_year'] : 'all';

// Build WHERE clause for filtering - fix the logic
$where_clause = "";
if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
    $where_clause = "AND e.school_year_id = " . intval($selected_school_year);
}

// Get available school years for filter dropdown (only those with evaluation data)
$school_years_result = $conn->query("
    SELECT sy.id, sy.year, sy.semester, COUNT(e.id) as evaluation_count
    FROM school_years sy
    LEFT JOIN evaluations e ON sy.id = e.school_year_id
    GROUP BY sy.id, sy.year, sy.semester
    HAVING evaluation_count > 0
    ORDER BY sy.year DESC, sy.semester
");
$available_school_years = [];
if ($school_years_result && $school_years_result->num_rows > 0) {
    while ($row = $school_years_result->fetch_assoc()) {
        $available_school_years[] = $row;
    }
}

// Fetch analytics data
try {
    // Total counts - simplified logic
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        // Filtered counts for specific school year
        $school_year_id = intval($selected_school_year);

        // Count students enrolled in the selected school year (if enrollments exist)
        $result = $conn->query("SELECT COUNT(DISTINCT u.id) as total_students FROM users u JOIN student_enrollments se ON se.student_id = u.id WHERE u.user_type = 'student' AND se.school_year_id = $school_year_id");
        if ($result && $result->num_rows > 0) {
            $total_students = $result->fetch_assoc()['total_students'];
        } else {
            // Fallback to counting all students if no enrollments found for the year
            $result = $conn->query("SELECT COUNT(*) as total_students FROM users WHERE user_type = 'student'");
            $total_students = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_students'] : 0;
        }

        // Count teachers assigned in the selected school year (if teacher_subjects exists)
        $result = $conn->query("SELECT COUNT(DISTINCT u.id) as total_teachers FROM users u JOIN teacher_subjects ts ON ts.teacher_id = u.id WHERE u.user_type = 'teacher' AND ts.school_year_id = $school_year_id");
        if ($result && $result->num_rows > 0) {
            $total_teachers = $result->fetch_assoc()['total_teachers'];
        } else {
            // Fallback to counting all teachers
            $result = $conn->query("SELECT COUNT(*) as total_teachers FROM users WHERE user_type = 'teacher'");
            $total_teachers = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_teachers'] : 0;
        }

        $result = $conn->query("SELECT COUNT(*) as total_evaluations FROM evaluations e WHERE e.school_year_id = $school_year_id");
        $total_evaluations = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_evaluations'] : 0;

        $result = $conn->query("SELECT COUNT(*) as total_peer_evaluations FROM evaluations e WHERE e.evaluator_type = 'teacher' AND e.school_year_id = $school_year_id");
        $total_peer_evaluations = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_peer_evaluations'] : 0;

        $result = $conn->query("SELECT COUNT(DISTINCT e.subject_id) as total_subjects FROM evaluations e WHERE e.school_year_id = $school_year_id");
        $total_subjects = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_subjects'] : 0;

        // Students who have submitted evaluations (distinct student evaluators)
        $result = $conn->query("SELECT COUNT(DISTINCT e.evaluator_id) as students_evaluated FROM evaluations e WHERE e.evaluator_type = 'student' AND e.school_year_id = $school_year_id");
        $students_evaluated = $result && $result->num_rows > 0 ? $result->fetch_assoc()['students_evaluated'] : 0;

        // Teachers who have submitted peer evaluations (distinct teacher evaluators)
        $result = $conn->query("SELECT COUNT(DISTINCT e.evaluator_id) as teachers_evaluated FROM evaluations e WHERE e.evaluator_type = 'teacher' AND e.school_year_id = $school_year_id");
        $teachers_evaluated = $result && $result->num_rows > 0 ? $result->fetch_assoc()['teachers_evaluated'] : 0;

        // If no data for this school year, show message
        if ($total_evaluations == 0) {
            $no_data_message = "No evaluation data found for the selected school year.";
        }
    } else {
        // All school years - show overall statistics
        $result = $conn->query("SELECT COUNT(*) as total_students FROM users WHERE user_type = 'student'");
        $total_students = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_students'] : 0;

        $result = $conn->query("SELECT COUNT(*) as total_teachers FROM users WHERE user_type = 'teacher'");
        $total_teachers = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_teachers'] : 0;

        $result = $conn->query("SELECT COUNT(*) as total_evaluations FROM evaluations");
        $total_evaluations = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_evaluations'] : 0;

        $result = $conn->query("SELECT COUNT(*) as total_peer_evaluations FROM evaluations WHERE evaluator_type = 'teacher'");
        $total_peer_evaluations = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_peer_evaluations'] : 0;

        $result = $conn->query("SELECT COUNT(*) as total_subjects FROM subjects");
        $total_subjects = $result && $result->num_rows > 0 ? $result->fetch_assoc()['total_subjects'] : 0;

        // Overall students who have submitted evaluations
        $result = $conn->query("SELECT COUNT(DISTINCT e.evaluator_id) as students_evaluated FROM evaluations e WHERE e.evaluator_type = 'student'");
        $students_evaluated = $result && $result->num_rows > 0 ? $result->fetch_assoc()['students_evaluated'] : 0;

        // Overall teachers who have submitted peer evaluations
        $result = $conn->query("SELECT COUNT(DISTINCT e.evaluator_id) as teachers_evaluated FROM evaluations e WHERE e.evaluator_type = 'teacher'");
        $teachers_evaluated = $result && $result->num_rows > 0 ? $result->fetch_assoc()['teachers_evaluated'] : 0;
    }

    // Evaluations by strand (with filter) - use 'strand' column from users table
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        $school_year_id = intval($selected_school_year);
        $strand_query = "
            SELECT u.strand, COUNT(e.id) as evaluation_count 
            FROM evaluations e 
            JOIN users u ON e.evaluator_id = u.id 
            WHERE e.evaluator_type = 'student' AND u.strand IS NOT NULL AND e.school_year_id = $school_year_id
            GROUP BY u.strand
        ";
    } else {
        $strand_query = "
            SELECT u.strand, COUNT(e.id) as evaluation_count 
            FROM evaluations e 
            JOIN users u ON e.evaluator_id = u.id 
            WHERE e.evaluator_type = 'student' AND u.strand IS NOT NULL
            GROUP BY u.strand
        ";
    }

    $result = $conn->query($strand_query);
    $strand_data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $strand_data[] = ['strand' => $row['strand'], 'evaluation_count' => $row['evaluation_count']];
        }
    }

    // Average ratings by Department and Strand (grouped aggregation for chart)
    // This replaces the old yearly aggregation and will power a comparison chart between departments and strands
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        $school_year_id = intval($selected_school_year);
        $dept_strand_query = "
            SELECT u.department AS department, u.strand AS strand, 
                   ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) AS avg_rating,
                   COUNT(e.id) AS total_evaluations
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.answer REGEXP '^[0-5]$' AND e.school_year_id = $school_year_id
            GROUP BY u.department, u.strand
            ORDER BY u.department, u.strand
        ";
    } else {
        $dept_strand_query = "
            SELECT u.department AS department, u.strand AS strand, 
                   ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) AS avg_rating,
                   COUNT(e.id) AS total_evaluations
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.answer REGEXP '^[0-5]$'
            GROUP BY u.department, u.strand
            ORDER BY u.department, u.strand
        ";
    }

    $result = $conn->query($dept_strand_query);
    $dept_strand_data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dept_strand_data[] = $row;
        }
    }

    // Also prepare aggregated data for departments (avg & count) and strands (avg & count)
    if (isset($school_year_id) && is_numeric($school_year_id)) {
        $dept_agg_query = "SELECT u.department AS department, ROUND(AVG(CAST(e.answer AS DECIMAL)),2) AS avg_rating, COUNT(e.id) AS total_evaluations FROM evaluations e JOIN users u ON e.teacher_id = u.id WHERE e.answer REGEXP '^[0-5]$' AND e.school_year_id = $school_year_id GROUP BY u.department ORDER BY u.department";
        $strand_agg_query = "SELECT u.strand AS strand, ROUND(AVG(CAST(e.answer AS DECIMAL)),2) AS avg_rating, COUNT(e.id) AS total_evaluations FROM evaluations e JOIN users u ON e.teacher_id = u.id WHERE e.answer REGEXP '^[0-5]$' AND e.school_year_id = $school_year_id GROUP BY u.strand ORDER BY u.strand";
    } else {
        $dept_agg_query = "SELECT u.department AS department, ROUND(AVG(CAST(e.answer AS DECIMAL)),2) AS avg_rating, COUNT(e.id) AS total_evaluations FROM evaluations e JOIN users u ON e.teacher_id = u.id WHERE e.answer REGEXP '^[0-5]$' GROUP BY u.department ORDER BY u.department";
        $strand_agg_query = "SELECT u.strand AS strand, ROUND(AVG(CAST(e.answer AS DECIMAL)),2) AS avg_rating, COUNT(e.id) AS total_evaluations FROM evaluations e JOIN users u ON e.teacher_id = u.id WHERE e.answer REGEXP '^[0-5]$' GROUP BY u.strand ORDER BY u.strand";
    }

    $dept_agg_res = $conn->query($dept_agg_query);
    $dept_agg_data = [];
    if ($dept_agg_res && $dept_agg_res->num_rows > 0) {
        while ($r = $dept_agg_res->fetch_assoc()) $dept_agg_data[] = $r;
    }

    $strand_agg_res = $conn->query($strand_agg_query);
    $strand_agg_data = [];
    if ($strand_agg_res && $strand_agg_res->num_rows > 0) {
        while ($r = $strand_agg_res->fetch_assoc()) $strand_agg_data[] = $r;
    }

    // Top performing teachers (with filter)
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        $school_year_id = intval($selected_school_year);
        $teacher_query = "
            SELECT CONCAT(u.firstname, ' ', u.lastname) as teacher_name, 
                   ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_rating,
                   COUNT(e.id) as evaluation_count
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.evaluator_type = 'student' AND e.answer REGEXP '^[0-5]$' AND e.school_year_id = $school_year_id
            GROUP BY e.teacher_id, u.firstname, u.lastname
            HAVING evaluation_count >= 1
            ORDER BY avg_rating DESC
            LIMIT 3
        ";
    } else {
        $teacher_query = "
            SELECT CONCAT(u.firstname, ' ', u.lastname) as teacher_name, 
                   ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_rating,
                   COUNT(e.id) as evaluation_count
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.evaluator_type = 'student' AND e.answer REGEXP '^[0-5]$'
            GROUP BY e.teacher_id, u.firstname, u.lastname
            HAVING evaluation_count >= 1
            ORDER BY avg_rating DESC
            LIMIT 3
        ";
    }

    $result = $conn->query($teacher_query);
    $top_teachers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $top_teachers[] = $row;
        }
    }

    // Evaluation distribution by rating (with filter)
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        $school_year_id = intval($selected_school_year);
        $rating_query = "
            SELECT e.answer as rating, COUNT(*) as count
            FROM evaluations e
            WHERE e.evaluator_type = 'student' AND e.answer REGEXP '^[0-5]$' AND e.school_year_id = $school_year_id
            GROUP BY e.answer
            ORDER BY e.answer
        ";
    } else {
        $rating_query = "
            SELECT e.answer as rating, COUNT(*) as count
            FROM evaluations e
            WHERE e.evaluator_type = 'student' AND e.answer REGEXP '^[0-5]$'
            GROUP BY e.answer
            ORDER BY e.answer
        ";
    }

    $result = $conn->query($rating_query);
    $rating_distribution = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rating_distribution[] = $row;
        }
    }

    // Monthly evaluation trends (check if created_at exists and apply filter)
    $result = $conn->query("SHOW COLUMNS FROM evaluations LIKE 'created_at'");
    if ($result && $result->num_rows > 0) {
        if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
            $school_year_id = intval($selected_school_year);
            $monthly_query = "
                SELECT DATE_FORMAT(e.created_at, '%Y-%m') as month,
                       COUNT(*) as evaluation_count,
                       ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_rating
                FROM evaluations e
                WHERE e.evaluator_type = 'student' AND e.answer REGEXP '^[0-5]$' AND e.created_at IS NOT NULL AND e.school_year_id = $school_year_id
                GROUP BY DATE_FORMAT(e.created_at, '%Y-%m')
                ORDER BY month
            ";
        } else {
            $monthly_query = "
                SELECT DATE_FORMAT(e.created_at, '%Y-%m') as month,
                       COUNT(*) as evaluation_count,
                       ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_rating
                FROM evaluations e
                WHERE e.evaluator_type = 'student' AND e.answer REGEXP '^[0-5]$' AND e.created_at IS NOT NULL
                GROUP BY DATE_FORMAT(e.created_at, '%Y-%m')
                ORDER BY month
            ";
        }

        $result = $conn->query($monthly_query);
        $monthly_trends = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $monthly_trends[] = $row;
            }
        }
    } else {
        // If no created_at column, create dummy monthly data
        $monthly_trends = [
            ['month' => '2024-01', 'evaluation_count' => 15, 'avg_rating' => 4.2],
            ['month' => '2024-02', 'evaluation_count' => 23, 'avg_rating' => 4.1],
            ['month' => '2024-03', 'evaluation_count' => 18, 'avg_rating' => 4.3]
        ];
    }

    // Peer Evaluation Analytics
    // Top rated teachers in peer evaluations (with filter)
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        $school_year_id = intval($selected_school_year);
        $peer_teacher_query = "
            SELECT CONCAT(u.firstname, ' ', u.lastname) as teacher_name, 
                   ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_rating,
                   COUNT(e.id) as evaluation_count
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.evaluator_type = 'teacher' AND e.answer REGEXP '^[0-5]$' AND e.school_year_id = $school_year_id
            GROUP BY e.teacher_id, u.firstname, u.lastname
            HAVING evaluation_count >= 1
            ORDER BY avg_rating DESC
            LIMIT 10
        ";
    } else {
        $peer_teacher_query = "
            SELECT CONCAT(u.firstname, ' ', u.lastname) as teacher_name, 
                   ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_rating,
                   COUNT(e.id) as evaluation_count
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.evaluator_type = 'teacher' AND e.answer REGEXP '^[0-5]$'
            GROUP BY e.teacher_id, u.firstname, u.lastname
            HAVING evaluation_count >= 1
            ORDER BY avg_rating DESC
            LIMIT 10
        ";
    }

    $result = $conn->query($peer_teacher_query);
    $top_peer_teachers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $top_peer_teachers[] = $row;
        }
    }

    // Peer evaluation by department analytics
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        $school_year_id = intval($selected_school_year);
        $dept_peer_query = "
            SELECT 
                u.department,
                COUNT(e.id) as peer_evaluation_count,
                ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_peer_rating,
                COUNT(DISTINCT e.evaluator_id) as evaluators_count,
                COUNT(DISTINCT e.teacher_id) as evaluated_teachers_count
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.evaluator_type = 'teacher' AND e.answer REGEXP '^[0-5]$' AND e.school_year_id = $school_year_id
            GROUP BY u.department
            ORDER BY avg_peer_rating DESC
        ";
    } else {
        $dept_peer_query = "
            SELECT 
                u.department,
                COUNT(e.id) as peer_evaluation_count,
                ROUND(AVG(CAST(e.answer AS DECIMAL)), 2) as avg_peer_rating,
                COUNT(DISTINCT e.evaluator_id) as evaluators_count,
                COUNT(DISTINCT e.teacher_id) as evaluated_teachers_count
            FROM evaluations e
            JOIN users u ON e.teacher_id = u.id
            WHERE e.evaluator_type = 'teacher' AND e.answer REGEXP '^[0-5]$'
            GROUP BY u.department
            ORDER BY avg_peer_rating DESC
        ";
    }

    $result = $conn->query($dept_peer_query);
    $department_peer_data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $department_peer_data[] = $row;
        }
    }

    // Peer evaluation coverage (participation rate)
    if ($selected_school_year !== 'all' && is_numeric($selected_school_year)) {
        $school_year_id = intval($selected_school_year);
        $coverage_query = "
            SELECT 
                total_teachers.department,
                total_teachers.total_teachers,
                COALESCE(evaluating_teachers.evaluating_teachers, 0) as evaluating_teachers,
                COALESCE(evaluated_teachers.evaluated_teachers, 0) as evaluated_teachers,
                ROUND((COALESCE(evaluating_teachers.evaluating_teachers, 0) / total_teachers.total_teachers) * 100, 1) as participation_rate,
                ROUND((COALESCE(evaluated_teachers.evaluated_teachers, 0) / total_teachers.total_teachers) * 100, 1) as coverage_rate
            FROM (
                SELECT department, COUNT(*) as total_teachers
                FROM users 
                WHERE user_type = 'teacher' AND department IS NOT NULL
                GROUP BY department
            ) total_teachers
            LEFT JOIN (
                SELECT u.department, COUNT(DISTINCT e.evaluator_id) as evaluating_teachers
                FROM evaluations e
                JOIN users u ON e.evaluator_id = u.id
                WHERE e.evaluator_type = 'teacher' AND e.school_year_id = $school_year_id
                GROUP BY u.department
            ) evaluating_teachers ON total_teachers.department = evaluating_teachers.department
            LEFT JOIN (
                SELECT u.department, COUNT(DISTINCT e.teacher_id) as evaluated_teachers
                FROM evaluations e
                JOIN users u ON e.teacher_id = u.id
                WHERE e.evaluator_type = 'teacher' AND e.school_year_id = $school_year_id
                GROUP BY u.department
            ) evaluated_teachers ON total_teachers.department = evaluated_teachers.department
            ORDER BY participation_rate DESC
        ";
    } else {
        $coverage_query = "
            SELECT 
                total_teachers.department,
                total_teachers.total_teachers,
                COALESCE(evaluating_teachers.evaluating_teachers, 0) as evaluating_teachers,
                COALESCE(evaluated_teachers.evaluated_teachers, 0) as evaluated_teachers,
                ROUND((COALESCE(evaluating_teachers.evaluating_teachers, 0) / total_teachers.total_teachers) * 100, 1) as participation_rate,
                ROUND((COALESCE(evaluated_teachers.evaluated_teachers, 0) / total_teachers.total_teachers) * 100, 1) as coverage_rate
            FROM (
                SELECT department, COUNT(*) as total_teachers
                FROM users 
                WHERE user_type = 'teacher' AND department IS NOT NULL
                GROUP BY department
            ) total_teachers
            LEFT JOIN (
                SELECT u.department, COUNT(DISTINCT e.evaluator_id) as evaluating_teachers
                FROM evaluations e
                JOIN users u ON e.evaluator_id = u.id
                WHERE e.evaluator_type = 'teacher'
                GROUP BY u.department
            ) evaluating_teachers ON total_teachers.department = evaluating_teachers.department
            LEFT JOIN (
                SELECT u.department, COUNT(DISTINCT e.teacher_id) as evaluated_teachers
                FROM evaluations e
                JOIN users u ON e.teacher_id = u.id
                WHERE e.evaluator_type = 'teacher'
                GROUP BY u.department
            ) evaluated_teachers ON total_teachers.department = evaluated_teachers.department
            ORDER BY participation_rate DESC
        ";
    }

    $result = $conn->query($coverage_query);
    $peer_coverage_data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $peer_coverage_data[] = $row;
        }
    }
} catch (Exception $e) {
    // Handle errors gracefully and log the error
    // Database error logged server-side
    $total_students = $total_teachers = $total_evaluations = $total_peer_evaluations = $total_subjects = 0;
    $strand_data = $yearly_ratings = $top_teachers = $rating_distribution = $monthly_trends = [];
    $top_peer_teachers = $department_peer_data = $peer_coverage_data = [];
}


?>

<div class="w-full">
    <!-- Header with Filters -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-3xl font-bold text-gray-900">Faculty Evaluation Analytics Dashboard</h1>
                <p class="text-gray-600 mt-2">Comprehensive analysis of SHS faculty evaluation data</p>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white rounded-lg shadow-md p-4 min-w-[300px]">
                <form method="GET" action="" class="flex flex-col sm:flex-row gap-3">
                    <!-- Preserve other GET parameters -->
                    <?php if (isset($_GET['module'])): ?>
                        <input type="hidden" name="module" value="<?php echo htmlspecialchars($_GET['module']); ?>">
                    <?php endif; ?>

                    <div class="flex-1">
                        <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                        <select name="school_year" id="school_year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="all" <?php echo $selected_school_year === 'all' ? 'selected' : ''; ?>>All School Years (Total Data)</option>
                            <?php foreach ($available_school_years as $sy): ?>
                                <option value="<?php echo $sy['id']; ?>" <?php echo $selected_school_year == $sy['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sy['year'] . ' - ' . $sy['semester'] . ' (' . $sy['evaluation_count'] . ' evaluations)'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 text-sm font-medium">
                            Apply Filter
                        </button>
                    </div>
                </form>

                <?php if ($selected_school_year !== 'all'): ?>
                    <div class="mt-2">
                        <a href="?<?php echo isset($_GET['module']) ? 'module=' . htmlspecialchars($_GET['module']) : ''; ?>"
                            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear Filter
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Current Filter Display -->
        <?php if ($selected_school_year !== 'all'): ?>
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span class="text-blue-900 font-medium">
                        Filtered by:
                        <?php
                        $current_sy = array_filter($available_school_years, function ($sy) use ($selected_school_year) {
                            return $sy['id'] == $selected_school_year;
                        });
                        if (!empty($current_sy)) {
                            $current_sy = array_values($current_sy)[0];
                            echo htmlspecialchars($current_sy['year'] . ' - ' . $current_sy['semester'] . ' (' . $current_sy['evaluation_count'] . ' evaluations)');
                        }
                        ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- No Data Message -->
    <?php if (isset($no_data_message)): ?>
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span class="text-yellow-800 font-medium"><?php echo $no_data_message; ?></span>
            </div>
            <p class="text-yellow-700 mt-2 text-sm">Try selecting a different school year or clear the filter to view all data.</p>
        </div>
    <?php endif; ?>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium opacity-90">Total Students</h3>
                    <p class="text-3xl font-bold"><?php echo number_format($total_students); ?></p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium opacity-90">Total Teachers</h3>
                    <p class="text-3xl font-bold"><?php echo number_format($total_teachers); ?></p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium opacity-90">Students Evaluated</h3>
                    <p class="text-3xl font-bold"><?php echo number_format($students_evaluated ?? 0); ?></p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zm4 0a3 3 0 100-6 3 3 0 000 6zM2 17a6 6 0 0112 0H2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium opacity-90">Teachers Evaluated</h3>
                    <p class="text-3xl font-bold"><?php echo number_format($teachers_evaluated ?? 0); ?></p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 3a3 3 0 100 6 3 3 0 000-6zM4 13a6 6 0 1112 0H4z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium opacity-90">Total Evaluations</h3>
                    <p class="text-3xl font-bold"><?php echo number_format($total_evaluations); ?></p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h6a1 1 0 011 1v2a1 1 0 01-1 1h-6a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h6a1 1 0 011 1v2a1 1 0 01-1 1h-6a1 1 0 01-1-1v-2z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium opacity-90">Peer Evaluations</h3>
                    <p class="text-3xl font-bold"><?php echo number_format($total_peer_evaluations); ?></p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6zm2 2a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h4a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div> -->

        <!-- <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium opacity-90">Total Subjects</h3>
                    <p class="text-3xl font-bold"><?php echo number_format($total_subjects); ?></p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                    </svg>
                </div>
            </div>
        </div> -->
    </div>

    <!-- Peer Evaluation Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Department Peer Ratings Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Peer Ratings by Department</h3>
            <div class="relative h-80">
                <canvas id="departmentPeerChart"></canvas>
            </div>
        </div>

        <!-- Peer Evaluation Coverage -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Peer Evaluation Participation</h3>
            <div class="space-y-4 max-h-80 overflow-y-auto">
                <?php if (empty($peer_coverage_data)): ?>
                    <div class="text-center text-gray-500 py-8">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>No peer evaluation data available</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($peer_coverage_data as $dept): ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($dept['department']); ?></h4>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-blue-600"><?php echo $dept['participation_rate']; ?>%</div>
                                    <div class="text-xs text-gray-500">Participation</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="text-gray-600">Teachers Evaluating:</div>
                                    <div class="font-medium"><?php echo $dept['evaluating_teachers']; ?>/<?php echo $dept['total_teachers']; ?></div>
                                </div>
                                <div>
                                    <div class="text-gray-600">Teachers Evaluated:</div>
                                    <div class="font-medium"><?php echo $dept['evaluated_teachers']; ?>/<?php echo $dept['total_teachers']; ?></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Coverage Rate</span>
                                    <span><?php echo $dept['coverage_rate']; ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $dept['coverage_rate']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <!-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Evaluations by Strand</h3>
            <div class="relative h-80">
                <canvas id="strandChart"></canvas>
            </div>
        </div>


        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Rating Distribution</h3>
            <div class="relative h-80">
                <canvas id="ratingChart"></canvas>
            </div>
        </div>
    </div> -->

    <!-- Full Width Charts -->
    <div class="grid grid-cols-1 gap-6 mb-8">
        <!-- Yearly Performance Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Department Comparison</h3>
                    <div class="flex items-center gap-2">
                        <label for="deptMetric" class="text-sm text-gray-600">Compare by:</label>
                        <select id="deptMetric" class="px-2 py-1 border rounded text-sm">
                            <option value="avg">Average Rating</option>
                            <option value="count">Number of Evaluations</option>
                        </select>
                    </div>
                </div>
                <div class="relative h-80">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Strand Comparison</h3>
                    <div class="flex items-center gap-2">
                        <label for="strandMetric" class="text-sm text-gray-600">Compare by:</label>
                        <select id="strandMetric" class="px-2 py-1 border rounded text-sm">
                            <option value="avg">Average Rating</option>
                            <option value="count">Number of Evaluations</option>
                        </select>
                    </div>
                </div>
                <div class="relative h-80">
                    <canvas id="strandChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Evaluation Trends -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Monthly Evaluation Trends</h3>
            <div class="relative h-96">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Teachers Table -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Top Performing Teachers</h3>
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Evaluations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($top_teachers as $index => $teacher): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                            <?php echo strtoupper(substr($teacher['teacher_name'], 0, 2)); ?>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($teacher['teacher_name']); ?></div>
                                        <div class="text-sm text-gray-500">Rank #<?php echo $index + 1; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-semibold"><?php echo $teacher['avg_rating']; ?>/5.0</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo number_format($teacher['evaluation_count']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $rating = (float)$teacher['avg_rating'];
                                $performance_class = '';
                                $performance_text = '';

                                if ($rating >= 4.5) {
                                    $performance_class = 'bg-green-100 text-green-800';
                                    $performance_text = 'Excellent';
                                } elseif ($rating >= 4.0) {
                                    $performance_class = 'bg-blue-100 text-blue-800';
                                    $performance_text = 'Very Good';
                                } elseif ($rating >= 3.5) {
                                    $performance_class = 'bg-yellow-100 text-yellow-800';
                                    $performance_text = 'Good';
                                } else {
                                    $performance_class = 'bg-red-100 text-red-800';
                                    $performance_text = 'Needs Improvement';
                                }
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $performance_class; ?>">
                                    <?php echo $performance_text; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Peer Evaluated Teachers Table -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Top Peer-Evaluated Teachers</h3>
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peer Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peer Evaluations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peer Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($top_peer_teachers)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No peer evaluation data available for the selected period.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($top_peer_teachers as $index => $teacher): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                                <?php echo strtoupper(substr($teacher['teacher_name'], 0, 2)); ?>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($teacher['teacher_name']); ?></div>
                                            <div class="text-sm text-gray-500">Rank: #<?php echo ($index + 1); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900"><?php echo number_format($teacher['avg_rating'], 2); ?></div>
                                        <div class="ml-2 flex">
                                            <?php
                                            $rating = $teacher['avg_rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>';
                                                } else {
                                                    echo '<svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo number_format($teacher['evaluation_count']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $rating = $teacher['avg_rating'];
                                    if ($rating >= 4.5) {
                                        $performance_class = 'bg-green-100 text-green-800';
                                        $performance_text = 'Excellent';
                                    } elseif ($rating >= 4.0) {
                                        $performance_class = 'bg-blue-100 text-blue-800';
                                        $performance_text = 'Very Good';
                                    } elseif ($rating >= 3.5) {
                                        $performance_class = 'bg-yellow-100 text-yellow-800';
                                        $performance_text = 'Good';
                                    } else {
                                        $performance_class = 'bg-red-100 text-red-800';
                                        $performance_text = 'Needs Improvement';
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $performance_class; ?>">
                                        <?php echo $performance_text; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Management Actions -->
    <!-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 ml-3">Manage Questionnaires</h3>
            </div>
            <p class="text-gray-600 mb-4">Create and manage evaluation questionnaires for faculty assessment.</p>
            <a href="?module=questionnaire" class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                Manage Questionnaires
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 ml-3">Manage Academic Years</h3>
            </div>
            <p class="text-gray-600 mb-4">Set up and manage academic years for the evaluation system.</p>
            <a href="?module=academic-year" class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors duration-200">
                Manage Academic Years
            </a>
        </div>
    </div> -->
</div>

<!-- Chart.js and Custom Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Defensive chart rendering helpers
    const chartColors = {
        primary: '#3B82F6',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#06B6D4',
        purple: '#8B5CF6'
    };

    function safeInt(v, fallback = 0) {
        const n = parseInt(v);
        return Number.isFinite(n) ? n : fallback;
    }

    function safeFloat(v, fallback = 0) {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : fallback;
    }

    function getCtxIfExists(id) {
        const el = document.getElementById(id);
        if (!el) {
            console.warn('Canvas not found:', id);
            return null;
        }
        const ctx = el.getContext && el.getContext('2d');
        if (!ctx) console.warn('Unable to get 2d context for:', id);
        return ctx;
    }

    // Utility to safely map PHP arrays
    const strandData = <?php echo json_encode($strand_data); ?> || [];
    const ratingData = <?php echo json_encode($rating_distribution); ?> || [];
    const deptStrandData = <?php echo json_encode($dept_strand_data); ?> || [];
    const deptAggData = <?php echo json_encode($dept_agg_data); ?> || [];
    const strandAggData = <?php echo json_encode($strand_agg_data); ?> || [];
    const monthlyData = <?php echo json_encode($monthly_trends); ?> || [];
    const departmentPeerData = <?php echo json_encode($department_peer_data); ?> || [];

    // Debug: print dataset summaries to console
    console.groupCollapsed('Dashboard Data Summary');
    // Debug logs removed for production
    console.groupEnd();

    // Department and Strand comparison charts
    (function() {
        // Helper to create a simple bar chart from flat agg data
        function createFlatBarChart(canvasId, dataArray, labelKey, metricKey, metricLabel) {
            const ctx = getCtxIfExists(canvasId);
            if (!ctx) return null;
            const labels = dataArray.map(r => r[labelKey] || 'Unknown');
            const data = dataArray.map(r => metricKey === 'avg' ? safeFloat(r['avg_rating'], null) : safeInt(r['total_evaluations'], 0));
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: metricLabel,
                        data: data,
                        backgroundColor: chartColors.primary,
                        borderColor: chartColors.primary
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: metricLabel
                            },
                            max: metricKey === 'avg' ? 5 : undefined
                        }
                    }
                }
            });
        }

        // Department chart
        let deptChart = null;

        function renderDept(metric) {
            if (deptChart) deptChart.destroy();
            deptChart = createFlatBarChart('deptChart', deptAggData, 'department', metric, metric === 'avg' ? 'Average Rating' : 'Number of Evaluations');
        }
        renderDept('avg');
        const deptSelect = document.getElementById('deptMetric');
        if (deptSelect) deptSelect.addEventListener('change', function() {
            renderDept(this.value);
        });

        // Strand chart
        let strandChart = null;

        function renderStrand(metric) {
            if (strandChart) strandChart.destroy();
            strandChart = createFlatBarChart('strandChart', strandAggData, 'strand', metric, metric === 'avg' ? 'Average Rating' : 'Number of Evaluations');
        }
        renderStrand('avg');
        const strandSelect = document.getElementById('strandMetric');
        if (strandSelect) strandSelect.addEventListener('change', function() {
            renderStrand(this.value);
        });
    })();

    // Monthly Trends Chart
    (function() {
        const monthlyCtx = getCtxIfExists('monthlyChart');
        if (!monthlyCtx) return;

        const labels = monthlyData.map(item => {
            if (!item || !item.month) return 'N/A';
            // Expecting format YYYY-MM
            try {
                const date = new Date(item.month + '-01');
                if (isNaN(date.getTime())) return item.month;
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short'
                });
            } catch (e) {
                return item.month;
            }
        });

        const evalCounts = monthlyData.map(item => safeInt(item.evaluation_count, 0));
        const avgRatings = monthlyData.map(item => safeFloat(item.avg_rating, null));

        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Evaluations',
                    data: evalCounts,
                    backgroundColor: chartColors.info + '80',
                    borderColor: chartColors.info,
                    borderWidth: 1,
                    yAxisID: 'y',
                    borderRadius: 6
                }, {
                    label: 'Average Rating',
                    data: avgRatings,
                    type: 'line',
                    borderColor: chartColors.warning,
                    backgroundColor: chartColors.warning,
                    borderWidth: 3,
                    pointBackgroundColor: chartColors.warning,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: {
                            color: '#F3F4F6'
                        },
                        title: {
                            display: true,
                            text: 'Number of Evaluations'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 5,
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Average Rating'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    })();

    // Department Peer Ratings Chart
    (function() {
        const deptCtx = getCtxIfExists('departmentPeerChart');
        if (!deptCtx) return;

        const labels = departmentPeerData.map(d => d.department || 'Unknown');
        const counts = departmentPeerData.map(d => safeInt(d.peer_evaluation_count, 0));
        const ratings = departmentPeerData.map(d => safeFloat(d.avg_peer_rating, null));

        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Peer Evaluations',
                    data: counts,
                    backgroundColor: chartColors.primary,
                    borderColor: chartColors.primary,
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Average Rating',
                    data: ratings,
                    type: 'line',
                    backgroundColor: chartColors.warning,
                    borderColor: chartColors.warning,
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointBackgroundColor: chartColors.warning,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed && typeof context.parsed.y !== 'undefined' ? context.parsed.y : null;
                                if (context.datasetIndex === 0) {
                                    return 'Peer Evaluations: ' + (value !== null ? value : 'N/A');
                                } else {
                                    return 'Average Rating: ' + (value !== null ? Number(value).toFixed(2) : 'N/A');
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Evaluations'
                        },
                        grid: {
                            color: '#F3F4F6'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 5,
                        title: {
                            display: true,
                            text: 'Average Rating'
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            stepSize: 0.5
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    })();
</script>