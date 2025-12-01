<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

$page_title = 'Detail Pemilik Hewan';

// Get owner ID
$owner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$owner_id) {
    $_SESSION['error'] = "ID pemilik tidak valid";
    header("Location: index.php");
    exit;
}

// Get owner data
$stmt = $pdo->prepare("
    SELECT 
        u.*,
        COUNT(DISTINCT p.pet_id) as total_pets,
        COUNT(DISTINCT a.appointment_id) as total_appointments,
        COUNT(DISTINCT mr.record_id) as total_medical_records
    FROM users u
    LEFT JOIN pet p ON u.user_id = p.owner_id
    LEFT JOIN appointment a ON u.user_id = a.owner_id
    LEFT JOIN medical_record mr ON p.pet_id = mr.pet_id
    WHERE u.user_id = ? AND u.role = 'Owner'
    GROUP BY u.user_id
");
$stmt->execute([$owner_id]);
$owner = $stmt->fetch();

if (!$owner) {
    $_SESSION['error'] = "Pemilik tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Get owner's pets
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        COUNT(DISTINCT mr.record_id) as total_records
    FROM pet p
    LEFT JOIN medical_record mr ON p.pet_id = mr.pet_id
    WHERE p.owner_id = ?
    GROUP BY p.pet_id
    ORDER BY p.tanggal_registrasi DESC
");
$stmt->execute([$owner_id]);
$pets = $stmt->fetchAll();

// Get owner's appointments
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        p.nama_hewan,
        v.nama_dokter
    FROM appointment a
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE a.owner_id = ?
    ORDER BY a.tanggal_appointment DESC, a.jam_appointment DESC
    LIMIT 10
");
$stmt->execute([$owner_id]);
$appointments = $stmt->fetchAll();

// Get recent medical records
$stmt = $pdo->prepare("
    SELECT 
        mr.*,
        p.nama_hewan,
        v.nama_dokter
    FROM medical_record mr
    JOIN pet p ON mr.pet_id = p.pet_id
    JOIN veterinarian v ON mr.dokter_id = v.dokter_id
    WHERE p.owner_id = ?
    ORDER BY mr.tanggal_kunjungan DESC
    LIMIT 10
");
$stmt->execute([$owner_id]);
$medical_records = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Detail Pemilik Hewan</h2>
        <div class="flex gap-2">
            <a href="edit.php?id=<?php echo $owner_id; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Owner Info Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-start gap-6">
            <div class="flex-shrink-0">
                <div class="h-24 w-24 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-bold">
                    <?php echo strtoupper(substr($owner['nama_lengkap'], 0, 1)); ?>
                </div>
            </div>
            
            <div class="flex-grow">
                <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($owner['nama_lengkap']); ?></h3>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full <?php echo $owner['status'] === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo $owner['status']; ?>
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <p class="text-sm text-gray-600">Username</p>
                        <p class="font-medium">@<?php echo htmlspecialchars($owner['username']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($owner['email']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Nomor Telepon</p>
                        <p class="font-medium"><?php echo htmlspecialchars($owner['no_telepon'] ?? '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Bergabung</p>
                        <p class="font-medium"><?php echo date('d F Y', strtotime($owner['created_at'])); ?></p>
                    </div>
                    
                    <?php if ($owner['alamat']): ?>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Alamat</p>
                        <p class="font-medium"><?php echo htmlspecialchars($owner['alamat']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                    <i class="fas fa-paw text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Hewan</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $owner['total_pets']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                    <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Janji Temu</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $owner['total_appointments']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                    <i class="fas fa-file-medical text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Rekam Medis</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $owner['total_medical_records']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pets Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-paw mr-2"></i> Hewan Peliharaan
        </h3>
        
        <?php if (empty($pets)): ?>
            <p class="text-gray-500 text-center py-8">Belum ada hewan terdaftar</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($pets as $pet): ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex-shrink-0 h-12 w-12 bg-orange-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-<?php echo $pet['jenis'] === 'Anjing' ? 'dog' : ($pet['jenis'] === 'Kucing' ? 'cat' : 'paw'); ?> text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($pet['nama_hewan']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($pet['jenis']); ?><?php echo $pet['ras'] ? ' - ' . htmlspecialchars($pet['ras']) : ''; ?></p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-3 pt-3 border-t">
                            <span class="text-xs text-gray-500"><?php echo $pet['total_records']; ?> rekam medis</span>
                            <span class="px-2 py-1 text-xs rounded-full <?php echo $pet['status'] === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo $pet['status']; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Appointments -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-calendar-alt mr-2"></i> Riwayat Janji Temu (10 Terakhir)
        </h3>
        
        <?php if (empty($appointments)): ?>
            <p class="text-gray-500 text-center py-8">Belum ada riwayat janji temu</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hewan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dokter</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm"><?php echo date('d/m/Y', strtotime($apt['tanggal_appointment'])); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo date('H:i', strtotime($apt['jam_appointment'])); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($apt['nama_hewan']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($apt['nama_dokter']); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        <?php 
                                        echo $apt['status'] === 'Confirmed' ? 'bg-green-100 text-green-800' : 
                                             ($apt['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                             ($apt['status'] === 'Completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')); 
                                        ?>">
                                        <?php echo $apt['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Medical Records -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-file-medical-alt mr-2"></i> Riwayat Rekam Medis (10 Terakhir)
        </h3>
        
        <?php if (empty($medical_records)): ?>
            <p class="text-gray-500 text-center py-8">Belum ada riwayat rekam medis</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hewan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dokter</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diagnosis</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($medical_records as $mr): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm"><?php echo date('d/m/Y H:i', strtotime($mr['tanggal_kunjungan'])); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($mr['nama_hewan']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($mr['nama_dokter']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars(substr($mr['diagnosis'], 0, 50)) . (strlen($mr['diagnosis']) > 50 ? '...' : ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
