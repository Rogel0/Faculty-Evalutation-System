<?php

/**
 * Payment Protection System
 * Commissioned Project Protection
 */

class PaymentProtection
{

    /**
     * Check if payment protection should be active
     * Returns true if system should be locked
     */
    public static function isLocked()
    {
        // Check for override file first
        $overrideFile = __DIR__ . '/payment_override.php';
        if (file_exists($overrideFile)) {
            return false; // Override active, don't lock
        }

        // Set timezone to Philippines
        date_default_timezone_set('Asia/Manila');

        // Lock date: September 9, 2025 (day after Monday September 8)
        $lockDate = new DateTime('2025-09-29 00:00:00');
        $currentDate = new DateTime();

        // Check if current date is on or after the lock date
        return $currentDate >= $lockDate;
    }

    /**
     * Display the payment protection message
     */
    public static function showPaymentMessage()
    {
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Faculty Evaluation System - Contact Developer</title>
            <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
            <style>
                @keyframes fade-in {
                    0% {
                        opacity: 0;
                        transform: translateY(20px);
                    }

                    100% {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .fade-in-animation {
                    animation: fade-in 0.8s ease-out;
                }
            </style>
        </head>

        <body class="min-h-screen bg-gradient-to-br from-blue-50 via-slate-50 to-gray-50 flex items-center justify-center p-4">
            <div class="max-w-lg mx-auto text-center fade-in-animation">

                <!-- Simple Icon -->
                <div class="mb-8">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Simple Message -->
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200">
                    <h1 class="text-2xl font-bold mb-4 text-gray-800">
                        Please Contact the Developer
                    </h1>

                    <div class="w-16 h-0.5 bg-blue-500 rounded-full mx-auto mb-6"></div>

                    <p class="text-gray-600 mb-8 leading-relaxed">
                        To continue using the system, please get in touch with the developer.
                    </p>
                </div>

                <!-- Simple project info -->
                <div class="mt-6 text-xs text-gray-400 text-center">
                    <p>Faculty Evaluation System - Lyceum of Alabang</p>
                </div>
            </div>
        </body>

        </html>
<?php
        exit();
    }

    /**
     * Get days remaining until lock (for admin notification)
     */
    public static function getDaysUntilLock()
    {
        date_default_timezone_set('Asia/Manila');
        $lockDate = new DateTime('2025-09-09 00:00:00');
        $currentDate = new DateTime();

        if ($currentDate >= $lockDate) {
            return 0; // Already locked
        }

        $interval = $currentDate->diff($lockDate);
        return $interval->days;
    }
}
