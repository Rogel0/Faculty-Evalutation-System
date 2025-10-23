<?php
include_once('../config/database.php');
?>

<div class="flex">
    <div id="drawerOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-10 hidden"></div>
    <div id="drawerContent" class="fixed top-0 right-0 z-20 w-full max-w-2xl h-full bg-white shadow-xl transform translate-x-full transition-transform duration-500">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">New Supervisor</h2>
                <button id="closeDrawerBtn" type="button" class="px-3 py-1 rounded hover:bg-gray-100 transition text-gray-600">×</button>
            </div>
            <div class="flex-1 p-6 overflow-y-auto">
                <form action="../actions/AddSupervisor.php" method="POST" class="space-y-8">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="s_lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="lastname" id="s_lastname" required placeholder="Enter last name" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="s_firstname" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="firstname" id="s_firstname" required placeholder="Enter first name" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="s_middlename" class="block text-sm font-medium text-gray-700">Middle Name <span class="text-xs text-gray-400">(optional)</span></label>
                                <input type="text" name="middlename" id="s_middlename" placeholder="Enter middle name" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="s_position" class="block text-sm font-medium text-gray-700">Position</label>
                                <input type="text" name="position" id="s_position" placeholder="e.g., Supervisor" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Account & Contact</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="s_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="s_email" required placeholder="Enter email" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="s_username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" id="s_username" required placeholder="Enter username" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="s_password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" name="password" id="s_password" required placeholder="Enter password" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="s_department" class="block text-sm font-medium text-gray-700">Department</label>
                                <input type="text" name="department" id="s_department" placeholder="Enter department" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                        </div>
                    </div>

            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-4 bg-white">
                <button type="button" id="closeDrawerBtn" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition shadow">Add Supervisor</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- drawer overlay styling moved to src/styles/global.css; removed duplicate/malformed inline style -->