<div class="overflow-x-auto border border-gray-200 bg-white shadow-sm rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Teacher ID</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Name</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Email</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Department</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <?php
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $records_per_page = 10;
            $offset = ($page - 1) * $records_per_page;
            $search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $search_esc = $conn->real_escape_string($search_q);

            if ($search_q !== '') {
                $total_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'teacher' AND (firstname LIKE '%$search_esc%' OR lastname LIKE '%$search_esc%' OR email LIKE '%$search_esc%')";
            } else {
                $total_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'teacher'";
            }
            $total_result = $conn->query($total_query);
            $total_records = $total_result->fetch_assoc()['count'];
            $total_pages = ceil($total_records / $records_per_page);

            // get active school year id (if any) to filter teacher_subjects
            $school_year_id = null;
            $syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
            if ($syRes && $r = $syRes->fetch_assoc()) $school_year_id = (int)$r['id'];

            if ($search_q !== '') {
                $result = $conn->query("SELECT id, teacher_code, firstname, lastname, email, department FROM users WHERE user_type = 'teacher' AND (firstname LIKE '%$search_esc%' OR lastname LIKE '%$search_esc%' OR email LIKE '%$search_esc%') ORDER BY id DESC LIMIT $offset, $records_per_page");
            } else {
                $result = $conn->query("SELECT id, teacher_code, firstname, lastname, email, department FROM users WHERE user_type = 'teacher' ORDER BY id DESC LIMIT $offset, $records_per_page");
            }

            if ($result && $result->num_rows > 0):
                $rowIndex = 0;
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-200 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['teacher_code']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['lastname'] . ', ' . $row['firstname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['department']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-center border-b border-gray-100">
                            <div class="flex items-center justify-center gap-2">
                                <a href="#" data-teacher-id="<?php echo $row['id']; ?>" class="edit-teacher inline-flex items-center px-3 py-1 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold text-xs transition">Edit</a>
                            </div>
                        </td>
                    </tr>
                <?php $rowIndex++;
                endwhile;
            else: ?>
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">No teachers added yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-700">Showing <span class="font-medium"><?php echo $offset + 1 ?></span> to <span class="font-medium"><?php echo min($offset + $records_per_page, $total_records) ?></span> of <span class="font-medium"><?php echo $total_records ?></span> results</p>
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?><a href="?module=add_teacher&page=<?php echo ($page - 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Previous</a><?php endif; ?>
                    <?php if ($page < $total_pages): ?><a href="?module=add_teacher&page=<?php echo ($page + 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Next</a><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include_once(__DIR__ . '/../modal/edit_teacher_modal.php'); ?>

<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-teacher')) {
            e.preventDefault();
            const btn = e.target.closest('.edit-teacher');
            const id = btn.getAttribute('data-teacher-id');
            openEditTeacher(id);
        }
    });

    function openEditTeacher(id) {
        const modal = document.getElementById('editTeacherModal');
        modal.classList.remove('hidden');
        fetch('../actions/GetTeacher.php?id=' + encodeURIComponent(id))
            .then(r => r.json()).then(data => {
                if (data.error) return showToast(data.error, 'error');
                document.getElementById('edit_teacher_id').value = data.id;
                document.getElementById('edit_teacher_code').value = data.teacher_code || '';
                document.getElementById('edit_teacher_firstname').value = data.firstname || '';
                document.getElementById('edit_teacher_lastname').value = data.lastname || '';
                document.getElementById('edit_teacher_email').value = data.email || '';
                document.getElementById('edit_teacher_department').value = data.department || '';

                // populate assigned subjects as hidden inputs for submission
                const container = document.getElementById('editTeacherSubjects');
                container.innerHTML = '';
                (data.subject_ids || []).forEach(sid => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center gap-2';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'subject_ids[]';
                    input.value = sid;
                    div.appendChild(input);
                    const span = document.createElement('span');
                    span.className = 'text-sm text-gray-700';
                    span.textContent = 'Subject ID: ' + sid;
                    div.appendChild(span);
                    container.appendChild(div);
                });
            }).catch(err => showToast('Failed to load teacher: ' + err, 'error'));
    }

    document.getElementById('editTeacherForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        fetch('../actions/UpdateTeacherAjax.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    document.getElementById('editTeacherModal').classList.add('hidden');
                    showToast('Teacher updated', 'success');
                    setTimeout(() => location.reload(), 700);
                } else showToast(data.error || 'Failed to update', 'error');
            }).catch(err => showToast('Request failed: ' + err, 'error'));
    });
</script>