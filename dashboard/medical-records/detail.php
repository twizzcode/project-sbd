<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/medical_record_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

$page_title = "Detail Rekam Medis";

// Get record ID
$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$record_id) {
    $_SESSION['error'] = "ID rekam medis tidak valid";
    header("Location: index.php");
    exit;
}

// Get record data
$record = get_medical_record($pdo, $record_id);

if (!$record) {
    $_SESSION['error'] = "Data rekam medis tidak ditemukan";
    header("Location: index.php");
    exit;
}

// No attachments needed anymore


// History feature disabled (table not created yet)
$history = [];

include '../../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Detail Rekam Medis</h2>
            <p class="text-gray-600">
                ID: #<?php echo str_pad($record_id, 6, '0', STR_PAD_LEFT); ?>
            </p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>

            <?php if ($record['status'] === 'Active' && in_array($_SESSION['role'], ['Admin', 'Dokter'])): ?>
                <a href="edit.php?id=<?php echo $record_id; ?>" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <button onclick="confirmDelete(<?php echo $record_id; ?>)"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-trash mr-2"></i> Hapus
                </button>
            <?php endif; ?>

            <button onclick="printRecord()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Medical Record Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Rekam Medis</h3>
                    <?php echo get_medical_record_status_badge($record['status']); ?>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Status and Schedule -->
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tanggal Pemeriksaan</p>
                        <p class="font-medium mb-4">
                            <?php echo date('l, d F Y', strtotime($record['tanggal'])); ?>
                        </p>

                        <?php if ($record['appointment_id']): ?>
                            <p class="text-sm text-gray-600 mb-1">Dari Janji Temu</p>
                            <p class="mb-4">
                                <a href="../appointments/detail.php?id=<?php echo $record['appointment_id']; ?>"
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-calendar-check mr-1"></i>
                                    Lihat Detail Janji Temu
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Created/Updated Info -->
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dibuat Oleh</p>
                        <p class="font-medium mb-4">
                            <?php echo htmlspecialchars($record['created_by_name']); ?><br>
                            <span class="text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?>
                            </span>
                        </p>

                        <?php if ($record['updated_by']): ?>
                            <p class="text-sm text-gray-600 mb-1">Terakhir Diubah</p>
                            <p class="font-medium mb-4">
                                <?php echo htmlspecialchars($record['updated_by_name']); ?><br>
                                <span class="text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i', strtotime($record['updated_at'])); ?>
                                </span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Medical Details -->
                <div class="mt-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Diagnosis</p>
                        <p class="bg-gray-50 p-3 rounded-lg">
                            <?php echo nl2br(htmlspecialchars($record['diagnosis'])); ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tindakan</p>
                        <p class="bg-gray-50 p-3 rounded-lg">
                            <?php echo nl2br(htmlspecialchars($record['tindakan'])); ?>
                        </p>
                    </div>

                    <?php if ($record['resep']): ?>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Resep</p>
                            <p class="bg-gray-50 p-3 rounded-lg">
                                <?php echo nl2br(htmlspecialchars($record['resep'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($record['catatan']): ?>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Catatan</p>
                            <p class="bg-gray-50 p-3 rounded-lg">
                                <?php echo nl2br(htmlspecialchars($record['catatan'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Medical Record History -->
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
                                <?php if ($log['action'] === 'UPDATE' && $log['old_status'] && $log['new_status']): ?>
                                    <p class="text-sm mt-1">
                                        Status: 
                                        <span class="text-gray-600">
                                            <?php echo $log['old_status']; ?> → <?php echo $log['new_status']; ?>
                                        </span>
                                    </p>
                                <?php endif; ?>
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
                
                <div class="flex items-start gap-4 mb-4">
                    <?php if ($record['pet_foto']): ?>
                        <?php 
                        $rec_pet_foto_src = (strpos($record['pet_foto'], 'http') === 0) 
                            ? $record['pet_foto'] 
                            : '/vetclinic/assets/images/uploads/' . $record['pet_foto'];
                        ?>
                        <img src="<?php echo $rec_pet_foto_src; ?>"
                             alt="<?php echo htmlspecialchars($record['nama_hewan']); ?>"
                             class="w-20 h-20 rounded-lg object-cover"
                             onerror="this.src='https://via.placeholder.com/80?text=Pet'">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paw text-gray-400 text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h4 class="font-bold text-lg">
                            <?php echo htmlspecialchars($record['nama_hewan']); ?>
                        </h4>
                        <p class="text-gray-600">
                            <?php echo htmlspecialchars($record['jenis_hewan']); ?>
                            <?php if ($record['ras_hewan']): ?>
                                - <?php echo htmlspecialchars($record['ras_hewan']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <p class="text-sm text-gray-600 mb-1">Pemilik</p>
                    <p class="font-medium">
                        <?php echo htmlspecialchars($record['owner_name']); ?>
                    </p>
                    <p class="text-gray-600 text-sm">
                        <?php echo htmlspecialchars($record['owner_phone']); ?>
                        <?php if ($record['owner_email']): ?>
                            <br><?php echo htmlspecialchars($record['owner_email']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Doctor Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Informasi Dokter</h3>
                
                <div class="flex items-start gap-4">
                    <?php if ($record['dokter_foto']): ?>
                        <?php 
                        $rec_dokter_foto_src = (strpos($record['dokter_foto'], 'http') === 0) 
                            ? $record['dokter_foto'] 
                            : '/vetclinic/assets/images/uploads/' . $record['dokter_foto'];
                        ?>
                        <img src="<?php echo $rec_dokter_foto_src; ?>"
                             alt="Dr. <?php echo htmlspecialchars($record['dokter_name']); ?>"
                             class="w-20 h-20 rounded-lg object-cover"
                             onerror="this.src='https://via.placeholder.com/80?text=Dr'">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-md text-gray-400 text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h4 class="font-bold text-lg">
                            Dr. <?php echo htmlspecialchars($record['dokter_name']); ?>
                        </h4>
                        <?php if ($record['dokter_spesialisasi']): ?>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($record['dokter_spesialisasi']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Template -->
<div id="print-content" class="hidden">
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 24px; margin-bottom: 10px;">REKAM MEDIS VETERINER</h1>
            <p style="font-size: 18px; margin: 0;">VetClinic</p>
            <p style="margin: 5px 0;">Jl. Contoh No. 123, Kota, 12345</p>
            <p style="margin: 5px 0;">Telp: (021) 123-4567</p>
        </div>

        <div style="margin-bottom: 30px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <p><strong>ID Rekam Medis:</strong> #<?php echo str_pad($record_id, 6, '0', STR_PAD_LEFT); ?></p>
                        <p><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($record['tanggal'])); ?></p>
                    </td>
                    <td style="width: 50%;">
                        <p><strong>Status:</strong> <?php echo $record['status']; ?></p>
                        <p><strong>Dibuat:</strong> <?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 18px; margin-bottom: 10px;">Data Pasien</h2>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <p><strong>Nama Hewan:</strong> <?php echo htmlspecialchars($record['nama_hewan']); ?></p>
                        <p><strong>Jenis:</strong> <?php echo htmlspecialchars($record['jenis_hewan']); ?></p>
                        <?php if ($record['ras_hewan']): ?>
                            <p><strong>Ras:</strong> <?php echo htmlspecialchars($record['ras_hewan']); ?></p>
                        <?php endif; ?>
                    </td>
                    <td style="width: 50%;">
                        <p><strong>Pemilik:</strong> <?php echo htmlspecialchars($record['owner_name']); ?></p>
                        <p><strong>Telepon:</strong> <?php echo htmlspecialchars($record['owner_phone']); ?></p>
                        <?php if ($record['owner_email']): ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($record['owner_email']); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 18px; margin-bottom: 10px;">Pemeriksaan</h2>
            <p><strong>Dokter:</strong> Dr. <?php echo htmlspecialchars($record['dokter_name']); ?></p>
            <?php if ($record['dokter_spesialisasi']): ?>
                <p><strong>Spesialisasi:</strong> <?php echo htmlspecialchars($record['dokter_spesialisasi']); ?></p>
            <?php endif; ?>
            
            <div style="margin-top: 15px;">
                <p><strong>Diagnosis:</strong></p>
                <p style="margin-left: 20px;"><?php echo nl2br(htmlspecialchars($record['diagnosis'])); ?></p>
            </div>

            <div style="margin-top: 15px;">
                <p><strong>Tindakan:</strong></p>
                <p style="margin-left: 20px;"><?php echo nl2br(htmlspecialchars($record['tindakan'])); ?></p>
            </div>

            <?php if ($record['resep']): ?>
                <div style="margin-top: 15px;">
                    <p><strong>Resep:</strong></p>
                    <p style="margin-left: 20px;"><?php echo nl2br(htmlspecialchars($record['resep'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($record['catatan']): ?>
                <div style="margin-top: 15px;">
                    <p><strong>Catatan:</strong></p>
                    <p style="margin-left: 20px;"><?php echo nl2br(htmlspecialchars($record['catatan'])); ?></p>
                </div>
            <?php endif; ?>
        </div>


        <div style="margin-top: 50px; text-align: right;">
            <div style="display: inline-block; text-align: center;">
                <p>Dokter Pemeriksa</p>
                <br><br><br>
                <p>Dr. <?php echo htmlspecialchars($record['dokter_name']); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus rekam medis ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete.php?id=${id}`;
        }
    });
}

function printRecord() {
    const printContent = document.getElementById('print-content').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}