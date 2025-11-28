<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // Find owner by username
            $stmt = $pdo->prepare("
                SELECT owner_id, username, password, nama_lengkap, email
                FROM owner
                WHERE username = ?
            ");
            $stmt->execute([$username]);
            $owner = $stmt->fetch();
            
            if ($owner && password_verify($password, $owner['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session
                $_SESSION['user_id'] = $owner['owner_id'];
                $_SESSION['username'] = $owner['username'];
                $_SESSION['nama_lengkap'] = $owner['nama_lengkap'];
                $_SESSION['role'] = 'Owner';
                $_SESSION['owner_id'] = $owner['owner_id'];
                $_SESSION['owner_name'] = $owner['nama_lengkap'];
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE owner SET last_login = NOW() WHERE owner_id = ?");
                $stmt->execute([$owner['owner_id']]);
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}

$page_title = 'Owner Portal Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - VetClinic</title>
    <script>
        // Suppress Tailwind CDN warning - MUST RUN FIRST
        (function() {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                const msg = args[0]?.toString() || '';
                if (msg.includes('cdn.tailwindcss.com') || msg.includes('should not be used in production')) {
                    return;
                }
                originalWarn.apply(console, args);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
                <i class="fas fa-paw text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">VetClinic</h1>
            <p class="text-gray-600 mt-2">Pet Owner Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Welcome Back!</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input type="text" name="username" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Enter your username">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Enter your password">
                </div>

                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login to Portal
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="text-center mb-4">
                    <p class="text-sm text-gray-600">
                        Don't have an account? 
                        <a href="register.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                            <i class="fas fa-user-plus mr-1"></i>Register here
                        </a>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Staff or Doctor? 
                        <a href="/auth/login.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                            Login here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
