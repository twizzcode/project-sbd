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

$page_title = 'Detail Janji Temu';

// Get appointment ID
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$appointment_id) {
    $_SESSION['error'] = "ID Janji Temu tidak valid";
    header("Location: index.php");
    exit;
}

// Get appointment details
$result = mysqli_query($conn, "
    SELECT 
        a.*,
        p.nama_hewan,
        p.jenis as jenis_hewan,
        p.ras as ras_hewan,
        o.nama_lengkap as owner_name,
        o.no_telepon as owner_phone,
        o.email as owner_email,
        v.nama_dokter as dokter_name,
        v.spesialisasi as dokter_spesialisasi
    FROM appointment a
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN users o ON a.owner_id = o.user_id
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE a.appointment_id = '$appointment_id'
");

$appointment = mysqli_fetch_assoc($result);

if (!$appointment) {
    $_SESSION['error'] = "Data janji temu tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Appointment history feature disabled (table not created yet)
$history = [];

// Get medical records
$result = mysqli_query($conn, "
    SELECT 
        mr.*,
        v.nama_dokter as dokter_name
    FROM medical_record mr
    JOIN veterinarian v ON mr.dokter_id = v.dokter_id
    WHERE mr.appointment_id = '$appointment_id'
    ORDER BY mr.tanggal_kunjungan DESC
");

$medical_records = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Detail Janji Temu</h2>
            <p class="text-gray-600">
                ID: #<?php echo str_pad($appointment_id, 6, '0', STR_PAD_LEFT); ?>
            </p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            
            <a href="edit.php?id=<?php echo $appointment_id; ?>" 
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            
            <a href="../medical-records/create.php?appointment_id=<?php echo $appointment_id; ?>"
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-notes-medical mr-2"></i> Buat Rekam Medis
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Appointment Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Janji Temu</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Status and Schedule -->
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tanggal</p>
                        <p class="font-medium mb-4">
                            <?php echo date('l, d F Y', strtotime($appointment['tanggal_appointment'])); ?><br>
                        </p>

                        <p class="text-sm text-gray-600 mb-1">Layanan</p>
                    </div>

                    <!-- Created Info -->
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dibuat Pada</p>
                        <p class="font-medium mb-4">
                            <span class="text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($appointment['created_at'])); ?>
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Complaint & Notes -->
                <div class="mt-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Keluhan</p>
                        <p class="bg-gray-50 p-3 rounded-lg">
                            <?php echo nl2br(htmlspecialchars($appointment['keluhan'] ?? '-')); ?>
                        </p>
                    </div>

                    <?php if ($appointment['catatan']): ?>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Catatan</p>
                            <p class="bg-gray-50 p-3 rounded-lg">
                                <?php echo nl2br(htmlspecialchars($appointment['catatan'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Medical Records -->
            <?php if (!empty($medical_records)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Rekam Medis</h3>
                
                <?php foreach ($medical_records as $record): ?>
                    <div class="border-b border-gray-200 last:border-0 pb-4 mb-4 last:pb-0 last:mb-0">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">
                                    Dr. <?php echo htmlspecialchars($record['dokter_name']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?>
                                </p>
                            </div>
                            <a href="../medical-records/detail.php?id=<?php echo $record['record_id']; ?>"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="text-sm">
                                <span class="text-gray-600">Diagnosis:</span>
                                <?php echo htmlspecialchars($record['diagnosis']); ?>
                            </p>
                            <p class="text-sm">
                                <span class="text-gray-600">Tindakan:</span>
                                <?php echo htmlspecialchars($record['tindakan']); ?>
                            </p>
                            <?php if ($record['resep']): ?>
                                <p class="text-sm">
                                    <span class="text-gray-600">Resep:</span>
                                    <?php echo nl2br(htmlspecialchars($record['resep'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>



            <!-- Appointment History -->
            <?php if (!empty($history)): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Riwayat Perubahan</h3>
                
                <div class="space-y-4">
                    <?php foreach ($history as $log): ?>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <?php if ($log['action'] === 'CREATE'): ?>
                                    <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                <?php elseif ($log['action'] === 'UPDATE'): ?>
                                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-1">
                                <p class="font-medium">
                                    <?php echo htmlspecialchars($log['performed_by_name']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i', strtotime($log['performed_at'])); ?>
                                </p>
                                <?php if ($log['notes']): ?>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($log['notes']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Patient Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Pasien</h3>

                <div class="border-t pt-4">
                    <p class="text-sm text-gray-600 mb-1">Pemilik</p>
                    <p class="font-medium">
                        <?php echo htmlspecialchars($appointment['owner_name']); ?>
                    </p>
                    <p class="text-gray-600 text-sm">
                        <?php echo htmlspecialchars($appointment['owner_phone']); ?>
                        <?php if ($appointment['owner_email']): ?>
                            <br><?php echo htmlspecialchars($appointment['owner_email']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Doctor Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Dokter</h3>
                
                <div class="flex items-start gap-4">                
                    <div>
                        <h4 class="font-bold text-lg">
                            Dr. <?php echo htmlspecialchars($appointment['dokter_name']); ?>
                        </h4>
                        <?php if ($appointment['dokter_spesialisasi']): ?>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($appointment['dokter_spesialisasi']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
</div>

<script>
// No additional JavaScript needed
</script>

<?php include '../../includes/footer.php'; ?>