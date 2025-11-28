<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

// Check role authorization
if (!in_array($_SESSION['role'], ['Admin', 'Inventory', 'Staff'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut";
    header("Location: index.php");
    exit;
}

$page_title = "Tambah Dokter Hewan";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_dokter' => $_POST['nama_dokter'] ?? '',
        'no_lisensi' => $_POST['no_lisensi'] ?? '',
        'spesialisasi' => $_POST['spesialisasi'] ?? 'Umum',
        'no_telepon' => $_POST['no_telepon'] ?? '',
        'email' => $_POST['email'] ?? '',
        'jadwal_praktek' => $_POST['jadwal_praktek'] ?? '',
        'tanggal_bergabung' => $_POST['tanggal_bergabung'] ?? date('Y-m-d'),
        'status' => 'Aktif'
    ];

    // Validate input
    $errors = [];
    
    if (empty($data['nama_dokter'])) {
        $errors[] = "Nama dokter harus diisi";
    }

    if (empty($data['no_telepon'])) {
        $errors[] = "Nomor telepon harus diisi";
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Validate unique license number
    if (!empty($data['no_lisensi'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM veterinarian WHERE no_lisensi = ?");
        $stmt->execute([$data['no_lisensi']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nomor lisensi sudah digunakan";
        }
    }

    if (empty($errors)) {
        // ... (existing code)
    } else {
        error_log("Validation errors: " . implode(", ", $errors));
    }
}

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <!-- ... -->
    <form action="create.php" method="POST" class="bg-white rounded-lg shadow-md p-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Dokter <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nama_dokter" required
                       value="<?php echo $_POST['nama_dokter'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Dr. Nama Lengkap">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Lisensi
                </label>
                <input type="text" name="no_lisensi"
                       value="<?php echo $_POST['no_lisensi'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="VET-XXXX-XXXX">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Spesialisasi <span class="text-red-600">*</span>
                </label>
                <select name="spesialisasi" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="Umum">Umum</option>
                    <option value="Bedah">Bedah</option>
                    <option value="Gigi">Gigi</option>
                    <option value="Kulit">Kulit</option>
                    <option value="Kardio">Kardio</option>
                    <option value="Eksotik">Eksotik</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Bergabung
                </label>
                <input type="date" name="tanggal_bergabung" required
                       value="<?php echo $_POST['tanggal_bergabung'] ?? date('Y-m-d'); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Telepon <span class="text-red-600">*</span>
                </label>
                <input type="text" name="no_telepon" required
                       value="<?php echo $_POST['no_telepon'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="08xxxxxxxxxx">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email
                </label>
                <input type="email" name="email"
                       value="<?php echo $_POST['email'] ?? ''; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="dokter@vetclinic.com">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Jadwal Praktek (Display Only)
                </label>
                <input type="text" name="jadwal_praktek"
                       value="<?php echo $_POST['jadwal_praktek'] ?? 'Senin - Jumat, 09:00 - 17:00'; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Contoh: Senin - Jumat, 09:00 - 17:00">
                <p class="mt-1 text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Sistem akan otomatis membuat jadwal Senin-Jumat jam 09:00-17:00 untuk booking online.
                </p>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i> Simpan Dokter
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>