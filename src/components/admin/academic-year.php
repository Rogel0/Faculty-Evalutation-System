<?php
// Academic Year management component with enhanced UI
include_once('../config/database.php');

// Handle session messages - will be shown via toast
$successMsg = $_SESSION['success'] ?? null;
$errorMsg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Fetch existing academic years
$rows = [];
$anyActiveYear = false;
try {
    $stmt = $conn->prepare("SELECT id, year, semester, start_date, end_date, is_active FROM school_years ORDER BY start_date DESC, id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
        if ($r['is_active']) $anyActiveYear = true;
    }
    $stmt->close();
} catch (Exception $e) {
    $errorMsg = 'Error loading academic years: ' . $e->getMessage();
}
?>

<!-- Main Card -->
<div class="overflow-x-auto border border-gray-200 bg-white shadow-sm rounded-lg">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Academic Years & Semesters</h2>
            <p class="text-gray-600 text-sm mt-1">Manage evaluation periods and schedules</p>
        </div>
        <button id="addYearBtn" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold text-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Academic Year
        </button>
    </div>

    <!-- Table -->
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Academic Year</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Semester</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Period</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Duration</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No academic years found</td>
                </tr>
            <?php else: ?>
                <?php $rowIndex = 0;
                foreach ($rows as $r): ?>
                    <?php
                    $startDate = new DateTime($r['start_date']);
                    $endDate = new DateTime($r['end_date']);
                    $now = new DateTime();
                    $duration = $startDate->diff($endDate)->days . ' days';
                    $isActive = $r['is_active'];
                    $isCurrent = $now >= $startDate && $now <= $endDate;
                    ?>
                    <tr class="<?php echo $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-200 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold border-b border-gray-100 text-center">
                            <?php echo htmlspecialchars($r['year']); ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center">
                            <?php echo htmlspecialchars($r['semester']); ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center">
                            <div><?php echo $startDate->format('M d, Y'); ?></div>
                            <div class="text-xs text-gray-500"><?php echo $endDate->format('M d, Y'); ?></div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold border-b border-gray-100 text-center">
                            <?php echo $duration; ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center border-b border-gray-100">
                            <?php if ($isActive): ?>
                                <?php if ($isCurrent): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active - Current
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Active
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center border-b border-gray-100">
                            <div class="flex items-center justify-center gap-2">

                                <button class="editYearBtn inline-flex items-center px-3 py-1 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold text-xs transition"
                                    data-id="<?php echo $r['id']; ?>" <?php if ($anyActiveYear && !$isActive) echo 'disabled style="opacity:0.5;cursor:not-allowed"'; ?>>
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.293-6.293a1 1 0 011.414 0l1.586 1.586a1 1 0 010 1.414L11 17H9v-2z" />
                                    </svg>
                                    Edit
                                </button>
                                <form method="POST" action="../actions/DeleteAcademicYear.php" style="display:inline-block;"
                                    onsubmit="return confirm('Are you sure you want to delete this academic year?');">
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="inline-flex items-center px-3 py-1 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold text-xs transition" <?php if ($anyActiveYear && !$isActive) echo 'disabled style="opacity:0.5;cursor:not-allowed"'; ?>>
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php $rowIndex++;
                endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Simple Modal -->
<div id="yearModal" style="display:none; background: rgba(0, 0, 0, 0.4);" class="fixed inset-0 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Add Academic Year</h3>
            <button type="button" id="ay_cancel" class="px-3 py-1 rounded hover:bg-gray-100 transition text-gray-600">×</button>
        </div>

        <!-- Modal Body -->
        <form id="yearForm" method="POST" action="../actions/AddAcademicYear.php" class="p-6 space-y-4">
            <input type="hidden" name="id" id="ay_id">

            <!-- Academic Year -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                <input type="text" name="year" id="ay_year"
                    class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm"
                    placeholder="e.g., 2025-2026" required>
            </div>

            <!-- Semester -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                <select name="semester" id="ay_semester"
                    class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm">
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="Summer">Summer</option>
                    <option value="Midyear">Midyear</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" id="ay_start_date"
                        class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" id="ay_end_date"
                        class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-200 px-4 py-2.5 shadow-sm"
                        required>
                </div>
            </div>

            <!-- Active Status -->
            <!-- Removed checkbox - using toggle button in table instead -->

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit"
                    id="ay_submit"
                    class="px-4 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-lg font-semibold transition">
                    <span id="submitBtnText">Save Academic Year</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal functionality
    const modal = document.getElementById('yearModal');
    const addBtn = document.getElementById('addYearBtn');
    const cancelBtn = document.getElementById('ay_cancel');
    const form = document.getElementById('yearForm');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtnText = document.getElementById('submitBtnText');

    // Open modal for adding - block when an active year already exists
    const anyActiveYear = <?php echo $anyActiveYear ? 'true' : 'false'; ?>;
    addBtn.addEventListener('click', function() {
        if (anyActiveYear) {
            showToast('An active academic year already exists. Deactivate it before adding a new one.', 'error');
            return;
        }
        resetForm();
        modalTitle.textContent = 'Add Academic Year';
        submitBtnText.textContent = 'Save Academic Year';
        document.getElementById('ay_submit').disabled = false;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    // Close modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    cancelBtn.addEventListener('click', closeModal);

    // Close modal on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Reset form
    function resetForm() {
        form.reset();
        document.getElementById('ay_id').value = '';
    }

    // Edit functionality
    document.querySelectorAll('.editYearBtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            modalTitle.textContent = 'Edit Academic Year';
            submitBtnText.textContent = 'Update Academic Year';

            // Fetch data
            fetch('../actions/GetAcademicYear.php?id=' + id)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('ay_id').value = data.id;
                    document.getElementById('ay_year').value = data.year;
                    document.getElementById('ay_semester').value = data.semester;
                    document.getElementById('ay_start_date').value = data.start_date;
                    document.getElementById('ay_end_date').value = data.end_date;
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                })
                .catch(err => {
                    showToast('Failed to load academic year data', 'error');
                    console.error(err);
                });
        });
    });

    // Form validation and AJAX submit
    form.addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('ay_start_date').value);
        const endDate = new Date(document.getElementById('ay_end_date').value);

        if (endDate <= startDate) {
            e.preventDefault();
            showToast('End date must be after start date', 'error');
            return false;
        }

        // If JS is enabled, submit via fetch to show toast immediately and avoid full reload
        if (window.fetch) {
            e.preventDefault();
            submitBtnText.textContent = 'Saving...';
            const fd = new FormData(form);
            fetch('../actions/AddAcademicYear.php', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(r => r.json()).then(result => {
                if (result.success) {
                    showToast(result.message || 'Academic year saved', 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(result.error || 'Failed to save academic year', 'error');
                    submitBtnText.textContent = 'Save Academic Year';
                }
            }).catch(err => {
                showToast('Request failed: ' + err, 'error');
                submitBtnText.textContent = 'Save Academic Year';
            });
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });

    // Show toast messages
    <?php if ($successMsg): ?>
        showToast("<?php echo addslashes($successMsg); ?>", "success");
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        showToast("<?php echo addslashes($errorMsg); ?>", "error");
    <?php endif; ?>
</script>