<?php
require_once __DIR__ . '/../includes/owner_auth.php';

$page_title = 'Book Appointment';

// Get owner's pets
$stmt = $pdo->prepare("SELECT pet_id, nama_hewan, jenis FROM pet WHERE owner_id = ? AND status = 'Aktif' ORDER BY nama_hewan");
$stmt->execute([$_SESSION['owner_id']]);
$pets = $stmt->fetchAll();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'] ?? null;
    $tanggal = $_POST['tanggal'] ?? null;
    $jam = $_POST['jam'] ?? null;
    $dokter_id = $_POST['dokter_id'] ?? null;
    $keluhan = $_POST['keluhan'] ?? '';
    
    if ($pet_id && $tanggal && $jam && $dokter_id) {
        try {
            // Check if slot is still available
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM appointment 
                WHERE dokter_id = ? 
                AND tanggal_appointment = ? 
                AND jam_appointment = ? 
                AND status NOT IN ('Cancelled', 'No_Show')
            ");
            $stmt->execute([$dokter_id, $tanggal, $jam]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $error_message = 'Maaf, slot waktu tersebut sudah dibooking. Silakan pilih waktu lain.';
            } else {
                // Insert appointment
                $stmt = $pdo->prepare("
                    INSERT INTO appointment (pet_id, owner_id, dokter_id, tanggal_appointment, jam_appointment, jenis_layanan, status, keluhan_awal, created_at)
                    VALUES (?, ?, ?, ?, ?, 'Pemeriksaan Umum', 'Pending', ?, NOW())
                ");
                $stmt->execute([$pet_id, $_SESSION['owner_id'], $dokter_id, $tanggal, $jam, $keluhan]);
                
                $success_message = 'Appointment berhasil dibuat! Status: Menunggu konfirmasi dari klinik.';
                
                // Clear form
                $_POST = [];
            }
        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan saat membuat appointment: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Mohon lengkapi semua field yang diperlukan (pet, tanggal, waktu, dan dokter).';
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
                       onchange="loadAvailableSlots()"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Step 3: Select Doctor & Time -->
            <div class="mb-8" id="doctorTimeSection" style="display: none;">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-3 text-sm">3</span>
                    Pilih Dokter & Waktu
                </h3>
                <div id="availableSlots" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-3"></i>
                    <p class="text-gray-600">Loading available slots...</p>
                </div>
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
                <button type="submit" id="submitBtn" disabled
                        class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                    <i class="fas fa-calendar-check mr-2"></i>Book Appointment
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
let selectedDoctor = null;
let selectedTime = null;

function loadAvailableSlots() {
    const tanggal = document.getElementById('tanggal').value;
    if (!tanggal) return;

    const date = new Date(tanggal + 'T00:00:00');
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const dayName = days[date.getDay()];

    document.getElementById('doctorTimeSection').style.display = 'block';
    document.getElementById('availableSlots').innerHTML = '<i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-3"></i><p class="text-gray-600">Loading...</p>';

    fetch(`get_available_slots.php?tanggal=${tanggal}&hari=${dayName}`)
        .then(response => response.json())
        .then(data => {
            displayAvailableSlots(data);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('availableSlots').innerHTML = '<p class="text-red-600">Error loading slots</p>';
        });
}

function displayAvailableSlots(doctors) {
    const container = document.getElementById('availableSlots');
    
    if (doctors.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600">Tidak ada jadwal dokter tersedia untuk tanggal ini</p>
            </div>
        `;
        return;
    }

    let html = '<div class="space-y-6">';
    
    doctors.forEach(doctor => {
        html += `
            <div class="border-2 border-gray-200 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user-md text-indigo-600 text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-lg text-gray-800">Dr. ${doctor.nama_dokter}</h4>
                        <p class="text-sm text-gray-600">${doctor.spesialisasi}</p>
                    </div>
                </div>
                <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
        `;
        
        doctor.slots.forEach(slot => {
            const isAvailable = slot.available;
            html += `
                <button type="button" 
                        onclick="selectSlot(${doctor.dokter_id}, '${slot.time}', this)"
                        class="time-slot px-3 py-2 rounded-lg text-sm font-medium transition
                               ${isAvailable ? 'bg-white border-2 border-gray-300 hover:border-indigo-600 hover:bg-indigo-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed border-2 border-gray-200'}"
                        ${!isAvailable ? 'disabled' : ''}>
                    ${slot.time}
                </button>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function selectSlot(dokterId, time, button) {
    // Remove previous selection
    document.querySelectorAll('.time-slot').forEach(btn => {
        btn.classList.remove('border-indigo-600', 'bg-indigo-600', 'text-white');
        btn.classList.add('border-gray-300', 'bg-white');
    });
    
    // Highlight selected
    button.classList.remove('border-gray-300', 'bg-white');
    button.classList.add('border-indigo-600', 'bg-indigo-600', 'text-white');
    
    // Set hidden fields
    selectedDoctor = dokterId;
    selectedTime = time;
    
    // Remove old hidden inputs if exists
    document.querySelectorAll('input[name="dokter_id"], input[name="jam"]').forEach(el => el.remove());
    
    // Add hidden inputs
    const form = document.getElementById('bookingForm');
    const dokterInput = document.createElement('input');
    dokterInput.type = 'hidden';
    dokterInput.name = 'dokter_id';
    dokterInput.value = dokterId;
    form.appendChild(dokterInput);
    
    const jamInput = document.createElement('input');
    jamInput.type = 'hidden';
    jamInput.name = 'jam';
    jamInput.value = time;
    form.appendChild(jamInput);
    
    // Enable submit button
    document.getElementById('submitBtn').disabled = false;
}

// Form validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    if (!selectedDoctor || !selectedTime) {
        e.preventDefault();
        alert('Mohon pilih dokter dan waktu appointment');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/owner_footer.php'; ?>
