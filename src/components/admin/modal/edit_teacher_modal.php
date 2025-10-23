<div id="editTeacherModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50" onclick="if(event.target==this)this.classList.add('hidden')">
    <div class="absolute inset-0 opacity-50 bg-black"></div>
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white z-10">
        <div class="flex items-center justify-between">
            <h3 class="text-2xl font-bold">Edit Teacher</h3>
            <button onclick="document.getElementById('editTeacherModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">×</button>
        </div>
        <form id="editTeacherForm" class="mt-4">
            <input type="hidden" name="id" id="edit_teacher_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm">Teacher Code</label>
                    <input type="text" name="teacher_code" id="edit_teacher_code" readonly class="mt-1 w-full rounded border px-3 py-2 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm">First Name</label>
                    <input type="text" name="firstname" id="edit_teacher_firstname" class="mt-1 w-full rounded border px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Last Name</label>
                    <input type="text" name="lastname" id="edit_teacher_lastname" class="mt-1 w-full rounded border px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Email</label>
                    <input type="email" name="email" id="edit_teacher_email" class="mt-1 w-full rounded border px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Department</label>
                    <input type="text" name="department" id="edit_teacher_department" class="mt-1 w-full rounded border px-3 py-2">
                </div>
            </div>
            <h4 class="mt-4">Assigned Subjects</h4>
            <div id="editTeacherSubjects" class="space-y-2"></div>
            <div class="flex justify-end gap-4 mt-6">
                <button type="button" onclick="document.getElementById('editTeacherModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 rounded">Cancel</button>
                <button id="saveTeacherBtn" type="submit" class="px-4 py-2 bg-amber-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>