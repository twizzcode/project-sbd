<?php
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com cdn.datatables.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com cdn.datatables.net fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:");

$page_title = "Data Dokter Hewan";

// Build query conditions
$where_clauses = ["1=1"];
$params = [];

// Search filter
$search = $_GET['search'] ?? '';
if ($search) {
    $where_clauses[] = "(nama_dokter LIKE ? OR no_telepon LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

// Status filter
$status = $_GET['status'] ?? '';
if ($status && in_array($status, ['Aktif', 'Cuti', 'Resign'])) {
    $where_clauses[] = "status = ?";
    $params[] = $status;
}

// Build the final query
$where_clause = implode(" AND ", $where_clauses);

// Count total records for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM veterinarian WHERE $where_clause");
$stmt->execute($params);
$total_records = $stmt->fetchColumn();

// Pagination
$records_per_page = 10;
$total_pages = ceil($total_records / $records_per_page);
$current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Fetch veterinarians with pagination
$stmt = $pdo->prepare("
    SELECT 
        dokter_id as supplier_id,
        nama_dokter as nama_supplier,
        spesialisasi as jenis,
        no_telepon as kontak,
        email,
        jadwal_praktek as jadwal,
        status,
        no_lisensi,
        tanggal_bergabung,
        '' as alamat,
        0 as total_items
    FROM veterinarian
    WHERE $where_clause
    ORDER BY nama_dokter
    LIMIT ? OFFSET ?
");

$params[] = $records_per_page;
$params[] = $offset;
$stmt->execute($params);
$suppliers = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Data Dokter Hewan</h2>
            <p class="text-gray-600">Kelola data dokter hewan klinik</p>
        </div>
        
        <?php if (in_array($_SESSION['role'], ['Admin', 'Staff'])): ?>
            <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah Dokter
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cari Dokter
                </label>
                <input type="text" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Nama dokter, kontak, email..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status
                </label>
                <select name="status" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Status</option>
                    <option value="Aktif" <?php echo $status === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Cuti" <?php echo $status === 'Cuti' ? 'selected' : ''; ?>>Cuti</option>
                    <option value="Resign" <?php echo $status === 'Resign' ? 'selected' : ''; ?>>Resign</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
                <a href="?" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Suppliers List -->
    <div class="bg-white rounded-lg shadow-md">
        <?php if ($suppliers): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Nama Dokter</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Spesialisasi</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Kontak</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Email</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Jadwal Praktik</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($supplier['nama_supplier']); ?>
                                        </div>
                                        <?php if ($supplier['no_lisensi']): ?>
                                            <div class="text-xs text-gray-500">
                                                Lisensi: <?php echo htmlspecialchars($supplier['no_lisensi']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <?php if ($supplier['jenis']): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($supplier['jenis']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?php if ($supplier['kontak']): ?>
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-phone text-gray-400 mr-1"></i>
                                            <?php echo htmlspecialchars($supplier['kontak']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?php if ($supplier['email']): ?>
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                            <?php echo htmlspecialchars($supplier['email']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?php if ($supplier['jadwal']): ?>
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($supplier['jadwal']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?php
                                    $statusClass = match($supplier['status']) {
                                        'Aktif' => 'bg-green-100 text-green-800',
                                        'Cuti' => 'bg-yellow-100 text-yellow-800',
                                        'Resign' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($supplier['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex space-x-2">
                                        <a href="detail.php?id=<?php echo $supplier['supplier_id']; ?>" 
                                           class="text-blue-500 hover:text-blue-600" 
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (in_array($_SESSION['role'], ['Admin', 'Inventory', 'Staff'])): ?>
                                            <a href="detail.php?id=<?php echo $supplier['supplier_id']; ?>"
                                               class="text-blue-500 hover:text-blue-600"
                                               title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $supplier['supplier_id']; ?>"
                                               class="text-yellow-500 hover:text-yellow-600"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-between items-center p-4 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $records_per_page, $total_records); ?> 
                        dari <?php echo $total_records; ?> supplier
                    </div>
                    
                    <div class="flex space-x-2">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-gray-500 mb-2">Tidak ada data dokter hewan</div>
                <?php if (in_array($_SESSION['role'], ['Admin', 'Staff'])): ?>
                    <a href="create.php" class="text-blue-500 hover:text-blue-600">
                        Tambah dokter baru
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed z-10 inset-0 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Konfirmasi Hapus
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Apakah Anda yakin ingin menghapus supplier ini? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" action="delete.php" method="POST" class="inline">
                    <input type="hidden" name="supplier_id" id="deleteSupplierId">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(supplierId) {
    document.getElementById('deleteSupplierId').value = supplierId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php include '../includes/footer.php'; ?>