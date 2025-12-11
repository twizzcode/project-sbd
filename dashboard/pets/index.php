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
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';

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
    GROUP BY p.pet_id
";

$result = mysqli_query($conn, $query);
$pets = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../../includes/header.php';
?>

<!-- Content Header -->
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <h2 class="text-2xl font-bold text-gray-800">Daftar Hewan</h2>
    
    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i> Tambah Hewan
    </a>
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

<script>
function confirmDelete(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus data hewan "${name}"?`)) {
        window.location.href = `delete.php?id=${id}`;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
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


<script>
function confirmDelete(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus data hewan "${name}"?`)) {
        window.location.href = `delete.php?id=${id}`;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>