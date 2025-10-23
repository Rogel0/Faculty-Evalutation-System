<?php
// Minimal supervisor dashboard view
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Supervisor Dashboard</h1>

    <p>Welcome, <?php echo htmlspecialchars($_SESSION['displayName'] ?? ($_SESSION['firstname'] ?? 'Supervisor')); ?>.</p>

    <div class="mt-6">
        <p class="text-sm text-slate-600">This area is for supervisor-specific tools and reports. Add modules as needed.</p>
    </div>
</div>