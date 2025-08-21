<div class="overflow-x-auto border border-gray-200 bg-white shadow-sm rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Student ID</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">First Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Last Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date Added</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <?php
            $result = $conn->query("SELECT student_id, firstname, lastname, email, created_at FROM tbluser_students ORDER BY created_at DESC LIMIT 10");
            if ($result && $result->num_rows > 0):
                $rowIndex = 0;
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo $rowIndex % 2 === 0 ? 'bg-gray-50' : 'bg-white'; ?> hover:bg-yellow-50 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['firstname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['lastname']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php $rowIndex++;
                endwhile;
            else: ?>
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">No students added yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>