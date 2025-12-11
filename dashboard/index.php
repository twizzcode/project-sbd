<?php
session_start();
require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect Owner to portal
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Owner') {
    header('Location: /owners/portal/');
    exit();
}

// Redirect Admin to appointments
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    header('Location: /dashboard/appointments/');
    exit();
}

// Default redirect
header('Location: /auth/login.php');
exit();
?>

$page_title = 'Janji Temu';

// Initialize variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d', strtotime('+7 days'));
$dokter_id = isset($_GET['dokter_id']) ? (int)$_GET['dokter_id'] : 0;

// Build query
$query = "SELECT a.*, p.nama_hewan, o.nama_lengkap as owner_name,
    o.no_telepon as owner_phone, v.nama_dokter as dokter_name, a.jenis_layanan as nama_layanan
    FROM appointment a
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN users o ON a.owner_id = o.user_id
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE 1=1";

if ($search) {
    $query .= " AND (p.nama_hewan LIKE '%$search%' OR o.nama_lengkap LIKE '%$search%' OR o.no_telepon LIKE '%$search%')";
}
if ($status) {
    $query .= " AND a.status = '$status'";
}
if ($date_from && $date_to) {
    $query .= " AND a.tanggal_appointment BETWEEN '$date_from' AND '$date_to'";
}
if ($dokter_id) {
    $query .= " AND a.dokter_id = '$dokter_id'";
}

// Get total records
$count_query = str_replace("SELECT a.*, p.nama_hewan", "SELECT COUNT(*)", $query);
$result = mysqli_query($conn, $count_query);
$row = mysqli_fetch_row($result);
$total_records = $row[0];
$total_pages = ceil($total_records / $per_page);

// Add sorting and limit
$query .= " ORDER BY a.tanggal_appointment ASC LIMIT $per_page OFFSET $offset";

// Execute query
$result = mysqli_query($conn, $query);
$appointments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $appointments[] = $row;
}

// Get all doctors
$doctors_result = mysqli_query($conn, "SELECT dokter_id, nama_dokter FROM veterinarian WHERE status = 'Aktif' ORDER BY nama_dokter");
$doctors = [];
while ($row = mysqli_fetch_assoc($doctors_result)) {
    $doctors[] = $row;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Janji Temu</h2>
        <div class="flex gap-2">
            <a href="calendar.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-calendar-alt mr-2"></i> Kalender
            </a>
            <a href="create.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i> Buat Janji
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Nama/Telepon...">
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Confirmed" <?php echo $status === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="No_Show" <?php echo $status === 'No_Show' ? 'selected' : ''; ?>>No Show</option>
                </select>
            </div>

            <!-- Doctor -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dokter</label>
                <select name="dokter_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Dokter</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo $doctor['dokter_id']; ?>"
                                <?php echo $dokter_id === $doctor['dokter_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($doctor['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="md:col-span-3 lg:col-span-5 flex gap-2 justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <?php if ($search || $status || $dokter_id || $date_from != date('Y-m-d') || $date_to != date('Y-m-d', strtotime('+7 days'))): ?>
                    <a href="?" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-times mr-2"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Appointments List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal & Jam
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pasien & Pemilik
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Layanan & Dokter
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data janji temu
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($appointment['tanggal_appointment'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('H:i', strtotime($appointment['jam_appointment'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($appointment['nama_hewan']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($appointment['owner_name']); ?> -
                                        <?php echo htmlspecialchars($appointment['owner_phone']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($appointment['nama_layanan']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($appointment['dokter_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo get_appointment_status_badge($appointment['status']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="detail.php?id=<?php echo $appointment['appointment_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $appointment['appointment_id']; ?>"
                                       class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $appointment['appointment_id']; ?>, '<?php echo htmlspecialchars($appointment['nama_hewan']); ?>')"
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_records); ?> 
                of <?php echo $total_records; ?> entries
            </div>
            <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&dokter_id=<?php echo $dokter_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                       class="px-4 py-2 text-gray-700 bg-white border rounded-md hover:bg-blue-50">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&dokter_id=<?php echo $dokter_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                       class="px-4 py-2 text-gray-700 bg-white border rounded-md hover:bg-blue-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus janji temu untuk ${name}?`,
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

// Enable DataTables
$(document).ready(function() {
    $('.datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        },
        pageLength: 10,
        ordering: true,
        responsive: true
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>