<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

$page_title = 'Tambah Pemilik Hewan';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nama_lengkap = clean_input($_POST['nama_lengkap'] ?? '');
    $no_telepon = clean_input($_POST['no_telepon'] ?? '');
    $alamat = clean_input($_POST['alamat'] ?? '');
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username wajib diisi";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh huruf, angka, dan underscore";
    }
    
    if (empty($email)) {
        $errors[] = "Email wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password wajib diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Konfirmasi password tidak cocok";
    }
    
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap wajib diisi";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "Username sudah digunakan";
        }
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah digunakan";
        }
    }
    
    // Insert if no errors
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, nama_lengkap, no_telepon, alamat, role, status)
                VALUES (?, ?, ?, ?, ?, ?, 'Owner', 'Aktif')
            ");
            
            $stmt->execute([
                $username,
                $email,
                $hashed_password,
                $nama_lengkap,
                $no_telepon,
                $alamat
            ]);
            
            $_SESSION['success'] = "Pemilik hewan berhasil ditambahkan";
            header("Location: index.php");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Gagal menambahkan pemilik: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container max-w-4xl mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Pemilik Hewan</h2>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="" method="POST" class="space-y-6">
            <!-- Account Information -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Akun</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Username <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="username" required
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="username123">
                        <p class="text-xs text-gray-500 mt-1">Huruf, angka, dan underscore saja</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-600">*</span>
                        </label>
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="email@example.com">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Minimal 6 karakter">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" name="password_confirm" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ulangi password">
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pribadi</h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" required
                               value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Nama lengkap pemilik">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Telepon
                        </label>
                        <input type="text" name="no_telepon"
                               value="<?php echo htmlspecialchars($_POST['no_telepon'] ?? ''); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="08123456789">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat
                        </label>
                        <textarea name="alamat" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Alamat lengkap"><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-4 pt-6 border-t">
                <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
