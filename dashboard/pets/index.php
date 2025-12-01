<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Pet management is restricted to staff only (not Owner role)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Owner') {
    header('Location: /owners/portal/');
    exit();
}

$page_title = 'Daftar Hewan';

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get search term
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$jenis = isset($_GET['jenis']) ? clean_input($_GET['jenis']) : '';

// Build query
$query = "
    SELECT 
        p.*,
        o.nama_lengkap as owner_name,
        o.no_telepon as owner_phone,
        COUNT(DISTINCT a.appointment_id) as total_visits
    FROM pet p
    JOIN users o ON p.owner_id = o.user_id
    LEFT JOIN appointment a ON p.pet_id = a.pet_id
";

$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(p.nama_hewan LIKE ? OR o.nama_lengkap LIKE ? OR o.no_telepon LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if ($status) {
    $conditions[] = "p.status = ?";
    $params[] = $status;
}

if ($jenis) {
    $conditions[] = "p.jenis = ?";
    $params[] = $jenis;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " GROUP BY p.pet_id ORDER BY p.tanggal_registrasi DESC";

// Get total records for pagination
$count_query = "SELECT COUNT(DISTINCT p.pet_id) as total FROM pet p JOIN users o ON p.owner_id = o.user_id";
if (!empty($conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $conditions);
}

$total_stmt = $pdo->prepare($count_query);
$total_stmt->execute($params);
$total_records = $total_stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Get pets with limit
$query .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pets = $stmt->fetchAll();

// Get distinct jenis for filter
$jenis_list = $pdo->query("SELECT DISTINCT jenis FROM pet ORDER BY jenis")->fetchAll(PDO::FETCH_COLUMN);

include '../../includes/header.php';
?>

<!-- Content Header -->
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <h2 class="text-2xl font-bold text-gray-800">Daftar Hewan</h2>
    
    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i> Tambah Hewan
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Cari nama hewan/pemilik...">
        </div>
        <div>
            <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="Aktif" <?php echo $status === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                <option value="Meninggal" <?php echo $status === 'Meninggal' ? 'selected' : ''; ?>>Meninggal</option>
            </select>
        </div>
        <div>
            <select name="jenis" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Jenis</option>
                <?php foreach ($jenis_list as $j): ?>
                    <option value="<?php echo $j; ?>" <?php echo $jenis === $j ? 'selected' : ''; ?>>
                        <?php echo $j; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-search mr-2"></i> Cari
            </button>
            <?php if ($search || $status || $jenis): ?>
                <a href="?" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Pets Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($pets as $pet): ?>
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
        <div class="p-4">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        <?php echo htmlspecialchars($pet['nama_hewan']); ?>
                    </h3>
                    <p class="text-gray-600">
                        <?php echo htmlspecialchars($pet['jenis']); ?> - 
                        <?php echo htmlspecialchars($pet['ras'] ?? 'Tidak ada ras'); ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                    <i class="fas fa-paw text-xl"></i>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <p class="text-gray-500">Status</p>
                        <p class="font-medium"><?php echo get_status_badge($pet['status']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Jenis Kelamin</p>
                        <p class="font-medium">
                            <?php if ($pet['jenis_kelamin'] === 'Jantan'): ?>
                                <i class="fas fa-mars text-blue-500"></i>
                            <?php else: ?>
                                <i class="fas fa-venus text-pink-500"></i>
                            <?php endif; ?>
                            <?php echo $pet['jenis_kelamin']; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Kunjungan</p>
                        <p class="font-medium"><?php echo $pet['total_visits']; ?> kali</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <p class="text-gray-500 text-sm mb-1">Pemilik</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pet['owner_name']); ?></p>
                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($pet['owner_phone']); ?></p>
                </div>
            </div>

            <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between">
                <a href="detail.php?id=<?php echo $pet['pet_id']; ?>" 
                   class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-eye"></i> Detail
                </a>
                <div class="flex gap-2">
                    <a href="edit.php?id=<?php echo $pet['pet_id']; ?>" 
                       class="text-yellow-600 hover:text-yellow-800">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button onclick="confirmDelete(<?php echo $pet['pet_id']; ?>, '<?php echo $pet['nama_hewan']; ?>')"
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($pets)): ?>
    <div class="col-span-full text-center py-8 text-gray-500">
        <i class="fas fa-paw text-4xl mb-2"></i>
        <p>Tidak ada data hewan</p>
    </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="mt-6 bg-white rounded-lg shadow-md px-4 py-3 flex items-center justify-between">
    <div class="flex-1 flex justify-between sm:hidden">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? "&search=$search" : ''; ?><?php echo $status ? "&status=$status" : ''; ?><?php echo $jenis ? "&jenis=$jenis" : ''; ?>" 
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Previous
            </a>
        <?php endif; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? "&search=$search" : ''; ?><?php echo $status ? "&status=$status" : ''; ?><?php echo $jenis ? "&jenis=$jenis" : ''; ?>" 
               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Next
            </a>
        <?php endif; ?>
    </div>
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing 
                <span class="font-medium"><?php echo $offset + 1; ?></span>
                to 
                <span class="font-medium"><?php echo min($offset + $per_page, $total_records); ?></span>
                of 
                <span class="font-medium"><?php echo $total_records; ?></span>
                results
            </p>
        </div>
        <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? "&search=$search" : ''; ?><?php echo $status ? "&status=$status" : ''; ?><?php echo $jenis ? "&jenis=$jenis" : ''; ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1) {
                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                }

                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? "&search=$search" : ''; ?><?php echo $status ? "&status=$status" : ''; ?><?php echo $jenis ? "&jenis=$jenis" : ''; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? "&search=$search" : ''; ?><?php echo $status ? "&status=$status" : ''; ?><?php echo $jenis ? "&jenis=$jenis" : ''; ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus data hewan "${name}"?`,
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
</script>

<?php include '../../includes/footer.php'; ?>