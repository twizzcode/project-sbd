<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'Admin') {
        header('Location: /dashboard/index.php');
    } else {
        header('Location: /owners/portal/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT user_id, username, password, nama_lengkap, email, role, status 
            FROM users 
            WHERE (username = ? OR email = ?) AND status = 'Aktif'
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // For backward compatibility with existing code
            if ($user['role'] === 'Owner') {
                $_SESSION['owner_id'] = $user['user_id'];
                $_SESSION['owner_name'] = $user['nama_lengkap'];
            }
            
            // Update last login
            $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update->execute([$user['user_id']]);
            
            // Redirect berdasarkan role
            if ($user['role'] === 'Admin') {
                header('Location: /appointments/');
            } else {
                header('Location: /owners/portal/');
            }
            exit;
        } else {
            $error = 'Username/Email atau Password salah!';
        }
    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VetClinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-paw text-purple-600 text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">VetClinic Management</h1>
            <p class="text-gray-600">Masuk ke akun Anda</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-user mr-2 text-purple-600"></i>Username atau Email
                    </label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        placeholder="Masukkan username atau email">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-lock mr-2 text-purple-600"></i>Password
                    </label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        placeholder="Masukkan password">
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">Belum punya akun?</p>
                <a href="register.php" class="text-purple-600 hover:text-purple-800 font-semibold">
                    Daftar sebagai Owner <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-white rounded-lg p-4 shadow-md">
            <div class="text-sm text-gray-600 space-y-2">
                <p><i class="fas fa-info-circle text-blue-500 mr-2"></i><strong>Login sebagai:</strong></p>
                <ul class="ml-6 space-y-1">
                    <li><i class="fas fa-user-shield text-red-500 mr-2"></i>Admin untuk kelola klinik</li>
                    <li><i class="fas fa-user text-purple-500 mr-2"></i>Owner untuk kelola hewan peliharaan</li>
                </ul>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="/landing.php" class="text-gray-600 hover:text-purple-600 transition">
                <i class="fas fa-home mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
