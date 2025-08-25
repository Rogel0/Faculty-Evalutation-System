<div class="overflow-x-auto border border-gray-200 bg-white shadow-sm rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Student ID</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">First Name</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Last Name</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Email</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Course</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <?php
            $result = $conn->query("SELECT id, firstname, lastname, email, course FROM users WHERE user_type = 'student' ORDER BY id DESC LIMIT 10");
            if ($result && $result->num_rows > 0):
                $rowIndex = 0;
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-200 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['firstname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['lastname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-semibold border-b border-gray-100 text-center"><?php echo htmlspecialchars($row['course']); ?></td>
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
</div>