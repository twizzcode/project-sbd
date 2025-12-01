<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Pemilik Hewan';
$errors = [];

// Get owner ID
$owner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get owner data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'Owner'");
$stmt->execute([$owner_id]);
$owner = $stmt->fetch();

if (!$owner) {
    $_SESSION['error'] = 'Data pemilik tidak ditemukan';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid token');
    }

    // Get and sanitize input
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $alamat = clean_input($_POST['alamat']);
    $no_telepon = clean_input($_POST['no_telepon']);
    $email = clean_input($_POST['email']);
    $catatan = clean_input($_POST['catatan']);

    // Validate required fields
    if (empty($nama_lengkap)) {
        $errors[] = 'Nama lengkap wajib diisi';
    }

    if (empty($no_telepon)) {
        $errors[] = 'Nomor telepon wajib diisi';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $no_telepon)) {
        $errors[] = 'Format nomor telepon tidak valid';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }

    // Check if email already exists (except for current owner)
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $owner_id]);
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Email sudah terdaftar';
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET nama_lengkap = ?, 
                    alamat = ?, 
                    no_telepon = ?, 
                    email = ?
                WHERE user_id = ? AND role = 'Owner'
            ");
            
            $stmt->execute([
                $nama_lengkap,
                $alamat,
                $no_telepon,
                $email,
                $owner_id
            ]);
            
            // Set success message and redirect
            $_SESSION['success'] = 'Data pemilik berhasil diperbarui';
            header('Location: index.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Pemilik Hewan</h2>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama_lengkap" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   value="<?php echo htmlspecialchars($owner['nama_lengkap']); ?>">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Alamat
            </label>
            <textarea name="alamat" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            ><?php echo htmlspecialchars($owner['alamat'] ?? ''); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    No. Telepon <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="no_telepon" required pattern="[0-9]{10,15}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($owner['no_telepon']); ?>">
                <p class="text-xs text-gray-500 mt-1">Format: 10-15 digit angka</p>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Email
                </label>
                <input type="email" name="email"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($owner['email'] ?? ''); ?>">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Catatan
            </label>
            <textarea name="catatan" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            ><?php echo htmlspecialchars($owner['catatan'] ?? ''); ?></textarea>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i> Simpan
            </button>
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-times mr-2"></i> Batal
            </a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>