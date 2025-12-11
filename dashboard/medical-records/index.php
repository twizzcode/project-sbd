<?php
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/medical_record_functions.php';

// Medical records is restricted to staff only (not Owner role)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Owner') {
    header('Location: /owners/portal/');
    exit();
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

$page_title = "Rekam Medis";

// Initialize filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$pet_id = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : 0;
$dokter_id = isset($_GET['dokter_id']) ? (int)$_GET['dokter_id'] : 0;
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// Build query
$query = "
    SELECT 
        mr.*,
        p.nama_hewan,
        p.jenis as jenis_hewan,
        p.ras as ras_hewan,
        o.nama_lengkap as owner_name,
        o.no_telepon as owner_phone,
        v.nama_dokter as dokter_name,
        v.spesialisasi as dokter_spesialisasi,
        a.appointment_id,
        'Aktif' as status
    FROM medical_record mr
    JOIN pet p ON mr.pet_id = p.pet_id
    JOIN users o ON p.owner_id = o.user_id
    JOIN veterinarian v ON mr.dokter_id = v.dokter_id
    LEFT JOIN appointment a ON mr.appointment_id = a.appointment_id
    ORDER BY mr.tanggal_kunjungan DESC
";

$result = mysqli_query($conn, $query);
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get doctors for filter
$result = mysqli_query($conn, "
    SELECT dokter_id, nama_dokter, spesialisasi
    FROM veterinarian 
    WHERE status = 'Aktif'
    ORDER BY nama_dokter
");

$doctors = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Rekam Medis</h2>
        <p class="text-gray-600">Kelola data rekam medis pasien</p>
    </div>

    <!-- Records List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pasien
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pemilik
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dokter
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Diagnosis
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data rekam medis yang ditemukan
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <?php echo date('d/m/Y', strtotime($record['tanggal_kunjungan'])); ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-paw text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($record['nama_hewan']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?php echo htmlspecialchars($record['jenis_hewan']); ?>
                                                <?php if ($record['ras_hewan']): ?>
                                                    - <?php echo htmlspecialchars($record['ras_hewan']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-900">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($record['owner_name']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars($record['owner_phone']); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-900">
                                    <div class="text-sm font-medium text-gray-900">
                                        Dr. <?php echo htmlspecialchars($record['dokter_name']); ?>
                                    </div>
                                    <?php if ($record['dokter_spesialisasi']): ?>
                                        <div class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($record['dokter_spesialisasi']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-900">
                                    <div class="text-sm text-gray-900">
                                        <?php 
                                        $diagnosis = htmlspecialchars($record['diagnosis'] ?? '-');
                                        echo strlen($diagnosis) > 50 ? substr($diagnosis, 0, 47) . '...' : $diagnosis;
                                        ?>
                                    </div>
                                    <?php if ($record['appointment_id']): ?>
                                        <div class="text-xs text-blue-600">
                                            <a href="../appointments/detail.php?id=<?php echo $record['appointment_id']; ?>">
                                                <i class="fas fa-calendar-check mr-1"></i> Dari Janji Temu
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php echo get_medical_record_status_badge($record['status']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                    <a href="edit.php?id=<?php echo $record['record_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>