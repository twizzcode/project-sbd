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

$page_title = 'Edit Janji Temu';

// Get appointment ID
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$appointment_id) {
    $_SESSION['error'] = "ID Janji Temu tidak valid";
    header("Location: index.php");
    exit;
}

// Get appointment data
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        p.nama_hewan,
        p.jenis as jenis_hewan,
        o.nama_lengkap as owner_name,
        o.no_telepon as owner_phone,
        v.nama_dokter as dokter_name,
        v.spesialisasi as dokter_spesialisasi,
        a.jenis_layanan
    FROM appointment a
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN users o ON a.owner_id = o.user_id
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE a.appointment_id = ?
");
$stmt->execute([$appointment_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    $_SESSION['error'] = "Data janji temu tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Get active doctors
$stmt = $pdo->prepare("
    SELECT dokter_id, nama_dokter, spesialisasi, jadwal_praktek
    FROM veterinarian 
    WHERE status = 'Active'
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
        $tanggal_appointment = clean_input($_POST['tanggal_appointment']);
        $jam_appointment = clean_input($_POST['jam_appointment']);
        $keluhan_awal = clean_input($_POST['keluhan_awal']);
        $catatan = clean_input($_POST['catatan'] ?? '');
        $status = clean_input($_POST['status']);

        // Validate required fields
        if (!$pet_id || !$dokter_id || !$tanggal_appointment || !$jam_appointment || !$status) {
            throw new Exception('Semua field wajib diisi');
        }

        // Validate date and time
        if (!validate_appointment_datetime($tanggal_appointment, $jam_appointment)) {
            throw new Exception('Tanggal dan jam tidak valid');
        }

        // Check doctor availability (excluding current appointment)
        if (!is_doctor_available($pdo, $dokter_id, $tanggal_appointment, $jam_appointment, null, $appointment_id)) {
            throw new Exception('Dokter tidak tersedia pada waktu yang dipilih');
        }

        // Get owner_id from pet
        $stmt = $pdo->prepare("SELECT owner_id FROM pet WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        $owner_id = $stmt->fetchColumn();

        // Update appointment
        $stmt = $pdo->prepare("
            UPDATE appointment SET
                pet_id = ?,
                owner_id = ?,
                dokter_id = ?,
                jenis_layanan = ?,
                tanggal_appointment = ?,
                jam_appointment = ?,
                keluhan_awal = ?,
                catatan = ?,
                status = ?
            WHERE appointment_id = ?
        ");

        $stmt->execute([
            $pet_id,
            $owner_id,
            $dokter_id,
            $jenis_layanan,
            $tanggal_appointment,
            $jam_appointment,
            $keluhan_awal,
            $catatan,
            $status,
            $appointment_id
        ]);

        // Create notification for status change if status changed
        if ($status !== $appointment['status']) {
            create_notification($pdo, $owner_id, 'appointment_status_changed', $appointment_id);
        }

        // Commit transaction
        $pdo->commit();

        // Set success message and redirect
        $_SESSION['success'] = 'Janji temu berhasil diperbarui';
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
        <h2 class="text-2xl font-bold text-gray-800">Edit Janji Temu</h2>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="" method="POST" class="space-y-6" id="appointmentForm">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <!-- Current Status -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Status Saat Ini</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium"><?php echo get_appointment_status_badge($appointment['status']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Dibuat pada</p>
                        <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($appointment['created_at'])); ?></p>
                    </div>
                    <?php if ($appointment['updated_at']): ?>
                    <div>
                        <p class="text-sm text-gray-500">Terakhir diubah</p>
                        <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($appointment['updated_at'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

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
                                data-phone="<?php echo htmlspecialchars($pet['no_telepon']); ?>"
                                <?php echo $pet['pet_id'] == $appointment['pet_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pet['nama_hewan']); ?> -
                            <?php echo htmlspecialchars($pet['jenis']); ?> 
                            (<?php echo htmlspecialchars($pet['owner_name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-2 text-sm text-gray-500" id="ownerInfo"></p>
            </div>

            <!-- Service Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="jenis_layanan">
                    Jenis Layanan <span class="text-red-500">*</span>
                </label>
                <select name="jenis_layanan" id="jenis_layanan" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Jenis Layanan</option>
                    <option value="Konsultasi Umum" <?php echo $appointment['jenis_layanan'] === 'Konsultasi Umum' ? 'selected' : ''; ?>>Konsultasi Umum</option>
                    <option value="Vaksinasi" <?php echo $appointment['jenis_layanan'] === 'Vaksinasi' ? 'selected' : ''; ?>>Vaksinasi</option>
                    <option value="Grooming" <?php echo $appointment['jenis_layanan'] === 'Grooming' ? 'selected' : ''; ?>>Grooming</option>
                    <option value="Operasi" <?php echo $appointment['jenis_layanan'] === 'Operasi' ? 'selected' : ''; ?>>Operasi</option>
                    <option value="Perawatan Gigi" <?php echo $appointment['jenis_layanan'] === 'Perawatan Gigi' ? 'selected' : ''; ?>>Perawatan Gigi</option>
                    <option value="Pemeriksaan Rutin" <?php echo $appointment['jenis_layanan'] === 'Pemeriksaan Rutin' ? 'selected' : ''; ?>>Pemeriksaan Rutin</option>
                    <option value="Emergency" <?php echo $appointment['jenis_layanan'] === 'Emergency' ? 'selected' : ''; ?>>Emergency</option>
                    <option value="Lainnya" <?php echo $appointment['jenis_layanan'] === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
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
                                data-schedule="<?php echo htmlspecialchars($doctor['jadwal_praktek'] ?? ''); ?>"
                                <?php echo $doctor['dokter_id'] == $appointment['dokter_id'] ? 'selected' : ''; ?>>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="tanggal_appointment">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_appointment" id="tanggal_appointment" required
                           value="<?php echo $appointment['tanggal_appointment']; ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="jam_appointment">
                        Jam <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_appointment" id="jam_appointment" required
                           value="<?php echo date('H:i', strtotime($appointment['jam_appointment'])); ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="status">
                    Status <span class="text-red-500">*</span>
                </label>
                <select name="status" id="status" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Pending" <?php echo $appointment['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Confirmed" <?php echo $appointment['status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="Completed" <?php echo $appointment['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $appointment['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="No_Show" <?php echo $appointment['status'] === 'No_Show' ? 'selected' : ''; ?>>No Show</option>
                </select>
            </div>

            <!-- Complaint -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="keluhan_awal">
                    Keluhan <span class="text-red-500">*</span>
                </label>
                <textarea name="keluhan_awal" id="keluhan_awal" rows="3" required
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Deskripsikan keluhan atau alasan kunjungan..."><?php echo htmlspecialchars($appointment['keluhan_awal']); ?></textarea>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="catatan">
                    Catatan Tambahan
                </label>
                <textarea name="catatan" id="catatan" rows="2"
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Catatan khusus atau informasi tambahan..."><?php echo htmlspecialchars($appointment['catatan']); ?></textarea>
            </div>

            <!-- Submit Buttons -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    const petSelect = document.getElementById('pet_id');
    const doctorSelect = document.getElementById('dokter_id');
    const dateInput = document.getElementById('tanggal_appointment');
    const timeInput = document.getElementById('jam_appointment');
    const statusSelect = document.getElementById('status');
    
    // Update owner info when pet is selected
    function updateOwnerInfo() {
        const selectedOption = petSelect.options[petSelect.selectedIndex];
        const ownerInfo = document.getElementById('ownerInfo');
        
        if (petSelect.value) {
            ownerInfo.textContent = `Pemilik: ${selectedOption.dataset.owner} - ${selectedOption.dataset.phone}`;
        } else {
            ownerInfo.textContent = '';
        }
    }

    // Update doctor schedule when doctor is selected
    function updateDoctorSchedule() {
        const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
        const doctorSchedule = document.getElementById('doctorSchedule');
        
        if (doctorSelect.value) {
            doctorSchedule.textContent = `Jadwal Praktek: ${selectedOption.dataset.schedule}`;
        } else {
            doctorSchedule.textContent = '';
        }
    }

    // Update status options based on current date
    function updateStatusOptions() {
        const appointmentDate = new Date(dateInput.value + ' ' + timeInput.value);
        const now = new Date();

        // If appointment is in the past, only allow Completed, Cancelled, or No_Show
        if (appointmentDate < now) {
            const validStatuses = ['Completed', 'Cancelled', 'No_Show'];
            Array.from(statusSelect.options).forEach(option => {
                option.disabled = !validStatuses.includes(option.value);
            });
        } else {
            Array.from(statusSelect.options).forEach(option => {
                option.disabled = false;
            });
        }
    }

    // Initial updates
    updateOwnerInfo();
    updateDoctorSchedule();
    updateStatusOptions();

    // Event listeners
    petSelect.addEventListener('change', updateOwnerInfo);
    doctorSelect.addEventListener('change', updateDoctorSchedule);
    dateInput.addEventListener('change', updateStatusOptions);
    timeInput.addEventListener('change', updateStatusOptions);

    // Form validation
    form.addEventListener('submit', function(e) {
        const date = new Date(dateInput.value);
        const time = timeInput.value;
        const currentStatus = statusSelect.value;

        // Check if status change is valid
        if (date < new Date().setHours(0, 0, 0, 0) && 
            ['Pending', 'Confirmed'].includes(currentStatus)) {
            e.preventDefault();
            alert('Janji temu yang sudah lewat tidak bisa berstatus Pending atau Confirmed');
            return;
        }

        // Additional validations can be added here
    });
});
</script>

<?php include '../../includes/footer.php'; ?>