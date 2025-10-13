<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$warnings = $_SESSION['upload_warnings'] ?? [];
?>

<div class="max-w-4xl mx-auto mt-8">
  <h2 class="text-2xl font-semibold mb-4">Upload Warnings</h2>
  <?php if (empty($warnings)): ?>
    <div class="p-4 bg-green-50 border border-green-200 rounded">No upload warnings found.</div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-500">Student ID</th>
            <th class="px-6 py-3 border-b text-left text-sm font-medium text-gray-500">Warnings</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($warnings as $w): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($w['student_id']); ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars(implode('; ', $w['warnings'])); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="mt-4">
      <a href="/faculty_evaluation/src/actions/clear_upload_warnings.php" class="px-4 py-2 bg-red-600 text-white rounded">Clear Warnings</a>
    </div>
  <?php endif; ?>
</div>