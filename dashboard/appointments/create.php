<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/appointment_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

$page_title = 'Buat Janji Temu';

// Get active doctors
$stmt = $pdo->prepare("
    SELECT dokter_id, nama_dokter, spesialisasi, jadwal_praktek
    FROM veterinarian 
    WHERE status = 'Aktif'
    ORDER BY nama_dokter
");
$stmt->execute();
$doctors = $stmt->fetchAll();

// Get pets with their owners
$stmt = $pdo->prepare("
    SELECT 
        p.pet_id,
        p.nama_hewan,
        p.jenis,
        o.user_id as owner_id,
        o.nama_lengkap as owner_name,
        o.no_telepon
    FROM pet p
    JOIN users o ON p.owner_id = o.user_id
    WHERE p.status = 'Aktif'
    ORDER BY o.nama_lengkap, p.nama_hewan
");
$stmt->execute();
$pets = $stmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        // Start transaction
        $pdo->beginTransaction();

        // Validate and sanitize input
        $pet_id = filter_var($_POST['pet_id'], FILTER_VALIDATE_INT);
        $dokter_id = filter_var($_POST['dokter_id'], FILTER_VALIDATE_INT);
        $jenis_layanan = clean_input($_POST['jenis_layanan'] ?? 'Konsultasi Umum');
        $tanggal = clean_input($_POST['tanggal']);
        $jam_appointment = clean_input($_POST['jam_appointment']);
        $keluhan = clean_input($_POST['keluhan']);
        $catatan = clean_input($_POST['catatan'] ?? '');

        // Validate required fields
        if (!$pet_id || !$dokter_id || !$tanggal || !$jam_appointment || !$keluhan) {
            throw new Exception('Semua field wajib diisi');
        }

        // Validate date and time
        if (!validate_appointment_datetime($tanggal, $jam_appointment)) {
            throw new Exception('Tanggal dan jam tidak valid');
        }

        // Check doctor availability
        if (!is_doctor_available($pdo, $dokter_id, $tanggal, $jam_appointment, null)) {
            throw new Exception('Dokter sudah memiliki janji temu pada waktu yang dipilih');
        }

        // Get owner_id from pet
        $stmt = $pdo->prepare("SELECT owner_id FROM pet WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        $owner_id = $stmt->fetchColumn();

        if (!$owner_id) {
            throw new Exception('Data hewan tidak valid');
        }

        // Insert appointment
        $stmt = $pdo->prepare("
            INSERT INTO appointment (
                pet_id, owner_id, dokter_id, jenis_layanan, 
                tanggal_appointment, jam_appointment, 
                keluhan_awal, catatan, status, created_at
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?,
                ?, ?, 'Pending', CURRENT_TIMESTAMP
            )
        ");

        $stmt->execute([
            $pet_id, $owner_id, $dokter_id, $jenis_layanan,
            $tanggal, $jam_appointment,
            $keluhan, $catatan
        ]);

        $appointment_id = $pdo->lastInsertId();

        // Create notification for owner
        create_notification($pdo, $owner_id, 'appointment_created', $appointment_id);

        // Commit transaction
        $pdo->commit();

        // Set success message and redirect
        $_SESSION['success'] = 'Janji temu berhasil dibuat';
        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container max-w-4xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Buat Janji Temu</h2>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="" method="POST" class="space-y-6" id="appointmentForm">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <!-- Pet Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="pet_id">
                    Pasien & Pemilik <span class="text-red-500">*</span>
                </label>
                <select name="pet_id" id="pet_id" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Pasien</option>
                    <?php foreach ($pets as $pet): ?>
                        <option value="<?php echo $pet['pet_id']; ?>"
                                data-owner="<?php echo htmlspecialchars($pet['owner_name']); ?>"
                                data-phone="<?php echo htmlspecialchars($pet['no_telepon']); ?>">
                            <?php echo htmlspecialchars($pet['nama_hewan']); ?> -
                            <?php echo htmlspecialchars($pet['jenis']); ?> 
                            (<?php echo htmlspecialchars($pet['owner_name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-2 text-sm text-gray-500" id="ownerInfo"></p>
            </div>

            <!-- Service Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="jenis_layanan">
                    Jenis Layanan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="jenis_layanan" id="jenis_layanan" required
                       value="Konsultasi Umum"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Contoh: Konsultasi Umum, Vaksinasi, Grooming">
                <p class="mt-2 text-sm text-gray-500">Masukkan jenis layanan yang dibutuhkan</p>
            </div>

            <!-- Doctor Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="dokter_id">
                    Dokter <span class="text-red-500">*</span>
                </label>
                <select name="dokter_id" id="dokter_id" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Dokter</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo $doctor['dokter_id']; ?>"
                                data-schedule="<?php echo htmlspecialchars($doctor['jadwal_praktek'] ?? 'Senin-Jumat 08:00-17:00'); ?>">
                            <?php echo htmlspecialchars($doctor['nama_dokter']); ?> 
                            <?php if ($doctor['spesialisasi']): ?>
                                - <?php echo htmlspecialchars($doctor['spesialisasi']); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-2 text-sm text-gray-500" id="doctorSchedule"></p>
            </div>

            <!-- Date and Time -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="tanggal">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal" id="tanggal" required
                           min="<?php echo date('Y-m-d'); ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="jam_appointment">
                        Jam <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_appointment" id="jam_appointment" required
                           min="08:00" max="20:00"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Jam praktek: 08:00 - 20:00</p>
                </div>
            </div>

            <!-- Complaint -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="keluhan">
                    Keluhan <span class="text-red-500">*</span>
                </label>
                <textarea name="keluhan" id="keluhan" rows="3" required
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Deskripsikan keluhan atau alasan kunjungan..."><?php echo isset($_POST['keluhan']) ? htmlspecialchars($_POST['keluhan']) : ''; ?></textarea>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="catatan">
                    Catatan Tambahan
                </label>
                <textarea name="catatan" id="catatan" rows="2"
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Catatan khusus atau informasi tambahan..."><?php echo isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : ''; ?></textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-4">
                <button type="reset" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    Reset
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    const petSelect = document.getElementById('pet_id');
    const doctorSelect = document.getElementById('dokter_id');
    const dateInput = document.getElementById('tanggal');
    const timeInput = document.getElementById('jam_mulai');
    
    // Update owner info when pet is selected
    petSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const ownerInfo = document.getElementById('ownerInfo');
        
        if (this.value) {
            ownerInfo.textContent = `Pemilik: ${selectedOption.dataset.owner} - ${selectedOption.dataset.phone}`;
        } else {
            ownerInfo.textContent = '';
        }
    });

    // Update doctor schedule when doctor is selected
    doctorSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const doctorSchedule = document.getElementById('doctorSchedule');
        
        if (this.value) {
            doctorSchedule.textContent = `Jadwal Praktek: ${selectedOption.dataset.schedule}`;
        } else {
            doctorSchedule.textContent = '';
        }
    });

    // Set minimum date to today
    dateInput.min = new Date().toISOString().split('T')[0];

    // Form validation
    form.addEventListener('submit', function(e) {
        const date = new Date(dateInput.value);
        const time = timeInput.value;

        // Check if date is in the past
        if (date < new Date().setHours(0, 0, 0, 0)) {
            e.preventDefault();
            alert('Tanggal tidak boleh di masa lalu');
            return;
        }

        // Check if date is too far in the future (e.g., max 3 months)
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 3);
        if (date > maxDate) {
            e.preventDefault();
            alert('Tanggal terlalu jauh di masa depan (maksimal 3 bulan dari sekarang)');
            return;
        }

        // Additional validations can be added here
    });
});
</script>

<?php include '../../includes/footer.php'; ?>