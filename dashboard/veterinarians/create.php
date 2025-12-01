<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Only Admin can add doctors
if ($_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menambah dokter";
    header("Location: index.php");
    exit;
}

$page_title = 'Tambah Dokter';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        $nama_dokter = clean_input($_POST['nama_dokter']);
        $no_lisensi = clean_input($_POST['no_lisensi']);
        $spesialisasi = clean_input($_POST['spesialisasi']);
        $no_telepon = clean_input($_POST['no_telepon']);
        $email = clean_input($_POST['email']);
        $tanggal_bergabung = clean_input($_POST['tanggal_bergabung']);
        $status = clean_input($_POST['status']);

        // Handle file upload - REMOVED
        $foto_url = null;

        $stmt = $pdo->prepare("
            INSERT INTO veterinarian (
                nama_dokter, no_lisensi, spesialisasi, no_telepon, 
                email, tanggal_bergabung, status, foto_url
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $nama_dokter, $no_lisensi, $spesialisasi, $no_telepon,
            $email, $tanggal_bergabung, $status, $foto_url
        ]);

        $_SESSION['success'] = "Dokter berhasil ditambahkan";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Dokter Baru</h2>
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Dokter <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_dokter" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Lisensi</label>
                        <input type="text" name="no_lisensi" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Spesialisasi</label>
                        <select name="spesialisasi" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Umum">Umum</option>
                            <option value="Bedah">Bedah</option>
                            <option value="Gigi">Gigi</option>
                            <option value="Kulit">Kulit</option>
                            <option value="Kardio">Kardio</option>
                            <option value="Eksotik">Eksotik</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input type="text" name="no_telepon" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Bergabung</label>
                        <input type="date" name="tanggal_bergabung" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Aktif">Aktif</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Resign">Resign</option>
                        </select>
                    </div>
                </div>

                <!-- Photo upload removed -->

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i> Simpan Dokter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
