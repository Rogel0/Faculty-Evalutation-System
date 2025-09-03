<?php
include_once('../config/database.php');
?>

<div class="p-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 t">Manage Student Evaluator</h1>
        <div class="flex gap-4">
            <button id="addStudentBtn" class="flex items-center px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Student</span>
            </button>
            <button id="uploadExcelBtn" class="flex items-center px-5 py-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-lg shadow transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16m16-8H4" />
                </svg>
                <span>Upload Excel/CSV</span>
            </button>
        </div>
    </div>

    <div class="flex items-center mb-6">
        <div class="relative w-full max-w-md">
            <input type="text" id="searchInput" placeholder="Search student..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-gray-900 placeholder-gray-400">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

    <?php include('tables/add_student_table.php') ?>
    <?php include('drawer/add_student_drawer.php') ?>
</div>