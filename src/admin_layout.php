<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['errorLogin'])) {
    // errorLogin present; handled via UI toast later
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- <link rel="shortcut icon" href="../images/BG_LOGIN.png" type="image/x-icon"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />
    <link rel="stylesheet" href="/faculty_evaluation/src/styles/global.css">
    <title><?php echo $title ?? 'Dashboard'; ?></title>
</head>

<body>
    <div class="flex h-screen bg-gray-100 overflow-y-hidden">
        <?php include('components/admin/sidebar.php'); ?>

        <div class="flex-1 flex flex-col">
            <main class="flex-1 p-6 overflow-auto">
                <?php
                if (isset($content) && file_exists($content)) {
                    include($content);
                } else {
                    echo '<p>Content not found.</p>';
                }
                ?>
            </main>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>
<script src="/faculty_evaluation/src/scripts/toast.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['errorLogin'])): ?>
        showToast(<?php echo json_encode($_SESSION['errorLogin']); ?>, "error");
        <?php unset($_SESSION['errorLogin']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        showToast(<?php echo json_encode($_SESSION['error']); ?>, "error");
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        showToast(<?php echo json_encode($_SESSION['success']); ?>, "success");
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['warning'])): ?>
        showToast(<?php echo json_encode($_SESSION['warning']); ?>, 'error');
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>
  });
</script>

<?php
// Include the modal HTML (rendered when upload_warnings exists in session)
if (isset($_SESSION['upload_warnings']) && !empty($_SESSION['upload_warnings'])) {
    $warnings = $_SESSION['upload_warnings'];
    include __DIR__ . '/components/admin/upload_warnings_modal.php';
}
?>

</html>