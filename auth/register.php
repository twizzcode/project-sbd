<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: /owners/portal/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $no_telepon = $_POST['no_telepon'];
    $alamat = $_POST['alamat'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek username sudah ada
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE username = '$username' OR email = '$email'");
        
        if (mysqli_num_rows($check) > 0) {
            $error = 'Username atau Email sudah terdaftar!';
        } else {
            // Insert user baru
            mysqli_query($conn, "INSERT INTO users (username, email, password, nama_lengkap, role, no_telepon, alamat, status)
                VALUES ('$username', '$email', '$password', '$nama_lengkap', 'Owner', '$no_telepon', '$alamat', 'Aktif')");
            
            $success = 'Registrasi berhasil! Silakan login.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Owner - VetClinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 min-h-screen py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-user-plus text-purple-600 text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Daftar Akun Owner</h1>
            <p class="text-gray-600">Kelola hewan peliharaan Anda dengan mudah</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
                <a href="login.php" class="font-bold underline ml-2">Login sekarang</a>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user mr-2 text-purple-600"></i>Nama Lengkap *
                        </label>
                        <input type="text" name="nama_lengkap" required
                            value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="John Doe">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-envelope mr-2 text-purple-600"></i>Email *
                        </label>
                        <input type="email" name="email" required
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="john@example.com">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user-circle mr-2 text-purple-600"></i>Username *
                        </label>
                        <input type="text" name="username" required
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="johndoe">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-phone mr-2 text-purple-600"></i>No. Telepon
                        </label>
                        <input type="tel" name="no_telepon"
                            value="<?= htmlspecialchars($_POST['no_telepon'] ?? '') ?>"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="081234567890">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>Alamat
                    </label>
                    <textarea name="alamat" rows="3"
                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500"
                        placeholder="Jl. Contoh No. 123"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock mr-2 text-purple-600"></i>Password *
                        </label>
                        <input type="password" name="password" required minlength="6"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="Min. 6 karakter">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock mr-2 text-purple-600"></i>Konfirmasi Password *
                        </label>
                        <input type="password" name="confirm_password" required minlength="6"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="Ulangi password">
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">Sudah punya akun?</p>
                <a href="login.php" class="text-purple-600 hover:text-purple-800 font-semibold">
                    Login di sini <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="/landing.php" class="text-gray-600 hover:text-purple-600 transition">
                <i class="fas fa-home mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
