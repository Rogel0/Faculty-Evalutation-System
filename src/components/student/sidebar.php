<!-- Mobile Menu Button -->
<div class="lg:hidden fixed top-0 left-0 z-50 w-full bg-slate-700 p-4">
    <button id="mobile-menu-toggle" class="text-white focus:outline-none focus:text-slate-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    <span class="ml-3 text-white font-semibold">Student Portal</span>
</div>

<!-- Sidebar -->
<div id="sidebar" class="fixed top-16 bottom-0 left-0 z-40 w-64 bg-slate-700 text-white transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:top-0 shadow-xl">
    <div class="flex items-center justify-between h-16 px-6 bg-slate-600 border-b border-slate-600">
        <h2 class="text-white text-xl font-bold">Student Panel</h2>
        <button id="sidebar-close" class="lg:hidden text-white focus:outline-none focus:text-slate-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-8 px-4">
        <ul class="space-y-2">
            <!-- Evaluate Teachers -->
            <li>
                <a href="?module=evaluate"
                    class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo (!isset($_GET['module']) || $_GET['module'] === 'evaluate') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">Evaluate Teachers</span>
                    <?php if (!isset($_GET['module']) || $_GET['module'] === 'evaluate'): ?>
                        <div class="w-1 h-8 bg-gray-900 rounded-full ml-auto"></div>
                    <?php endif; ?>
                </a>
            </li>

            <!-- My Evaluations -->
            <li>
                <a href="?module=my-evaluations"
                    class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo (isset($_GET['module']) && $_GET['module'] === 'my-evaluations') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">My Evaluations</span>
                    <?php if (isset($_GET['module']) && $_GET['module'] === 'my-evaluations'): ?>
                        <div class="w-1 h-8 bg-gray-900 rounded-full ml-auto"></div>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Profile -->
            <li>
                <a href="?module=profile"
                    class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo (isset($_GET['module']) && $_GET['module'] === 'profile') ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'text-slate-300 hover:bg-slate-600 hover:text-white'; ?>">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">Profile</span>
                    <?php if (isset($_GET['module']) && $_GET['module'] === 'profile'): ?>
                        <div class="w-1 h-8 bg-gray-900 rounded-full ml-auto"></div>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </nav>

    <!-- User Info Section -->
    <div class="absolute bottom-0 w-full p-4 border-t border-slate-600">
        <div class="flex items-center mb-3 p-3 bg-slate-600 rounded-lg">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-sm">
                        <?php echo substr($_SESSION['student_name'] ?? 'S', 0, 1); ?>
                    </span>
                </div>
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">
                    <?php echo $_SESSION['student_name'] ?? 'Student'; ?>
                </p>
                <p class="text-xs text-slate-300 truncate">
                    <?php echo $_SESSION['student_id'] ?? 'ID: Unknown'; ?>
                </p>
            </div>
        </div>

        <a href="/faculty_evaluation/src/auth/logout.php"
            class="flex items-center justify-center space-x-2 w-full p-3 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors duration-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">Sign Out</span>
        </a>
    </div>
</div>
</div>

<!-- Overlay for mobile menu -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-30 lg:hidden hidden"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarClose = document.getElementById('sidebar-close');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Mobile menu toggle
        mobileMenuToggle?.addEventListener('click', openSidebar);

        // Close sidebar
        sidebarClose?.addEventListener('click', closeSidebar);

        // Close sidebar when clicking overlay
        sidebarOverlay?.addEventListener('click', closeSidebar);

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        // Close sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
    });
</script>