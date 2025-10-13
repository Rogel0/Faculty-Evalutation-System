<?php
session_start();
include('config/database.php');
$pageTitle = "Login - Faculty Evaluation System";
include('auth/sessionCheck.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="styles/global.css">
    <link rel="stylesheet" href="styles/loading.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />
    <title><?php echo $pageTitle; ?></title>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-200">
    <?php include('components/toast/spinner.php') ?>
    <div class="min-h-screen flex">
        <div class="hidden lg:flex lg:w-3/5 relative overflow-hidden bg-gradient-to-br from-blue-900 via-indigo-800 to-purple-700">
            <!-- Background Image with Enhanced Styling -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-5"
                style="background-image: url('assets/images/login_bg.jpg'); 
                        filter: brightness(0.8) contrast(1.1) saturate(0.9);">
            </div>

            <!-- Enhanced Gradient Overlay for Better Text Readability -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/85 via-indigo-800/80 to-purple-700/85 z-10"></div>

            <!-- Additional Subtle Overlay for Depth -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-black/10 z-12"></div>

            <!-- Floating Animation Elements with Enhanced Design -->
            <div class="absolute inset-0 pointer-events-none z-15">
                <!-- Large floating orb -->
                <div class="absolute w-96 h-96 -top-20 -right-10 rounded-full bg-gradient-to-br from-amber-400/15 via-yellow-300/10 to-transparent animate-float-slow opacity-70 blur-xl"></div>

                <!-- Medium floating orb -->
                <div class="absolute w-72 h-72 -bottom-15 -left-10 rounded-full bg-gradient-to-br from-yellow-400/12 via-amber-300/8 to-transparent animate-float-delayed opacity-60 blur-lg"></div>

                <!-- Small floating orb -->
                <div class="absolute w-48 h-48 top-2/5 left-1/5 rounded-full bg-gradient-to-br from-yellow-300/10 via-amber-200/6 to-transparent animate-float opacity-50 blur-md"></div>

                <!-- Geometric shapes for visual interest -->
                <div class="absolute top-1/6 left-1/6 w-32 h-32 bg-gradient-to-br from-amber-400/20 to-yellow-500/10 opacity-30 animate-spin-slow blur-sm"
                    style="clip-path: polygon(50% 0%, 0% 100%, 100% 100%); transform-origin: center;"></div>

                <div class="absolute bottom-1/4 right-1/5 w-24 h-24 bg-gradient-to-br from-yellow-400/15 to-amber-500/10 opacity-25 animate-spin-reverse blur-sm"
                    style="clip-path: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%); transform-origin: center;"></div>

                <!-- Additional subtle elements -->
                <div class="absolute top-1/3 right-1/3 w-16 h-16 bg-amber-300/8 rounded-full animate-pulse opacity-40"></div>
                <div class="absolute bottom-1/3 left-1/4 w-20 h-20 bg-yellow-300/6 rounded-full animate-pulse opacity-30" style="animation-delay: 1.5s;"></div>
            </div>

            <!-- Enhanced Text Content with Better Styling -->
            <div class="relative z-20 flex flex-col items-center justify-center w-full p-12 text-center text-white">
                <h1 class="text-5xl xl:text-6xl font-bold mb-6 leading-tight text-white drop-shadow-2xl">
                    LYCEUM OF
                    <span class="text-amber-400 font-light ml-2 drop-shadow-lg">ALABANG</span>
                </h1>

                <div class="w-32 h-1 bg-gradient-to-r from-amber-400 to-yellow-400 rounded-full mb-8 shadow-lg"></div>

                <p class="text-amber-100 text-2xl xl:text-3xl font-light tracking-wider mb-6 drop-shadow-lg">
                    FACULTY EVALUATION SYSTEM
                </p>

                <p class="text-blue-100/90 text-lg max-w-md leading-relaxed font-light tracking-wide">
                    Empowering educational excellence through comprehensive faculty assessment and development
                </p>

                <!-- Simple decorative elements -->
                <div class="mt-8 flex space-x-2">
                    <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                    <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                    <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                </div>
            </div>
        </div>

        <!-- Enhanced Right Side - Login Form -->
        <div class="w-full lg:w-2/5 flex items-center justify-center p-8 bg-gradient-to-br from-gray-50 via-white to-gray-100 relative overflow-hidden">
            <!-- Subtle background pattern for desktop -->
            <div class="hidden lg:block absolute inset-0 opacity-5"
                style="background-image: radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%), 
                        radial-gradient(circle at 80% 20%, rgba(255, 206, 84, 0.3) 0%, transparent 50%), 
                        radial-gradient(circle at 40% 80%, rgba(120, 119, 198, 0.2) 0%, transparent 50%);">
            </div>

            <!-- Mobile background (hidden on desktop) -->
            <div class="lg:hidden absolute inset-0 bg-gradient-to-br from-blue-900 via-indigo-800 to-purple-700"></div>

            <div class="relative z-10 w-full max-w-md">
                <!-- Enhanced Header -->
                <div class="text-center mb-10">
                    <h2 class="text-4xl font-bold mb-3 bg-gradient-to-r from-blue-700 via-indigo-600 to-purple-600 bg-clip-text text-transparent lg:text-gray-800">
                        Welcome Back
                    </h2>
                    <p class="text-white/90 lg:text-gray-600 text-lg font-medium">Sign in to access your account</p>
                </div>

                <!-- Enhanced Form Card -->
                <div class="bg-white/98 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-10 mb-8 lg:shadow-xl">
                    <form id="loginForm" class="space-y-7" method="POST" action="auth/login.php">
                        <!-- Enhanced Username Field -->
                        <div class="form-group">
                            <label for="username" class="block text-gray-700 font-semibold text-sm mb-3 tracking-wide">Username</label>
                            <div class="relative group">
                                <input
                                    type="username"
                                    id="username"
                                    name="username"
                                    required
                                    class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 bg-gray-50/50 text-gray-900 placeholder-gray-400 font-medium hover:bg-white hover:border-gray-300"
                                    placeholder="Enter your username">
                            </div>
                            <div class="error-message text-red-500 text-xs mt-2 hidden font-medium"></div>
                        </div>

                        <!-- Enhanced Password Field -->
                        <div class="form-group">
                            <label for="password" class="block text-gray-700 font-semibold text-sm mb-3 tracking-wide">Password</label>
                            <div class="relative group">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    class="w-full px-4 pr-14 py-4 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 bg-gray-50/50 text-gray-900 placeholder-gray-400 font-medium hover:bg-white hover:border-gray-300"
                                    placeholder="Enter your password">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center group">
                                    <svg id="eyeIcon" class="h-5 w-5 text-gray-400 hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="error-message text-red-500 text-xs mt-2 hidden font-medium"></div>
                        </div>



                        <!-- Enhanced Sign In Button -->
                        <div class="pt-4">
                            <button
                                type="submit"
                                id="loginButton"
                                class="w-full bg-gradient-to-r from-amber-500 via-amber-600 to-orange-500 text-white py-4 px-6 rounded-2xl font-semibold text-lg hover:from-amber-600 hover:via-orange-600 hover:to-orange-600 focus:outline-none focus:ring-4 focus:ring-amber-500/30 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl active:scale-95 flex items-center justify-center">
                                <span class="tracking-wide">Sign In</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <div id="messageContainer" class="fixed top-4 right-4 z-50"></div>
</body>
<script src="scripts/loading.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>
<script src="/faculty_evaluation/src/scripts/toast.js"></script>
<script>
    <?php if (isset($_SESSION['error'])): ?>
        showToast(" <?php echo $_SESSION['error']; ?>", "error");
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        showToast("<?php echo $_SESSION['success']; ?>", "success");
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
</script>

</html>