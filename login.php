<?php
require_once 'includes/Auth.php';
require_once 'includes/helpers.php';

$auth = new Auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if ($auth->login($username, $password)) {
        redirect('dashboard.php');
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .heading-font {
            font-family: 'Space Grotesk', sans-serif;
        }
        .animated-bg {
            background: linear-gradient(-45deg, #1e293b, #0f172a, #1e3a8a, #312e81);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="animated-bg min-h-screen flex items-center justify-center p-4">
    <!-- Floating shapes -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 float-animation"></div>
        <div class="absolute top-40 right-20 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 float-animation" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-40 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 float-animation" style="animation-delay: 4s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <!-- Logo/Brand -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-white/10 backdrop-blur-sm rounded-2xl mb-4">
                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="heading-font text-4xl font-bold text-white mb-2">Budget-Procurement Management System</h1>
            <p class="text-blue-200 text-lg">Government Procurement Management</p>
        </div>

        <!-- Login Card -->
        <div class="glass-effect rounded-3xl shadow-2xl p-8">
            <h2 class="heading-font text-2xl font-semibold text-slate-800 mb-6">Sign In</h2>
            
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <p class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-slate-700 font-medium mb-2 text-sm">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" name="username" required autofocus
                               class="w-full pl-10 pr-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                               placeholder="Enter your username">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-medium mb-2 text-sm">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" name="password" required
                               class="w-full pl-10 pr-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                               placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 px-6 rounded-xl font-semibold heading-font text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    Sign In
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-8 pt-6 border-t border-slate-200">
                <p class="text-xs text-slate-600 mb-3 font-semibold">Default Credentials (Password: admin123):</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="font-semibold text-slate-700">Administrator</p>
                        <p class="text-slate-500">admin</p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="font-semibold text-slate-700">Budget Officer</p>
                        <p class="text-slate-500">budget_officer</p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="font-semibold text-slate-700">Procurement Officer</p>
                        <p class="text-slate-500">procurement</p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-lg">
                        <p class="font-semibold text-slate-700">Department Head</p>
                        <p class="text-slate-500">dept_head</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-blue-200 text-sm mt-6">
            © <?= date('Y') ?> Budget-Procurement Management System. All rights reserved.
        </p>
    </div>
</body>
</html>