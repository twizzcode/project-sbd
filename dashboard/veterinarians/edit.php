<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Dokter';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Get doctor data
$result = mysqli_query($conn, "SELECT * FROM veterinarian WHERE dokter_id = '$id'");

$doctor = mysqli_fetch_assoc($result);

if (!$doctor) {
    $_SESSION['error'] = "Data dokter tidak ditemukan";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        $nama_dokter = $_POST['nama_dokter'];
        $no_lisensi = $_POST['no_lisensi'];
        $spesialisasi = $_POST['spesialisasi'];
        $no_telepon = $_POST['no_telepon'];
        $email = $_POST['email'];
        $status = $_POST['status'];

        $result = mysqli_query($conn, "
            UPDATE veterinarian SET 
                nama_dokter = '$nama_dokter', no_lisensi = '$no_lisensi', spesialisasi = '$spesialisasi', 
                no_telepon = '$no_telepon', email = '$email', 
                status = '$status'
            WHERE dokter_id = '$id'
        ");

        $_SESSION['success'] = "Data dokter berhasil diperbarui";
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
            <h2 class="text-2xl font-bold text-gray-800">Edit Data Dokter</h2>
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Dokter <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_dokter" required value="<?php echo htmlspecialchars($doctor['nama_dokter']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Lisensi</label>
                        <input type="text" name="no_lisensi" value="<?php echo htmlspecialchars($doctor['no_lisensi']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Spesialisasi</label>
                        <select name="spesialisasi" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php
                            $specialties = ['Umum', 'Bedah', 'Gigi', 'Kulit', 'Kardio', 'Eksotik'];
                            foreach ($specialties as $spec) {
                                $selected = $doctor['spesialisasi'] === $spec ? 'selected' : '';
                                echo "<option value=\"$spec\" $selected>$spec</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input type="text" name="no_telepon" value="<?php echo htmlspecialchars($doctor['no_telepon']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Bergabung</label>
                        <input type="date" name="tanggal_bergabung" disabled value="<?php echo $doctor['tanggal_bergabung']; ?>" class="w-full px-4 py-2 border rounded-lg bg-gray-100 cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Tanggal bergabung tidak dapat diubah</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php
                            $statuses = ['Aktif', 'Cuti', 'Resign'];
                            foreach ($statuses as $status) {
                                $selected = $doctor['status'] === $status ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>$status</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Photo upload removed -->

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
