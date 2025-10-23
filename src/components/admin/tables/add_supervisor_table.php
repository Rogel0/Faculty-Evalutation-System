<div class="overflow-x-auto border border-gray-200 bg-white shadow-sm rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Name</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Email</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Department</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Position</th>
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
                $total_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'supervisor' AND (firstname LIKE '%$search_esc%' OR lastname LIKE '%$search_esc%' OR email LIKE '%$search_esc%')";
            } else {
                $total_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'supervisor'";
            }
            $total_result = $conn->query($total_query);
            $total_records = $total_result->fetch_assoc()['count'];
            $total_pages = ceil($total_records / $records_per_page);

            if ($search_q !== '') {
                $result = $conn->query("SELECT id, firstname, lastname, email, position, department FROM users WHERE user_type = 'supervisor' AND (firstname LIKE '%$search_esc%' OR lastname LIKE '%$search_esc%' OR email LIKE '%$search_esc%') ORDER BY id DESC LIMIT $offset, $records_per_page");
            } else {
                $result = $conn->query("SELECT id, firstname, lastname, email, position, department FROM users WHERE user_type = 'supervisor' ORDER BY id DESC LIMIT $offset, $records_per_page");
            }

            if ($result && $result->num_rows > 0):
                $rowIndex = 0;
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-200 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['lastname'] . ', ' . $row['firstname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['department']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['position']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-center border-b border-gray-100">
                            <div class="flex items-center justify-center gap-2">
                                <a href="#" data-supervisor-id="<?php echo $row['id']; ?>" class="edit-supervisor inline-flex items-center px-3 py-1 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold text-xs transition">Edit</a>
                            </div>
                        </td>
                    </tr>
                <?php $rowIndex++;
                endwhile;
            else: ?>
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">No supervisors added yet.</td>
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
                    <?php if ($page > 1): ?><a href="?module=add_supervisor&page=<?php echo ($page - 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Previous</a><?php endif; ?>
                    <?php if ($page < $total_pages): ?><a href="?module=add_supervisor&page=<?php echo ($page + 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Next</a><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php // include modal and JS for edit supervisor
?>

<div id="editSupervisorModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50" onclick="if(event.target==this)this.classList.add('hidden')">
    <div class="absolute inset-0 opacity-50 bg-black"></div>
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white z-10">
        <div class="flex items-center justify-between">
            <h3 class="text-2xl font-bold">Edit Supervisor</h3>
            <button onclick="document.getElementById('editSupervisorModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">×</button>
        </div>
        <form id="editSupervisorForm" class="mt-4">
            <input type="hidden" name="id" id="edit_supervisor_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm">First Name</label>
                    <input type="text" name="firstname" id="edit_supervisor_firstname" class="mt-1 w-full rounded border px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Last Name</label>
                    <input type="text" name="lastname" id="edit_supervisor_lastname" class="mt-1 w-full rounded border px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Username</label>
                    <input type="text" name="username" id="edit_supervisor_username" readonly class="mt-1 w-full rounded border px-3 py-2 bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm">Email</label>
                    <input type="email" name="email" id="edit_supervisor_email" class="mt-1 w-full rounded border px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Department</label>
                    <input type="text" name="department" id="edit_supervisor_department" class="mt-1 w-full rounded border px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm">Position</label>
                    <input type="text" name="position" id="edit_supervisor_position" class="mt-1 w-full rounded border px-3 py-2">
                </div>
            </div>
            <div class="flex justify-end gap-4 mt-6">
                <button type="button" onclick="document.getElementById('editSupervisorModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 rounded">Cancel</button>
                <button id="saveSupervisorBtn" type="submit" class="px-4 py-2 bg-amber-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-supervisor')) {
            e.preventDefault();
            const btn = e.target.closest('.edit-supervisor');
            const id = btn.getAttribute('data-supervisor-id');
            openEditSupervisor(id);
        }
    });

    function openEditSupervisor(id) {
        const modal = document.getElementById('editSupervisorModal');
        modal.classList.remove('hidden');
        fetch('../actions/GetSupervisor.php?id=' + encodeURIComponent(id))
            .then(r => r.json()).then(data => {
                if (data.error) return showToast(data.error, 'error');
                document.getElementById('edit_supervisor_id').value = data.id;
                document.getElementById('edit_supervisor_username').value = data.username || '';
                document.getElementById('edit_supervisor_firstname').value = data.firstname || '';
                document.getElementById('edit_supervisor_lastname').value = data.lastname || '';
                document.getElementById('edit_supervisor_email').value = data.email || '';
                document.getElementById('edit_supervisor_department').value = data.department || '';
                document.getElementById('edit_supervisor_position').value = data.position || '';
            }).catch(err => showToast('Failed to load supervisor: ' + err, 'error'));
    }

    document.getElementById('editSupervisorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fetch('../actions/UpdateSupervisorAjax.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    document.getElementById('editSupervisorModal').classList.add('hidden');
                    showToast('Supervisor updated', 'success');
                    setTimeout(() => location.reload(), 700);
                } else showToast(data.error || 'Failed to update', 'error');
            }).catch(err => showToast('Request failed: ' + err, 'error'));
    });
</script>