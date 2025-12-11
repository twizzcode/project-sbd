<?php
require_once __DIR__ . '/../includes/owner_auth.php';

$page_title = 'Book Appointment';

// Get owner's pets
$owner_id = $_SESSION['owner_id'];
$result = mysqli_query($conn, "SELECT pet_id, nama_hewan, jenis FROM pet WHERE owner_id = '$owner_id' AND status = 'Aktif' ORDER BY nama_hewan");
$pets = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pets[] = $row;
}

$success_message = '';
$error_message = '';

// Get available doctors
$result = mysqli_query($conn, "SELECT dokter_id, nama_dokter, spesialisasi FROM veterinarian WHERE status = 'Aktif' ORDER BY nama_dokter");
$doctors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $doctors[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'] ?? null;
    $tanggal = $_POST['tanggal'] ?? null;
    $dokter_id = $_POST['dokter_id'] ?? null;
    $keluhan = $_POST['keluhan'] ?? '';
    
    if ($pet_id && $tanggal && $dokter_id) {
        // Insert appointment without time and status
        $owner_id = $_SESSION['owner_id'];
        $keluhan_escaped = mysqli_real_escape_string($conn, $keluhan);
        mysqli_query($conn, "INSERT INTO appointment (pet_id, owner_id, dokter_id, tanggal_appointment, keluhan_awal, created_at)
            VALUES ('$pet_id', '$owner_id', '$dokter_id', '$tanggal', '$keluhan_escaped', NOW())");
        
        $success_message = 'Appointment berhasil dibuat!';
        $_POST = [];
    } else {
        $error_message = 'Mohon lengkapi semua field yang diperlukan (pet, tanggal, dan dokter).';
    }
}
?>

<?php require_once __DIR__ . '/../includes/owner_header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-3xl font-bold text-gray-800">Book Appointment</h1>
            <a href="appointments.php" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Appointments
            </a>
        </div>
        <p class="text-gray-600">Schedule a visit for your pet</p>
    </div>

    <?php if ($success_message): ?>
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
            <p class="text-green-700"><?= htmlspecialchars($success_message) ?></p>
        </div>
        <a href="appointments.php" class="mt-3 inline-block text-green-700 font-semibold hover:text-green-900">
            View My Appointments →
        </a>
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

    <?php if (empty($pets)): ?>
    <div class="bg-white rounded-xl shadow-lg p-12 text-center">
        <i class="fas fa-paw text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Pets Found</h3>
        <p class="text-gray-500 mb-6">You need to register a pet before booking an appointment</p>
        <a href="index.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-2"></i>Register Pet
        </a>
    </div>
    <?php else: ?>

    <!-- Booking Form -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form method="POST" id="bookingForm">
            <!-- Step 1: Select Pet -->
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 text-sm">1</span>
                    Pilih Hewan Peliharaan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($pets as $pet): ?>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="pet_id" value="<?= $pet['pet_id'] ?>" 
                               class="peer sr-only" required
                               <?= (isset($_POST['pet_id']) && $_POST['pet_id'] == $pet['pet_id']) ? 'checked' : '' ?>>
                        <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-indigo-300 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-50">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-<?= strtolower($pet['jenis']) === 'anjing' ? 'dog' : (strtolower($pet['jenis']) === 'kucing' ? 'cat' : 'paw') ?> text-indigo-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800"><?= htmlspecialchars($pet['nama_hewan']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($pet['jenis']) ?></p>
                                </div>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Step 2: Select Date -->
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 text-sm">2</span>
                    Pilih Tanggal
                </h3>
                <input type="date" name="tanggal" id="tanggal" required
                       min="<?= date('Y-m-d') ?>"
                       max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                       value="<?= $_POST['tanggal'] ?? '' ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Step 3: Select Doctor -->
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 text-sm">3</span>
                    Pilih Dokter
                </h3>
                <select name="dokter_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Dokter --</option>
                    <?php foreach ($doctors as $doctor): ?>
                    <option value="<?= $doctor['dokter_id'] ?>" <?= (isset($_POST['dokter_id']) && $_POST['dokter_id'] == $doctor['dokter_id']) ? 'selected' : '' ?>>
                        Dr. <?= htmlspecialchars($doctor['nama_dokter']) ?> - <?= htmlspecialchars($doctor['spesialisasi']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-gray-500 mt-2">Klinik akan menentukan waktu appointment setelah konfirmasi</p>
            </div>

            <!-- Step 4: Complaint/Notes -->
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 text-sm">4</span>
                    Keluhan / Catatan
                </h3>
                <textarea name="keluhan" rows="4" 
                          placeholder="Jelaskan keluhan atau kondisi hewan peliharaan Anda..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= $_POST['keluhan'] ?? '' ?></textarea>
                <p class="text-sm text-gray-500 mt-2">Opsional: Informasi ini akan membantu dokter untuk persiapan</p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4">
                <a href="appointments.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" id="submitBtn"
                        class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-calendar-check mr-2"></i>Book Appointment
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/owner_footer.php'; ?>
