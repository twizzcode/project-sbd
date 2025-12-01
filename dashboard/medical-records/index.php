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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$pet_id = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : 0;
$dokter_id = isset($_GET['dokter_id']) ? (int)$_GET['dokter_id'] : 0;
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// Build query
$params = [];
$where = [];

if ($search) {
    $where[] = "(p.nama_hewan LIKE ? OR o.nama_lengkap LIKE ? OR mr.diagnosa LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($status) {
    // Status filter is available but not used in the schema yet
    // You can add a status column to medical_record table if needed
}

if ($pet_id) {
    $where[] = "mr.pet_id = ?";
    $params[] = $pet_id;
}

if ($dokter_id) {
    $where[] = "mr.dokter_id = ?";
    $params[] = $dokter_id;
}

if ($date_start) {
    $where[] = "DATE(mr.tanggal_kunjungan) >= ?";
    $params[] = $date_start;
}

if ($date_end) {
    $where[] = "DATE(mr.tanggal_kunjungan) <= ?";
    $params[] = $date_end;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total records for pagination
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM medical_record mr
    JOIN pet p ON mr.pet_id = p.pet_id
    JOIN users o ON p.owner_id = o.user_id
    JOIN veterinarian v ON mr.dokter_id = v.dokter_id
    $whereClause
");
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records
$stmt = $pdo->prepare("
    SELECT 
        mr.*,
        p.nama_hewan,
        p.jenis as jenis_hewan,
        p.ras as ras_hewan,
        p.foto_url as pet_foto,
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
    $whereClause
    ORDER BY mr.tanggal_kunjungan DESC
    LIMIT ? OFFSET ?
");

$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$records = $stmt->fetchAll();

// Get doctors for filter
$stmt = $pdo->prepare("
    SELECT dokter_id, nama_dokter, spesialisasi
    FROM veterinarian 
    WHERE status = 'Aktif'
    ORDER BY nama_dokter
");
$stmt->execute();
$doctors = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Rekam Medis</h2>
            <p class="text-gray-600">Kelola data rekam medis pasien</p>
        </div>
    </div>

    <!-- Filters -->
    <form class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                       placeholder="Nama hewan/pemilik/diagnosis...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Status</option>
                    <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Archived" <?php echo $status === 'Archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dokter</label>
                <select name="dokter_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Dokter</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo $doctor['dokter_id']; ?>" 
                                <?php echo $dokter_id == $doctor['dokter_id'] ? 'selected' : ''; ?>>
                            Dr. <?php echo htmlspecialchars($doctor['nama_dokter']); ?>
                            <?php if ($doctor['spesialisasi']): ?>
                                (<?php echo htmlspecialchars($doctor['spesialisasi']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="date_start" value="<?php echo htmlspecialchars($date_start); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="date_end" value="<?php echo htmlspecialchars($date_end); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg w-full">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
            </div>

            <?php if ($search || $status || $pet_id || $dokter_id || $date_start || $date_end): ?>
                <div class="flex items-end">
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg w-full text-center">
                        <i class="fas fa-times mr-2"></i> Reset Filter
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </form>

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
                                        <?php if ($record['pet_foto']): ?>
                                            <?php 
                                            $rec_pet_foto = (strpos($record['pet_foto'], 'http') === 0) 
                                                ? $record['pet_foto'] 
                                                : '/vetclinic/assets/images/uploads/' . $record['pet_foto'];
                                            ?>
                                            <img src="<?php echo $rec_pet_foto; ?>"
                                                 alt="<?php echo htmlspecialchars($record['nama_hewan']); ?>"
                                                 class="w-8 h-8 rounded-full object-cover mr-3"
                                                 onerror="this.src='https://via.placeholder.com/32?text=P'">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-paw text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
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

        <?php if ($total_pages > 1): ?>
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Menampilkan <?php echo $offset + 1; ?> - 
                        <?php echo min($offset + $per_page, $total_records); ?> 
                        dari <?php echo $total_records; ?> data
                    </div>
                    
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                               class="bg-white hover:bg-gray-50 text-gray-600 px-3 py-1 rounded-lg border">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                               class="<?php echo $i === $page ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-50 text-gray-600'; ?> px-3 py-1 rounded-lg border">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                               class="bg-white hover:bg-gray-50 text-gray-600 px-3 py-1 rounded-lg border">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>