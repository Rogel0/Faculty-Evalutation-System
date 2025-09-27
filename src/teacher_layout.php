<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['errorLogin'])) {
    echo "<script>console.log('Error Login: " . $_SESSION['errorLogin'] . "');</script>";
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

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="/faculty_evaluation/src/scripts/toast.js"></script>

<script>
    <?php if (isset($_SESSION['error'])): ?>
        showToast("<?php echo $_SESSION['error']; ?>", "error");
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        showToast("<?php echo $_SESSION['success']; ?>", "success");
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
</script>

</html>