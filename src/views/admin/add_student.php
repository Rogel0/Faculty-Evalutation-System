<?php
include('../config/database.php');
?>

<div class="p-2">
    <div class="overflow-x-auto">
        <?php include('../components/admin/add_student.php') ?>
    </div>
</div>

<!-- XLSX.js for Excel file parsing -->
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<?php
// Cache-busting using file modification time so clients load latest JS during development
$batchUploadPath = __DIR__ . '/../../scripts/admin/batch_upload.js';
$previewUploadPath = __DIR__ . '/../../scripts/admin/preview_batch_upload.js';
$drawerPath = __DIR__ . '/../../scripts/admin/add_student_drawer.js';
$addPairPath = __DIR__ . '/../../scripts/admin/add_pair.js';
$batchV = file_exists($batchUploadPath) ? filemtime($batchUploadPath) : time();
$previewV = file_exists($previewUploadPath) ? filemtime($previewUploadPath) : time();
$drawerV = file_exists($drawerPath) ? filemtime($drawerPath) : time();
$addPairV = file_exists($addPairPath) ? filemtime($addPairPath) : time();
?>
<script src="../scripts/admin/add_student_drawer.js?v=<?php echo $drawerV; ?>"></script>
<script src="../scripts/admin/add_pair.js?v=<?php echo $addPairV; ?>"></script>
<script src="../scripts/admin/batch_upload.js?v=<?php echo $batchV; ?>"></script>
<script src="../scripts/admin/preview_batch_upload.js?v=<?php echo $previewV; ?>"></script>