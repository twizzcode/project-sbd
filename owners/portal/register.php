<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    
    // Validation
    if (empty($username) || empty($password) || empty($nama_lengkap) || empty($email) || empty($no_telepon)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT owner_id FROM owner WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists. Please choose another.';
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT owner_id FROM owner WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already registered. Please use another email.';
                } else {
                    try {
                        // Create owner profile with credentials
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            INSERT INTO owner (username, password, nama_lengkap, email, no_telepon, alamat, registered_at)
                            VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$username, $hashed_password, $nama_lengkap, $email, $no_telepon, $alamat]);
                        
                        $success = 'Registration successful! You can now login.';
                        
                        // Clear form
                        $username = $nama_lengkap = $email = $no_telepon = $alamat = '';
                        
                    } catch (Exception $e) {
                        throw $e;
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

$page_title = 'Owner Registration';
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
    <div class="max-w-2xl w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
                <i class="fas fa-paw text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">VetClinic</h1>
            <p class="text-gray-600 mt-2">Register as Pet Owner</p>
        </div>

        <!-- Registration Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Create Your Account</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-green-700 text-sm">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
                </p>
                <a href="login.php" class="inline-block mt-3 text-sm text-indigo-600 hover:text-indigo-700 font-semibold">
                    <i class="fas fa-arrow-right mr-1"></i>Go to Login
                </a>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <!-- Account Information -->
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <i class="fas fa-user-circle mr-2"></i>Account Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="username" required
                                   value="<?= htmlspecialchars($username ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Choose a username">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_lengkap" required
                                   value="<?= htmlspecialchars($nama_lengkap ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Your full name">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Min. 6 characters">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="confirm_password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Repeat password">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <i class="fas fa-address-card mr-2"></i>Contact Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" required
                                   value="<?= htmlspecialchars($email ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="your@email.com">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="no_telepon" required
                                   value="<?= htmlspecialchars($no_telepon ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="081234567890">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Address
                        </label>
                        <textarea name="alamat" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Your address (optional)"><?= htmlspecialchars($alamat ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition duration-200 flex items-center justify-center mt-6">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                        Login here
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
