<?php
include(__DIR__ . '/../../config/database.php');
if (!isset($currentRoute)) {
    header('Location: ../../router/admin.php?module=questionnaire');
    exit();
}
include(__DIR__ . '/../../components/common/admin/questionnaire.php');
