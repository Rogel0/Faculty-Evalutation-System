<div class="overflow-x-auto border border-gray-200 bg-white shadow-sm rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Student ID</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">First Name</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Last Name</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Email</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Year Level</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Course/Strand</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <?php
            // Pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $records_per_page = 10;
            $offset = ($page - 1) * $records_per_page;

            // Get optional search query
            $search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $search_esc = $conn->real_escape_string($search_q);

            // Get total records for pagination (with optional search)
            if ($search_q !== '') {
                $total_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'student' AND (student_id LIKE '%$search_esc%' OR firstname LIKE '%$search_esc%' OR lastname LIKE '%$search_esc%' OR email LIKE '%$search_esc%')";
            } else {
                $total_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'student'";
            }
            $total_result = $conn->query($total_query);
            $total_records = $total_result->fetch_assoc()['count'];
            $total_pages = ceil($total_records / $records_per_page);

            // Get records for current page (with optional search)
            if ($search_q !== '') {
                $result = $conn->query("SELECT id, student_id, firstname, lastname, email, grade_level, strand FROM users WHERE user_type = 'student' AND (student_id LIKE '%$search_esc%' OR firstname LIKE '%$search_esc%' OR lastname LIKE '%$search_esc%' OR email LIKE '%$search_esc%') ORDER BY id DESC LIMIT $offset, $records_per_page");
            } else {
                $result = $conn->query("SELECT id, student_id, firstname, lastname, email, grade_level, strand FROM users WHERE user_type = 'student' ORDER BY id DESC LIMIT $offset, $records_per_page");
            }
            if ($result && $result->num_rows > 0):
                $rowIndex = 0;
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-200 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['firstname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['lastname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold border-b border-gray-100 text-center">
                            <?php echo $row['grade_level'] ? 'Grade ' . htmlspecialchars($row['grade_level']) : 'N/A'; ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold border-b border-gray-100 text-center">
                            <?php
                            $strand = htmlspecialchars($row['strand']);
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
                            echo isset($strandLabels[$strand]) ? $strand : $strand;
                            ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center border-b border-gray-100">
                            <div class="flex items-center justify-center gap-2">
                                <a href="#" class="inline-flex items-center px-3 py-1 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold text-xs transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.293-6.293a1 1 0 011.414 0l1.586 1.586a1 1 0 010 1.414L11 17H9v-2z" />
                                    </svg>
                                    Edit
                                </a>
                                <a href="#" class="inline-flex items-center px-3 py-1 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold text-xs transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php $rowIndex++;
                endwhile;
            else: ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No students added yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium"><?php echo $offset + 1 ?></span>
                        to
                        <span class="font-medium"><?php echo min($offset + $records_per_page, $total_records) ?></span>
                        of
                        <span class="font-medium"><?php echo $total_records ?></span>
                        results
                    </p>
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?module=add_student&page=<?php echo ($page - 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?module=add_student&page=<?php echo ($page + 1) ?><?php echo $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>