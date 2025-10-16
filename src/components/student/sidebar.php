<?php
include(__DIR__ . '/../../config/database.php');
$currentModule = $_GET['module'] ?? 'dashboard';
$user_id = $_SESSION['userID'] ?? null;
$user_name = 'Student';
$student_id = 'ID: Unknown';
$student_strand = 'Unknown Strand';
$first_initial = 'S';

if ($user_id) {
    $sql = "SELECT firstname, lastname, id, strand FROM users WHERE id = $user_id AND user_type = 'student' LIMIT 1";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $user_name = $row['firstname'] . ' ' . $row['lastname'];
        $student_id = $row['id'];
        $student_strand = $row['strand']; // This will be the strand (STEM, ABM, HUMSS, GAS, ICT)
        $first_initial = strtoupper(substr($row['firstname'], 0, 1));
    }
}
?>


<button id="studentSidebarToggle" class="lg:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-2 rounded-full shadow-lg focus:outline-none">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<aside class="bg-slate-800 text-white w-64 h-full lg:h-screen flex flex-col shadow-xl fixed lg:static z-40 transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0" id="studentSidebar">
    <!-- Header -->
    <div class="p-6 border-b border-slate-700 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-xl"><?php echo $first_initial; ?></span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white"><?php echo htmlspecialchars($user_name); ?></h2>
                <p class="text-xs text-slate-300">Senior High School</p>
                <p class="text-xs text-slate-400">ID: <?php echo htmlspecialchars($student_id); ?> | <?php echo htmlspecialchars($student_strand ?? 'Unknown'); ?> Strand</p>
            </div>
        </div>
        <!-- Close button for mobile -->
        <button id="studentSidebarClose" class="lg:hidden text-white focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2">
        <a href="?module=evaluate_teacher" class="group flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'evaluate_teacher') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">Evaluate Teachers</span>
        </a>
        <!-- <a href="?module=my-evaluations" class="group flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'my-evaluations') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">My Evaluations</span>
        </a> -->
        <!-- <a href="?module=profile" class="group flex items-center space-x-3 p-3 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'profile') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">Profile</span>
        </a> -->
    </nav>

    <!-- Settings -->
    <div class="space-y-1 px-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3">Settings</p>
        <button type="button" onclick="openChangePasswordModal()" class="group flex items-center space-x-3 p-3 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-all duration-200 w-full">
            <div class="w-5 h-5 flex items-center justify-center">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="font-medium">Change Password</span>
        </button>
    </div>

    <!-- Sign Out -->
    <div class="mt-auto p-6 border-t border-slate-700">
        <a href="/faculty_evaluation/src/auth/logout.php" class="flex items-center justify-center space-x-2 w-full p-3 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors duration-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 9a1 1 0 011-1h10a1 1 0 110 2H4a1 1 0 01-1-1zm7 7a1 1 0 01-1-1v-4a1 1 0 112 0v4a1 1 0 01-1 1z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-semibold">Sign Out</span>
        </a>
    </div>
</aside>



<!-- Overlay for mobile -->
<div id="studentSidebarOverlay" class="fixed inset-0 bg-black opacity-50 z-30 hidden lg:hidden"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('studentSidebar');
        const toggleBtn = document.getElementById('studentSidebarToggle');
        const closeBtn = document.getElementById('studentSidebarClose');
        const overlay = document.getElementById('studentSidebarOverlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        toggleBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSidebar();
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
    });
</script>