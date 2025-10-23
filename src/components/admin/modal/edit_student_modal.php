<!-- Edit Student Modal (reusable modal in modal folder) -->
<div id="editStudentModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50" onclick="if(event.target==this)this.classList.add('hidden')">
    <div class="absolute inset-0 opacity-50 bg-black"></div>
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white z-10">
        <div class="flex flex-col items-start">
            <div class="flex items-center justify-between w-full">
                <h3 class="text-2xl font-bold text-gray-900">Edit Student</h3>
                <button onclick="document.getElementById('editStudentModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editStudentForm" class="w-full">
                <input type="hidden" name="id" id="edit_student_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 w-full">
                    <div>
                        <label class="block text-sm">Student ID</label>
                        <input type="text" name="student_id" id="edit_student_id_input" readonly class="mt-1 w-full rounded border px-3 py-2 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm">First Name</label>
                        <input type="text" name="firstname" id="edit_firstname" class="mt-1 w-full rounded border px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm">Last Name</label>
                        <input type="text" name="lastname" id="edit_lastname" class="mt-1 w-full rounded border px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm">Email</label>
                        <input type="email" name="email" id="edit_email" class="mt-1 w-full rounded border px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm">Grade Level</label>
                        <select name="grade_level" id="edit_grade_level" class="mt-1 w-full rounded border px-3 py-2">
                            <option value="">Select</option>
                            <option value="11">Grade 11</option>
                            <option value="12">Grade 12</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm">Strand</label>
                        <select name="course" id="edit_strand" class="mt-1 w-full rounded border px-3 py-2">
                            <option value="">Select Strand</option>
                            <?php
                            // Keep strand labels consistent with table component
                            $strandLabels = [
                                'STEM' => 'STEM (Science, Technology, Engineering, and Mathematics)',
                                'ABM' => 'ABM (Accountancy, Business and Management)',
                                'HUMSS' => 'HUMSS (Humanities and Social Sciences)',
                                'GAS' => 'GAS (General Academic Strand)',
                                'ICT' => 'ICT (Information and Communications Technology)',
                                'HE' => 'HE (Home Economics)',
                                'IA' => 'IA (Industrial Arts)',
                                'AFA' => 'AFA (Agri-Fishery Arts)',
                                'SPORTS' => 'Sports Track',
                                'AD' => 'AD (Arts and Design)'
                            ];
                            foreach ($strandLabels as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h4 class="mt-4 mb-2 font-semibold">Subject / Teacher Assignments</h4>
                <div id="editPairs" class="space-y-2 w-full">
                    <!-- pairs injected here -->
                </div>

                <div class="flex justify-end gap-4 mt-6 w-full">
                    <button type="button" onclick="document.getElementById('editStudentModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button id="saveEditBtn" type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>