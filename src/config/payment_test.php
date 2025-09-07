<?php
/**
 * Payment Protection Test
 * Use this file to test if the payment protection system is working
 */

require_once('payment_protection.php');

// Set timezone to Philippines for testing
date_default_timezone_set('Asia/Manila');

echo "<h2>Payment Protection System Test</h2>";
echo "<p><strong>Current Time (PH):</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Lock Date:</strong> September 9, 2025 00:00:00 PH Time</p>";

$isLocked = PaymentProtection::isLocked();
$daysUntilLock = PaymentProtection::getDaysUntilLock();

echo "<p><strong>System Status:</strong> " . ($isLocked ? '🔒 LOCKED' : '🔓 UNLOCKED') . "</p>";

if (!$isLocked) {
    echo "<p><strong>Days Until Lock:</strong> " . $daysUntilLock . " day(s)</p>";
    echo "<p><strong>Warning Level:</strong> ";
    if ($daysUntilLock <= 1) {
        echo "🔴 CRITICAL (Lock imminent!)";
    } elseif ($daysUntilLock <= 3) {
        echo "🟠 HIGH (Lock very soon)";
    } elseif ($daysUntilLock <= 7) {
        echo "🟡 MEDIUM (Lock approaching)";
    } else {
        echo "🟢 LOW (Lock not soon)";
    }
    echo "</p>";
} else {
    echo "<p style='color: red;'><strong>⚠️ SYSTEM IS LOCKED - Payment required to continue</strong></p>";
}

// Check for override file
$overrideFile = __DIR__ . '/payment_override.php';
if (file_exists($overrideFile)) {
    echo "<p style='color: blue;'><strong>ℹ️ Override file exists - Protection is disabled</strong></p>";
} else {
    echo "<p><strong>Override Status:</strong> No override file found</p>";
    echo "<p><em>To temporarily disable protection, create 'payment_override.php' in the config folder</em></p>";
}

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<p><strong>To disable protection:</strong> Create a file named 'payment_override.php' in this folder</p>";
echo "<p><strong>To enable protection:</strong> Delete the 'payment_override.php' file if it exists</p>";
echo "<p><strong>To change lock date:</strong> Edit the date in payment_protection.php</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; }
h2 { color: #333; }
p { margin: 10px 0; }
strong { font-weight: bold; }
hr { margin: 20px 0; border: none; height: 1px; background: #ddd; }
</style>
