<?php
// include database config (resolve relative to src/config)
include_once(__DIR__ . '/../../../config/database.php');
?>

<div class="overflow-x-auto border border-gray-200 bg-white shadow-sm rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Code</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Name</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Description</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <?php
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $records_per_page = 15;
            $offset = ($page - 1) * $records_per_page;
            $search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $search_esc = $conn->real_escape_string($search_q);

            if ($search_q !== '') {
                $total_query = "SELECT COUNT(*) as count FROM subjects WHERE subject_name LIKE '%$search_esc%' OR subject_code LIKE '%$search_esc%'";
                $result = $conn->query("SELECT id, subject_code, subject_name, description FROM subjects WHERE subject_name LIKE '%$search_esc%' OR subject_code LIKE '%$search_esc%' ORDER BY subject_name LIMIT $offset, $records_per_page");
            } else {
                $total_query = "SELECT COUNT(*) as count FROM subjects";
                $result = $conn->query("SELECT id, subject_code, subject_name, description FROM subjects ORDER BY subject_name LIMIT $offset, $records_per_page");
            }

            $total_result = $conn->query($total_query);
            $total_records = $total_result ? (int)$total_result->fetch_assoc()['count'] : 0;
            $total_pages = max(1, ceil($total_records / $records_per_page));

            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-b border-gray-100"><?php echo htmlspecialchars($row['subject_code']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 border-b border-gray-100 text-left"><?php echo htmlspecialchars($row['description']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-center border-b border-gray-100">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Future: edit link -->
                                <form action="../actions/DeleteSubject.php" method="POST" onsubmit="return confirm('Delete this subject?');">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-red-500 text-white hover:bg-red-600 text-xs">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">No subjects added yet.</td>
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
                    <?php if ($page > 1): ?><a href="?module=add_subject&page=<?php echo ($page - 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Previous</a><?php endif; ?>
                    <?php if ($page < $total_pages): ?><a href="?module=add_subject&page=<?php echo ($page + 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Next</a><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>