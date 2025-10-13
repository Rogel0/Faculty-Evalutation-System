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

<script src="../scripts/admin/add_student_drawer.js"></script>
<script src="../scripts/admin/add_pair.js"></script>
<script src="../scripts/admin/batch_upload.js"></script>
<script src="../scripts/admin/preview_batch_upload.js"></script>