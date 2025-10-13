<!-- Upload Warnings Modal -->
<div id="uploadWarningsModal" class="fixed inset-0 z-50 flex items-start justify-center p-6" style="background: rgba(0,0,0,0.6);">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl mt-12 overflow-auto">
    <div class="p-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-bold">Upload Warnings</h3>
      <div>
        <button onclick="document.getElementById('uploadWarningsModal').style.display='none'" class="px-3 py-1 bg-gray-200 rounded mr-2">Close</button>
        <form method="POST" action="/faculty_evaluation/src/actions/clear_upload_warnings.php" style="display:inline">
          <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded">Clear</button>
        </form>
      </div>
    </div>
    <div class="p-4">
      <p class="text-sm text-gray-600 mb-4">The following items had issues during the upload. Please review and fix them as needed.</p>
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left">Student ID</th>
              <th class="px-4 py-2 text-left">Warnings</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($warnings as $w): ?>
              <tr class="border-t">
                <td class="px-4 py-2 align-top font-mono text-sm"><?php echo htmlspecialchars($w['student_id']); ?></td>
                <td class="px-4 py-2 text-sm">
                  <ul class="list-disc pl-5">
                    <?php foreach ($w['warnings'] as $msg): ?>
                      <li><?php echo htmlspecialchars($msg); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>