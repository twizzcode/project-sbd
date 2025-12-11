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

// Check if owner has pets
$result = mysqli_query($conn, "SELECT COUNT(*) FROM pet WHERE owner_id = '$owner_id'");

$pet_count = mysqli_fetch_row($result)[0];

// Check if owner has appointments
$result = mysqli_query($conn, "SELECT COUNT(*) FROM appointment WHERE owner_id = '$owner_id'");

$appointment_count = mysqli_fetch_row($result)[0];

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Soft delete: Set status to Nonaktif
        $result = mysqli_query($conn, "UPDATE users SET status = 'Nonaktif' WHERE user_id = '$owner_id'");
        
        if (!$result) {
            throw new Exception(mysqli_error($conn));
        }
        
        $_SESSION['success'] = "Pemilik hewan berhasil dinonaktifkan";
        header("Location: index.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    } 
}

include '../../includes/header.php';
?>

<div class="container max-w-2xl mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Hapus Pemilik Hewan</h2>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Confirmation Card -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Konfirmasi Penghapusan</h3>
            <p class="text-gray-600">Apakah Anda yakin ingin menonaktifkan pemilik hewan ini?</p>
        </div>

        <!-- Owner Info -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                    <?php echo strtoupper(substr($owner['nama_lengkap'], 0, 1)); ?>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($owner['nama_lengkap']); ?></h4>
                    <p class="text-sm text-gray-600">@<?php echo htmlspecialchars($owner['username']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($owner['email']); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                <div>
                    <p class="text-sm text-gray-600">Total Hewan</p>
                    <p class="text-lg font-bold text-gray-800"><?php echo $pet_count; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Janji Temu</p>
                    <p class="text-lg font-bold text-gray-800"><?php echo $appointment_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Warning -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-yellow-600"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-yellow-800 mb-1">Informasi Penting:</h4>
                    <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                        <li>Akun akan dinonaktifkan (soft delete)</li>
                        <li>Pemilik tidak akan bisa login ke sistem</li>
                        <li>Data hewan dan riwayat medis akan tetap tersimpan</li>
                        <li>Akun dapat diaktifkan kembali melalui menu Edit</li>
                        <?php if ($pet_count > 0): ?>
                            <li class="font-semibold">Pemilik ini memiliki <?php echo $pet_count; ?> hewan terdaftar</li>
                        <?php endif; ?>
                        <?php if ($appointment_count > 0): ?>
                            <li class="font-semibold">Terdapat <?php echo $appointment_count; ?> janji temu</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Confirmation Form -->
        <form method="POST" class="space-y-4">
            <div class="flex justify-end gap-4">
                <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" name="confirm_delete" value="1" 
                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg"
                        onclick="return confirm('Apakah Anda benar-benar yakin ingin menonaktifkan pemilik ini?')">
                    <i class="fas fa-trash mr-2"></i> Ya, Nonaktifkan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
