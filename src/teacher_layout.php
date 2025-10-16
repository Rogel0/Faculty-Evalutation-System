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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />
    <title><?php echo $title ?? 'Teacher Dashboard'; ?></title>
</head>

<body>

    <div class="flex min-h-screen bg-gray-100">
        <?php include('components/teacher/sidebar.php'); ?>

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

<?php include __DIR__ . '/components/shared/change_password_modal.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['error'])): ?>
            showToast(<?php echo json_encode($_SESSION['error']); ?>, "error");
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            showToast(<?php echo json_encode($_SESSION['success']); ?>, "success");
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    });
</script>

</html>