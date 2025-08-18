<?php
include(__DIR__ . '/../../config/database.php');
if (!isset($currentRoute)) {
    header('Location: ../../router/admin.php?module=dashboard');
    exit();
}
include(__DIR__ . '/../../components/common/admin/dashboard.php');
