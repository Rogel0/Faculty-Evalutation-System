<?php
// include database config (resolve relative to src/config)
include('../config/database.php');
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<div class="p-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Manage Subjects</h1>
        <div class="flex gap-4">
            <button id="addSubjectBtn" class="flex items-center px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Subject</span>
            </button>
        </div>
    </div>

    <div class="flex items-center mb-6">
        <div class="relative w-full max-w-md">
            <form method="GET" action="?module=add_subject" id="subjectSearchForm">
                <input type="hidden" name="module" value="add_subject" />
                <?php $search_q = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>
                <input type="text" name="q" id="searchInput" value="<?php echo $search_q; ?>" placeholder="Search subject..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-gray-900 placeholder-gray-400">
            </form>
        </div>
    </div>

    <?php include('tables/add_subject_table.php'); ?>
    <?php include('drawer/add_subject_drawer.php'); ?>
</div>

<script>
    document.getElementById('addSubjectBtn').addEventListener('click', function() {
        document.getElementById('drawerOverlay').classList.remove('hidden');
        document.getElementById('drawerContent').classList.remove('translate-x-full');
        document.getElementById('drawerContent').classList.add('translate-x-0');
    });
    document.querySelectorAll('#closeDrawerBtn').forEach(btn => btn.addEventListener('click', function() {
        document.getElementById('drawerOverlay').classList.add('hidden');
        document.getElementById('drawerContent').classList.remove('translate-x-0');
        document.getElementById('drawerContent').classList.add('translate-x-full');
    }));
</script>