<?php
require_once __DIR__ . '/../includes/owner_auth.php';

$page_title = 'Add New Pet';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_hewan = trim($_POST['nama_hewan'] ?? '');
    $jenis = !empty($_POST['jenis']) ? $_POST['jenis'] : null;
    $ras = trim($_POST['ras'] ?? '');
    $jenis_kelamin = !empty($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : null;
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $berat_badan = $_POST['berat_badan'] ?? null;
    $warna = trim($_POST['warna'] ?? '');
    $ciri_khusus = trim($_POST['ciri_khusus'] ?? '');
    
    // Validation
    if (empty($nama_hewan)) {
        $error_message = 'Nama hewan wajib diisi.';
    } elseif (empty($jenis)) {
        $error_message = 'Jenis hewan wajib dipilih.';
    } elseif (empty($jenis_kelamin)) {
        $error_message = 'Jenis kelamin wajib dipilih.';
    } else {
        try {
            // Insert pet
            $stmt = $pdo->prepare("
                INSERT INTO pet (owner_id, nama_hewan, jenis, ras, jenis_kelamin, tanggal_lahir, berat_badan, warna, ciri_khusus, status, tanggal_registrasi)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif', NOW())
            ");
            
            $stmt->execute([
                $_SESSION['owner_id'],
                $nama_hewan,
                $jenis,
                $ras ?: null,
                $jenis_kelamin,
                $tanggal_lahir ?: null,
                $berat_badan ?: null,
                $warna ?: null,
                $ciri_khusus ?: null
            ]);
            
            $success_message = 'Pet berhasil didaftarkan!';
            
            // Clear form
            $_POST = [];
            
        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan saat mendaftarkan pet: ' . $e->getMessage();
        }
    }
}
?>

<?php require_once __DIR__ . '/../includes/owner_header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-3xl font-bold text-gray-800">Register New Pet</h1>
            <a href="index.php" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
        <p class="text-gray-600">Add a new pet to your family</p>
    </div>

    <?php if ($success_message): ?>
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
            <div>
                <p class="text-green-700 font-semibold"><?= htmlspecialchars($success_message) ?></p>
                <a href="index.php" class="text-green-700 hover:text-green-900 underline text-sm mt-1 inline-block">
                    View My Pets →
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
            <p class="text-red-700"><?= htmlspecialchars($error_message) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" class="bg-white rounded-xl shadow-lg p-8">
        
        <!-- Basic Information -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 text-sm">1</span>
                Informasi Dasar
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Hewan -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Hewan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_hewan" required
                           value="<?= htmlspecialchars($_POST['nama_hewan'] ?? '') ?>"
                           placeholder="Contoh: Fluffy, Max, Luna"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Jenis Hewan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jenis Hewan <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Anjing" <?= (isset($_POST['jenis']) && $_POST['jenis'] === 'Anjing') ? 'selected' : '' ?>>Anjing</option>
                        <option value="Kucing" <?= (isset($_POST['jenis']) && $_POST['jenis'] === 'Kucing') ? 'selected' : '' ?>>Kucing</option>
                        <option value="Burung" <?= (isset($_POST['jenis']) && $_POST['jenis'] === 'Burung') ? 'selected' : '' ?>>Burung</option>
                        <option value="Kelinci" <?= (isset($_POST['jenis']) && $_POST['jenis'] === 'Kelinci') ? 'selected' : '' ?>>Kelinci</option>
                        <option value="Hamster" <?= (isset($_POST['jenis']) && $_POST['jenis'] === 'Hamster') ? 'selected' : '' ?>>Hamster</option>
                        <option value="Reptil" <?= (isset($_POST['jenis']) && $_POST['jenis'] === 'Reptil') ? 'selected' : '' ?>>Reptil</option>
                        <option value="Lainnya" <?= (isset($_POST['jenis']) && $_POST['jenis'] === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <!-- Ras -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ras / Breed
                    </label>
                    <input type="text" name="ras"
                           value="<?= htmlspecialchars($_POST['ras'] ?? '') ?>"
                           placeholder="Contoh: Golden Retriever, Persian"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kelamin" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih --</option>
                        <option value="Jantan" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'Jantan') ? 'selected' : '' ?>>Jantan</option>
                        <option value="Betina" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'Betina') ? 'selected' : '' ?>>Betina</option>
                    </select>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="tanggal_lahir"
                           value="<?= $_POST['tanggal_lahir'] ?? '' ?>"
                           max="<?= date('Y-m-d') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Atau perkiraan tanggal lahir</p>
                </div>
            </div>
        </div>

        <!-- Physical Details -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 text-sm">2</span>
                Detail Fisik
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Berat Badan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Berat Badan (kg)
                    </label>
                    <input type="number" name="berat_badan" step="0.01" min="0"
                           value="<?= $_POST['berat_badan'] ?? '' ?>"
                           placeholder="Contoh: 5.5"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Warna -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Warna / Color
                    </label>
                    <input type="text" name="warna"
                           value="<?= htmlspecialchars($_POST['warna'] ?? '') ?>"
                           placeholder="Contoh: Cokelat, Putih Belang"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Ciri Khusus -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ciri Khusus / Special Marks
                    </label>
                    <textarea name="ciri_khusus" rows="3"
                              placeholder="Contoh: Ada tanda hitam di telinga kiri, ekor panjang..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($_POST['ciri_khusus'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Photo Upload Removed -->

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
            <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
                <i class="fas fa-paw mr-2"></i>Register Pet
            </button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="inline-block">
                    <img src="${e.target.result}" class="w-32 h-32 rounded-lg object-cover shadow-lg">
                    <p class="text-sm text-gray-600 mt-2">${input.files[0].name}</p>
                </div>
            `;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/owner_footer.php'; ?>
