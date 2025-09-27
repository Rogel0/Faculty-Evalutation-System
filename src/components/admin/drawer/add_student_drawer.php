<?php
include_once('../config/database.php');
?>

<div class="flex">
    <!-- Overlay -->
    <div id="drawerOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-10 hidden"></div>

    <!-- Drawer -->
    <div id="drawerContent" class="fixed top-0 right-0 z-20 w-full max-w-2xl h-full bg-white shadow-xl transform translate-x-full transition-transform duration-500">
        <div class="flex flex-col h-full">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">New Student</h2>
                <button id="closeDrawerBtn" type="button" class="px-3 py-1 rounded hover:bg-gray-100 transition text-gray-600">×</button>
            </div>

            <!-- Body -->
            <div class="flex-1 p-6 overflow-y-auto">
                <form action="../actions/AddStudent.php" method="POST" class="space-y-8">

                    <!-- Basic Info -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student ID -->
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700">Student ID</label>
                                <input type="text" name="student_id" id="student_id" required placeholder="Enter student ID"
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <!-- Last Name -->
                            <div>
                                <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="lastname" id="lastname" required placeholder="Enter last name"
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <!-- First Name -->
                            <div>
                                <label for="firstname" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="firstname" id="firstname" required placeholder="Enter first name"
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <!-- Middle Name -->
                            <div>
                                <label for="middlename" class="block text-sm font-medium text-gray-700">Middle Name <span class="text-xs text-gray-400">(optional)</span></label>
                                <input type="text" name="middlename" id="middlename" placeholder="Enter middle name"
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <!-- Strand -->
                            <div class="md:col-span-2">
                                <label for="course" class="block text-sm font-medium text-gray-700">Strand</label>
                                <select name="course" id="course" required
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                                    <option value="">Select SHS Strand</option>
                                    <optgroup label="📚 Academic Track">
                                        <option value="STEM">STEM - Science, Technology, Engineering, Mathematics</option>
                                        <option value="HUMSS">HUMSS - Humanities and Social Sciences</option>
                                        <option value="ABM">ABM - Accountancy, Business, and Management</option>
                                        <option value="GAS">GAS - General Academic Strand</option>
                                    </optgroup>
                                    <optgroup label="🔧 TVL Track">
                                        <option value="ICT">ICT - Information and Communications Technology</option>
                                        <option value="HE">HE - Home Economics</option>
                                        <option value="IA">IA - Industrial Arts</option>
                                        <option value="AFA">AFA - Agri-Fishery Arts</option>
                                    </optgroup>
                                    <optgroup label="🏃 Other Tracks">
                                        <option value="SPORTS">Sports Track</option>
                                        <option value="AD">Arts and Design Track</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Subject & Teacher -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Subject & Teacher Assignment</h3>
                        <p class="text-sm text-gray-600 mb-4">Assign subject(s) and teacher(s) for this student.</p>

                        <div id="subjectTeacherPairs" class="space-y-2">
                            <?php
                            $subjects = [];
                            $subjResult = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name");
                            if ($subjResult) {
                                while ($row = $subjResult->fetch_assoc()) {
                                    $subjects[] = $row;
                                }
                            }
                            $teachers = [];
                            $teacherResult = $conn->query("SELECT id, firstname, lastname FROM users WHERE user_type = 'teacher' ORDER BY lastname, firstname");
                            if ($teacherResult) {
                                while ($row = $teacherResult->fetch_assoc()) {
                                    $teachers[] = $row;
                                }
                            }
                            ?>
                            <div class="flex items-center gap-3">
                                <select name="subject_id[]" class="flex-1 mt-1 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm" required>
                                    <option value="">Select subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="teacher_id[]" class="flex-1 mt-1 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm" required>
                                    <option value="">Select teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['lastname'] . ', ' . $teacher['firstname']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" onclick="removePair(this)" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 text-xs">&times;</button>
                            </div>
                            <!-- template for cloning new pairs -->
                            <template id="subjectTeacherTemplate">
                                <div class="flex items-center gap-3">
                                    <select name="subject_id[]" class="flex-1 mt-1 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm" required>
                                        <option value="">Select subject</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="teacher_id[]" class="flex-1 mt-1 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm" required>
                                        <option value="">Select teacher</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['lastname'] . ', ' . $teacher['firstname']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" onclick="removePair(this)" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 text-xs">&times;</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" onclick="addPair()" class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium shadow">+ Add Subject/Teacher</button>
                    </div>

                    <!-- Contact Info -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-bold text-indigo-700 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12v1m0 4v1m-8-5v1m0 4v1m8-10V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m0 10a2 2 0 002 2h4a2 2 0 002-2v-2" />
                            </svg>
                            Contact & Account Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" required placeholder="Enter email"
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="birthdate" class="block text-sm font-medium text-gray-700">Birth Date</label>
                                <input type="date" name="birthdate" id="birthdate" required
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Account Username</label>
                                <input type="text" name="username" id="username" required placeholder="Enter username"
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Account Password</label>
                                <input type="password" name="password" id="password" required placeholder="Enter password"
                                    class="mt-2 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-4 py-2.5 shadow-sm">
                            </div>
                        </div>
                    </div>

            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-4 bg-white">
                <button type="button" id="closeDrawerBtn" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition shadow">Add Student</button>
            </div>
            </form>
        </div>
    </div>
</div>

<style>
    #drawerOverlay {
        opacity: 0.5;
        transition: opacity 0.3s ease-in-out;
        background-color: black;
    }
</style>