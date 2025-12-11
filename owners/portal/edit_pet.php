<?php
require_once __DIR__ . '/../includes/owner_auth.php';

$page_title = 'Edit Pet';

// Get pet ID
$pet_id = $_GET['id'] ?? null;
if (!$pet_id) {
    header('Location: index.php');
    exit;
}

// Get pet data and verify ownership
$owner_id = $_SESSION['owner_id'];
$result = mysqli_query($conn, "SELECT * FROM pet WHERE pet_id = '$pet_id' AND owner_id = '$owner_id'");
$pet = mysqli_fetch_assoc($result);

if (!$pet) {
    header('Location: index.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_hewan = $_POST['nama_hewan'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    $ras = $_POST['ras'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $berat_badan = $_POST['berat_badan'] ?? null;
    $warna = $_POST['warna'] ?? '';
    $ciri_khusus = $_POST['ciri_khusus'] ?? '';
    
    // Validation
    if (empty($nama_hewan)) {
        $error_message = 'Nama hewan wajib diisi.';
    } elseif (empty($jenis)) {
        $error_message = 'Jenis hewan wajib dipilih.';
    } elseif (empty($jenis_kelamin)) {
        $error_message = 'Jenis kelamin wajib dipilih.';
    } else {
        // Update pet
        mysqli_query($conn, "UPDATE pet SET nama_hewan = '$nama_hewan', jenis = '$jenis', ras = '$ras', 
            jenis_kelamin = '$jenis_kelamin', tanggal_lahir = '$tanggal_lahir', berat_badan = '$berat_badan', 
            warna = '$warna', ciri_khusus = '$ciri_khusus'
            WHERE pet_id = '$pet_id' AND owner_id = '$owner_id'");
        
        $success_message = 'Pet information updated successfully!';
        
        // Refresh pet data
        $result = mysqli_query($conn, "SELECT * FROM pet WHERE pet_id = '$pet_id' AND owner_id = '$owner_id'");
        $pet = mysqli_fetch_assoc($result);
    }
}
?>

<?php require_once __DIR__ . '/../includes/owner_header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-3xl font-bold text-gray-800">Edit Pet Information</h1>
            <a href="pet_profile.php?id=<?= $pet_id ?>" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Profile
            </a>
        </div>
        <p class="text-gray-600">Update <?= htmlspecialchars($pet['nama_hewan']) ?>'s information</p>
    </div>

    <?php if ($success_message): ?>
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
            <p class="text-green-700 font-semibold"><?= htmlspecialchars($success_message) ?></p>
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
        
        <!-- Current Photo Removed -->

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
                           value="<?= htmlspecialchars($pet['nama_hewan']) ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Jenis Hewan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jenis Hewan <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Anjing" <?= $pet['jenis'] === 'Anjing' ? 'selected' : '' ?>>Anjing</option>
                        <option value="Kucing" <?= $pet['jenis'] === 'Kucing' ? 'selected' : '' ?>>Kucing</option>
                        <option value="Burung" <?= $pet['jenis'] === 'Burung' ? 'selected' : '' ?>>Burung</option>
                        <option value="Kelinci" <?= $pet['jenis'] === 'Kelinci' ? 'selected' : '' ?>>Kelinci</option>
                        <option value="Hamster" <?= $pet['jenis'] === 'Hamster' ? 'selected' : '' ?>>Hamster</option>
                        <option value="Reptil" <?= $pet['jenis'] === 'Reptil' ? 'selected' : '' ?>>Reptil</option>
                        <option value="Lainnya" <?= $pet['jenis'] === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <!-- Ras -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ras / Breed
                    </label>
                    <input type="text" name="ras"
                           value="<?= htmlspecialchars($pet['ras'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kelamin" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Jantan" <?= $pet['jenis_kelamin'] === 'Jantan' ? 'selected' : '' ?>>Jantan</option>
                        <option value="Betina" <?= $pet['jenis_kelamin'] === 'Betina' ? 'selected' : '' ?>>Betina</option>
                    </select>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="tanggal_lahir"
                           value="<?= $pet['tanggal_lahir'] ?? '' ?>"
                           max="<?= date('Y-m-d') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
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
                           value="<?= $pet['berat_badan'] ?? '' ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Warna -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Warna / Color
                    </label>
                    <input type="text" name="warna"
                           value="<?= htmlspecialchars($pet['warna'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Ciri Khusus -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ciri Khusus / Special Marks
                    </label>
                    <textarea name="ciri_khusus" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($pet['ciri_khusus'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Photo Upload Removed -->

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
            <a href="pet_profile.php?id=<?= $pet_id ?>" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
                <i class="fas fa-save mr-2"></i>Save Changes
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
