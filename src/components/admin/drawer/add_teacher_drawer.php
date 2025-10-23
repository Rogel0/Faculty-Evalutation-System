<?php
include_once('../config/database.php');
?>

<div class="flex">
    <div id="drawerOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-10 hidden"></div>
    <div id="drawerContent" class="fixed top-0 right-0 z-20 w-full max-w-2xl h-full bg-white shadow-xl transform translate-x-full transition-transform duration-500">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">New Teacher</h2>
                <button id="closeDrawerBtn" type="button" class="px-3 py-1 rounded hover:bg-gray-100 transition text-gray-600">×</button>
            </div>
            <div class="flex-1 p-6 overflow-y-auto">
                <form action="../actions/AddTeacher.php" method="POST" class="space-y-8">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="t_lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="lastname" id="t_lastname" required placeholder="Enter last name" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="t_firstname" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="firstname" id="t_firstname" required placeholder="Enter first name" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="t_middlename" class="block text-sm font-medium text-gray-700">Middle Name <span class="text-xs text-gray-400">(optional)</span></label>
                                <input type="text" name="middlename" id="t_middlename" placeholder="Enter middle name" class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <!-- Position is fixed to 'Teacher' and not editable -->
                                <input type="hidden" name="position" value="Teacher" />
                                <label class="block text-sm font-medium text-gray-700">Position</label>
                                <div class="mt-2 text-sm text-gray-700">Teacher</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Account & Contact</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="t_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="t_email" required placeholder="Enter email" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="t_username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" id="t_username" required placeholder="Enter username" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="t_teacher_code" class="block text-sm font-medium text-gray-700">Teacher ID</label>
                                <input type="text" name="teacher_code" id="t_teacher_code" required placeholder="Enter Teacher ID (e.g., EMP123)" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="t_password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" name="password" id="t_password" required placeholder="Enter password" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="t_department" class="block text-sm font-medium text-gray-700">Department</label>
                                <input type="text" name="department" id="t_department" placeholder="Enter department" class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Optional: assign subjects to teacher (multiple) -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Assign Subjects (optional)</h3>
                        <p class="text-sm text-gray-600 mb-4">Assign one or more subjects to this teacher for the current school year.</p>

                        <div id="subjectTeacherPairs" class="space-y-2">
                            <?php
                            $subjects = [];
                            $subjResult = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name");
                            if ($subjResult) while ($r = $subjResult->fetch_assoc()) $subjects[] = $r;
                            ?>
                            <div class="flex items-center gap-3">
                                <select name="subject_ids[]" class="flex-1 mt-1 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                                    <option value="">Select subject</option>
                                    <?php foreach ($subjects as $s): ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['subject_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" onclick="removePair(this)" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 text-xs">&times;</button>
                            </div>

                            <template id="subjectTeacherTemplate">
                                <div class="flex items-center gap-3">
                                    <select name="subject_ids[]" class="flex-1 mt-1 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm" required>
                                        <option value="">Select subject</option>
                                        <?php foreach ($subjects as $s): ?>
                                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['subject_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" onclick="removePair(this)" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 text-xs">&times;</button>
                                </div>
                            </template>

                        </div>

                        <button type="button" onclick="addPair()" class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium shadow">+ Add Subject</button>
                        <p class="text-xs text-gray-500 mt-2">You can add multiple subjects before creating the teacher.</p>
                    </div>

            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-4 bg-white">
                <button type="button" id="closeDrawerBtn" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition shadow">Add Teacher</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- <style>
    #drawerOverlay {
        opacity: 0.5;
        transition: opacity 0.3s ease-in-out;
        background-color: black;
    }
</style -->