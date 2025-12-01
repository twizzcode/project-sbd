<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Hewan';

// Get pet ID from URL
$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pet_id) {
    $_SESSION['error'] = "ID Hewan tidak valid";
    header("Location: index.php");
    exit;
}

// Get pet data
$stmt = $pdo->prepare("SELECT * FROM pet WHERE pet_id = ?");
$stmt->execute([$pet_id]);
$pet = $stmt->fetch();

if (!$pet) {
    $_SESSION['error'] = "Data hewan tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Get all owners for the dropdown
$owners_stmt = $pdo->query("SELECT user_id as owner_id, nama_lengkap, no_telepon FROM users WHERE role = 'Owner' ORDER BY nama_lengkap");
$owners = $owners_stmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Check if POST data is received
        error_log("POST data received: " . print_r($_POST, true));
        
        // Start transaction
        $pdo->beginTransaction();

        $owner_id = clean_input($_POST['owner_id']);
        $nama_hewan = clean_input($_POST['nama_hewan']);
        $jenis = clean_input($_POST['jenis']);
        $ras = clean_input($_POST['ras'] ?? '');
        $jenis_kelamin = clean_input($_POST['jenis_kelamin']);
        $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? clean_input($_POST['tanggal_lahir']) : null;
        $berat_badan = !empty($_POST['berat_badan']) ? clean_input($_POST['berat_badan']) : null;
        $warna = clean_input($_POST['warna'] ?? '');
        $ciri_khusus = clean_input($_POST['ciri_khusus'] ?? '');
        $status = clean_input($_POST['status']);
        
        error_log("Processing update for pet_id: $pet_id with jenis: $jenis, jenis_kelamin: $jenis_kelamin");

        // Update pet data
        $stmt = $pdo->prepare("
            UPDATE pet SET 
                owner_id = ?,
                nama_hewan = ?,
                jenis = ?,
                ras = ?,
                jenis_kelamin = ?,
                tanggal_lahir = ?,
                berat_badan = ?,
                warna = ?,
                ciri_khusus = ?,
                status = ?
            WHERE pet_id = ?
        ");

        $stmt->execute([
            $owner_id,
            $nama_hewan,
            $jenis,
            $ras,
            $jenis_kelamin,
            $tanggal_lahir,
            $berat_badan,
            $warna,
            $ciri_khusus,
            $status,
            $pet_id
        ]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Data hewan berhasil diperbarui";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container max-w-4xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Data Hewan</h2>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <!-- Owner Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="owner_id">
                        Pemilik <span class="text-red-500">*</span>
                    </label>
                    <select name="owner_id" id="owner_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Pemilik</option>
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?php echo $owner['owner_id']; ?>"
                                    <?php echo $owner['owner_id'] == $pet['owner_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($owner['nama_lengkap'] ?? ''); ?> - 
                                <?php echo htmlspecialchars($owner['no_telepon'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Basic Information -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_hewan">
                        Nama Hewan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_hewan" id="nama_hewan" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo htmlspecialchars($pet['nama_hewan'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis">
                        Jenis Hewan <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis" id="jenis" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jenis Hewan</option>
                        <option value="Anjing" <?php echo $pet['jenis'] === 'Anjing' ? 'selected' : ''; ?>>Anjing</option>
                        <option value="Kucing" <?php echo $pet['jenis'] === 'Kucing' ? 'selected' : ''; ?>>Kucing</option>
                        <option value="Burung" <?php echo $pet['jenis'] === 'Burung' ? 'selected' : ''; ?>>Burung</option>
                        <option value="Kelinci" <?php echo $pet['jenis'] === 'Kelinci' ? 'selected' : ''; ?>>Kelinci</option>
                        <option value="Hamster" <?php echo $pet['jenis'] === 'Hamster' ? 'selected' : ''; ?>>Hamster</option>
                        <option value="Reptil" <?php echo $pet['jenis'] === 'Reptil' ? 'selected' : ''; ?>>Reptil</option>
                        <option value="Lainnya" <?php echo $pet['jenis'] === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="ras">
                        Ras
                    </label>
                    <input type="text" name="ras" id="ras"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Persian, Pomeranian, dll"
                           value="<?php echo htmlspecialchars($pet['ras'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis_kelamin">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kelamin" id="jenis_kelamin" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Jantan" <?php echo $pet['jenis_kelamin'] === 'Jantan' ? 'selected' : ''; ?>>
                            Jantan
                        </option>
                        <option value="Betina" <?php echo $pet['jenis_kelamin'] === 'Betina' ? 'selected' : ''; ?>>
                            Betina
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal_lahir">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo $pet['tanggal_lahir']; ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="berat_badan">
                        Berat Badan (kg)
                    </label>
                    <input type="number" step="0.01" name="berat_badan" id="berat_badan"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: 5.5"
                           value="<?php echo $pet['berat_badan'] ?? ''; ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="warna">
                        Warna
                    </label>
                    <input type="text" name="warna" id="warna"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Putih, Coklat, dll"
                           value="<?php echo htmlspecialchars($pet['warna'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Aktif" <?php echo $pet['status'] === 'Aktif' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="Meninggal" <?php echo $pet['status'] === 'Meninggal' ? 'selected' : ''; ?>>
                            Meninggal
                        </option>
                    </select>
                </div>

                <!-- Photo Upload Removed -->

                <!-- Special Characteristics -->
                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="ciri_khusus">
                        Ciri Khusus
                    </label>
                    <textarea name="ciri_khusus" id="ciri_khusus" rows="4"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Tambahkan ciri khusus tentang hewan ini (tanda lahir, perilaku, dll)..."
                    ><?php echo htmlspecialchars($pet['ciri_khusus'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-4">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
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