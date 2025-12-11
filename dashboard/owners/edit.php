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

$page_title = 'Edit Pemilik Hewan';
$errors = [];

// Get owner ID
$owner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$owner_id) {
    $_SESSION['error'] = "ID pemilik tidak valid";
    header("Location: index.php");
    exit;
}

// Get owner data
$result = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$owner_id' AND role = 'Owner'");

$owner = mysqli_fetch_assoc($result);

if (!$owner) {
    $_SESSION['error'] = "Pemilik tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $status = $_POST['status'] ?? 'Aktif';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap wajib diisi";
    }
    
    // Check if email already used by other user
    if (empty($errors)) {
        $email_check = mysqli_real_escape_string($conn, $email);
        $result = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email_check' AND user_id != '$owner_id'");
        
        if (mysqli_fetch_assoc($result)) {
            $errors[] = "Email sudah digunakan oleh pengguna lain";
        }
    }
    
    // Validate password if provided
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter";
        }
        if ($password !== $password_confirm) {
            $errors[] = "Konfirmasi password tidak cocok";
        }
    }
    
    // Update if no errors
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                // Update with new password
                $result = mysqli_query($conn, "
                    UPDATE users 
                    SET email = '$email', nama_lengkap = '$nama_lengkap', no_telepon = '$no_telepon', alamat = '$alamat', status = '$status', password = '$password'
                    WHERE user_id = '$owner_id'
                ");
                
            } else {
                // Update without changing password
                $result = mysqli_query($conn, "
                    UPDATE users 
                    SET email = '$email', nama_lengkap = '$nama_lengkap', no_telepon = '$no_telepon', alamat = '$alamat', status = '$status'
                    WHERE user_id = '$owner_id'
                ");
                
            }
            
            $_SESSION['success'] = "Data pemilik berhasil diperbarui";
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container max-w-4xl mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Pemilik Hewan</h2>
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
                            Username
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($owner['username']); ?>" disabled
                               class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Username tidak dapat diubah</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-600">*</span>
                        </label>
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? $owner['email']); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="email@example.com">
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i> Ganti Password (Kosongkan jika tidak ingin mengubah)
                    </label>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru
                        </label>
                        <input type="password" name="password"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Minimal 6 karakter">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password Baru
                        </label>
                        <input type="password" name="password_confirm"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ulangi password">
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pribadi</h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" required
                               value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? $owner['nama_lengkap']); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Nama lengkap pemilik">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Telepon
                        </label>
                        <input type="text" name="no_telepon"
                               value="<?php echo htmlspecialchars($_POST['no_telepon'] ?? $owner['no_telepon'] ?? ''); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="08123456789">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat
                        </label>
                        <textarea name="alamat" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Alamat lengkap"><?php echo htmlspecialchars($_POST['alamat'] ?? $owner['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Akun</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-600">*</span>
                    </label>
                    <select name="status" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Aktif" <?php echo ($owner['status'] === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Nonaktif" <?php echo ($owner['status'] === 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Status Nonaktif akan memblokir akses pengguna</p>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-4 pt-6 border-t">
                <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
