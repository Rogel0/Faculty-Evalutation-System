<?php
// Get current route for active state
$currentModule = $_GET['module'] ?? 'dashboard';

$user_name = $_SESSION['displayName'] ?? 'Administrator';

// Get first initial for avatar
$first_initial = strtoupper(substr($_SESSION['firstname'] ?? $_SESSION['username'] ?? 'A', 0, 1));
?>

<aside class="bg-slate-700 text-white w-64 min-h-screen flex flex-col shadow-xl">
    <!-- Header -->
    <div class="p-6 border-b border-slate-600">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white"><?php echo htmlspecialchars($user_name); ?></h2>
                <p class="text-xs text-slate-300">Faculty Evaluation</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 p-4 space-y-2">
        <!-- Dashboard -->
        <a href="?module=dashboard"
            class="group flex items-center space-x-2 p-4 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'dashboard') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
            <div class="w-5 h-5 flex items-center justify-center">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
            </div>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Questionnaire -->
        <a href="?module=student_questionnaire"
            class="group flex items-center space-x-2 p-4 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'student_questionnaire') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
            <div class="w-5 h-5 flex items-center justify-center">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="font-medium">Student Questionnaire</span>

        </a>

        <!-- Teacher Questionnaire -->
        <a href="?module=teacher_questionnaire"
            class="group flex items-center space-x-2 p-4 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'teacher_questionnaire') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
            <div class="w-5 h-5 flex items-center justify-center">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="font-medium">Teacher Questionnaire</span>
        </a>

        <!-- Add Student -->
        <a href="?module=add_student"
            class="group flex items-center space-x-2 p-4 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'add_student') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
            <div class="w-5 h-5 flex items-center justify-center">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                    <path d="M8 9a3 3 0 116 0 3 3 0 01-6 0zM2 17a6 6 0 1112 0H2z" />
                    <path d="M16 11v2m0 0v2m0-2h2m-2 0h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <span class="font-medium">Add Student</span>
        </a>

        <!-- Add Teacher -->
        <!-- <a href="?module=add_teacher"
            class="group flex items-center space-x-2 p-4 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'add_teacher') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
            <div class="w-5 h-5 flex items-center justify-center">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                    <path d="M8 9a3 3 0 116 0 3 3 0 01-6 0zM2 17a6 6 0 1112 0H2z" />
                    <path d="M16 11v2m0 0v2m0-2h2m-2 0h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <span class="font-medium">Add Teacher</span>
        </a> -->

        </a>

        <!-- Academic Year -->
        <a href="?module=academic-year"
            class="group flex items-center space-x-2 p-4 rounded-lg transition-all duration-200 <?php echo ($currentModule === 'academic-year') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
            <div class="w-5 h-5 flex items-center justify-center">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="font-medium">Academic Year</span>
        </a>

        <!-- Divider -->
        <div class="border-t border-slate-600 my-4"></div>

        <!-- Settings -->
        <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-3">Settings</p>

            <a href="#" class="group flex items-center space-x-3 p-3 rounded-lg text-slate-300 hover:bg-slate-600 hover:text-white transition-all duration-200">
                <div class="w-5 h-5 flex items-center justify-center">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <span class="font-medium">Change Password</span>
            </a>

            <!-- <a href="#" class="group flex items-center space-x-3 p-3 rounded-lg text-slate-300 hover:bg-slate-600 hover:text-white transition-all duration-200">
                <div class="w-5 h-5 flex items-center justify-center">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <span class="font-medium">Help & Support</span>
            </a> -->
        </div>
    </nav>

    <!-- User Section at Bottom -->
    <div class="p-4 border-t border-slate-600">
        <div class="flex items-center space-x-3 mb-3 p-3 bg-slate-600 rounded-lg">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold text-sm">
                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">
                    <?php echo $_SESSION['admin_name'] ?? 'Administrator'; ?>
                </p>
                <p class="text-xs text-slate-300">System Admin</p>
            </div>
        </div>

        <a href="../auth/logout.php"
            class="flex items-center justify-center space-x-2 w-full p-3 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors duration-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">Sign Out</span>
        </a>
    </div>
</aside>