<?php
// include database config (resolve relative to src/config)
include_once(__DIR__ . '/../../../config/database.php');
?>

<div class="flex">
    <div id="drawerOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-10 hidden"></div>
    <div id="drawerContent" class="fixed top-0 right-0 z-20 w-full max-w-2xl h-full bg-white shadow-xl transform translate-x-full transition-transform duration-500">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">New Subject</h2>
                <button id="closeDrawerBtn" type="button" class="px-3 py-1 rounded hover:bg-gray-100 transition text-gray-600">×</button>
            </div>
            <div class="flex-1 p-6 overflow-y-auto">
                <form action="../actions/AddSubject.php" method="POST" class="space-y-8">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Subject Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="subject_code" class="block text-sm font-medium text-gray-700">Subject Code</label>
                                <input type="text" name="subject_code" id="subject_code" required placeholder="e.g., MATH101" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="subject_name" class="block text-sm font-medium text-gray-700">Subject Name</label>
                                <input type="text" name="subject_name" id="subject_name" required placeholder="e.g., Algebra I" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="4" placeholder="Optional description" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm"></textarea>
                            </div>
                        </div>
                    </div>

            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-4 bg-white">
                <button type="button" id="closeDrawerBtn" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition shadow">Add Subject</button>
            </div>
            </form>
        </div>
    </div>
</div>