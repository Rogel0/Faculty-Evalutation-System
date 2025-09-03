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
    <link rel="stylesheet" href="styles/loading.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />
    <title><?php echo $pageTitle; ?></title>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-200">
    <?php include('components/toast/spinner.php') ?>
    <div class="min-h-screen flex">
        <div class="hidden lg:flex lg:w-3/5 relative overflow-hidden bg-gradient-to-br from-blue-800 via-indigo-700 to-purple-600">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-800/90 to-purple-600/80 z-10"></div>

            <div class="absolute inset-0 pointer-events-none z-5">
                <div class="absolute w-96 h-96 -top-20 -right-10 rounded-full bg-gradient-radial from-yellow-500/20 to-yellow-500/5 animate-float-slow opacity-60"></div>
                <div class="absolute w-72 h-72 -bottom-15 -left-10 rounded-full bg-gradient-radial from-yellow-500/20 to-yellow-500/5 animate-float-delayed opacity-60"></div>
                <div class="absolute w-48 h-48 top-2/5 left-1/5 rounded-full bg-gradient-radial from-yellow-500/20 to-yellow-500/5 animate-float opacity-60"></div>

                <div class="absolute top-1/6 left-1/6 w-24 h-24 bg-gradient-to-br from-yellow-500 to-yellow-600 opacity-15 animate-spin-slow" style="clip-path: polygon(50% 0%, 0% 100%, 100% 100%)"></div>
                <div class="absolute bottom-1/4 right-1/5 w-20 h-20 bg-gradient-to-br from-amber-500 to-yellow-500 opacity-15 animate-spin-reverse" style="clip-path: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)"></div>
            </div>

            <div class="relative z-20 flex flex-col items-center justify-center w-full p-12 text-center text-white">
                <h1 class="text-5xl xl:text-6xl font-bold mb-6 leading-tight text-white drop-shadow-lg">
                    LYCEUM OF
                    <span class="text-amber-400 font-light ml-2">ALABANG</span>
                </h1>

                <div class="w-24 h-1 bg-amber-400 rounded mb-6"></div>

                <p class="text-yellow-300 text-2xl xl:text-3xl font-light tracking-wider mb-4 drop-shadow">
                    FACULTY EVALUATION SYSTEM
                </p>

                <p class="text-white/80 text-sm max-w-md leading-relaxed mt-4">
                    Empowering educational excellence through comprehensive faculty assessment and development
                </p>
            </div>
        </div>

        <div class="w-full lg:w-2/5 flex items-center justify-center p-8 bg-gradient-to-br from-slate-50 to-slate-200 lg:bg-gradient-to-br lg:from-slate-50 lg:to-slate-200">
            <div class="lg:hidden absolute inset-0 bg-gradient-to-br from-blue-800 via-indigo-700 to-purple-600"></div>

            <div class="relative z-10 w-full max-w-md">
                <div class="text-center mb-8">
                    <h2 class="text-4xl font-bold mb-2 bg-gradient-to-r from-blue-800 to-yellow-600 bg-clip-text text-transparent lg:text-gray-900">
                        Welcome Back
                    </h2>
                    <p class="text-white/80 lg:text-gray-600">Sign in to access your account</p>
                </div>

                <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl border border-black/5 p-8 mb-8">
                    <form id="loginForm" class="space-y-6" method="POST" action="auth/login.php">
                        <div class="form-group">
                            <label for="username" class="block text-gray-700 font-semibold text-sm mb-2">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                                <input
                                    type="username"
                                    id="username"
                                    name="username"
                                    required
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition-all duration-200 bg-white text-gray-900 placeholder-gray-500"
                                    placeholder="Enter your username">
                            </div>
                            <div class="error-message text-red-500 text-xs mt-1 hidden"></div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="block text-gray-700 font-semibold text-sm mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition-all duration-200 bg-white text-gray-900 placeholder-gray-500"
                                    placeholder="Enter your password">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg id="eyeIcon" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="error-message text-red-500 text-xs mt-1 hidden"></div>
                        </div>

                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="rememberMe" name="remember_me" class="w-4 h-4 text-yellow-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500 focus:ring-2">
                                <span class="ml-2 text-sm font-medium text-gray-700">Remember me</span>
                            </label>

                            <a href="#" id="forgotPassword" class="text-sm text-amber-600 hover:text-amber-800 font-medium transition-colors">
                                Forgot password?
                            </a>
                        </div>

                        <button
                            type="submit"
                            id="loginButton"
                            class="w-full bg-gradient-to-r from-amber-500 to-amber-600 text-white py-3 px-6 rounded-xl font-semibold text-base hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-4 focus:ring-amber-500/50 transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Sign In</span>
                        </button>
                    </form>
                </div>

                <div class="text-center">
                    <p class="text-white/80 lg:text-gray-600 text-sm">
                        Don't have an account?
                        <a href="#" id="contactAdmin" class="text-amber-400 lg:text-amber-600 hover:text-amber-300 lg:hover:text-amber-800 font-semibold transition-colors">
                            Contact Administrator
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div id="messageContainer" class="fixed top-4 right-4 z-50"></div>
</body>
<script src="scripts/loading.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
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