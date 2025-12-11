<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Get owner ID
$owner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get owner data with related information
$result = mysqli_query($conn, "
    SELECT o.*, 
           COUNT(DISTINCT p.pet_id) as total_pets,
           COUNT(DISTINCT a.appointment_id) as total_appointments
    FROM users o
    LEFT JOIN pet p ON o.user_id = p.owner_id
    LEFT JOIN appointment a ON o.user_id = a.owner_id
    WHERE o.user_id = ? AND o.role = 'Owner'
    GROUP BY o.user_id
");

$owner = mysqli_fetch_assoc($result);

if (!$owner) {
    $_SESSION['error'] = 'Data pemilik tidak ditemukan';
    header('Location: index.php');
    exit;
}

// Get owner's pets
$result = mysqli_query($conn, "
    SELECT p.*, 
           COUNT(DISTINCT a.appointment_id) as total_visits
    FROM pet p
    LEFT JOIN appointment a ON p.pet_id = a.pet_id
    WHERE p.owner_id = ?
    GROUP BY p.pet_id
    ORDER BY p.tanggal_registrasi DESC
");

$pets = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get recent appointments
$result = mysqli_query($conn, "
    SELECT a.*,
           p.nama_hewan,
           v.nama_dokter
    FROM appointment a
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE a.owner_id = ?
    GROUP BY a.appointment_id
    ORDER BY a.tanggal_appointment DESC
    LIMIT 5
");

$recent_appointments = mysqli_fetch_all($result, MYSQLI_ASSOC);

$page_title = 'Detail Pemilik: ' . $owner['nama_lengkap'];

include '../../includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Owner Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($owner['nama_lengkap']); ?></h2>
                        <p class="text-gray-600">
                            Terdaftar: <?php echo isset($owner['tanggal_registrasi']) ? format_tanggal($owner['tanggal_registrasi']) : '-'; ?>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="edit.php?id=<?php echo $owner['user_id']; ?>" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>
                        <button onclick="confirmDelete(<?php echo $owner['user_id']; ?>, '<?php echo htmlspecialchars($owner['nama_lengkap']); ?>')"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-trash mr-2"></i> Hapus
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Informasi Kontak</h3>
                        <table class="w-full">
                            <tr>
                                <td class="py-2 text-gray-600">No. Telepon:</td>
                                <td class="py-2 font-medium"><?php echo htmlspecialchars($owner['no_telepon']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600">Email:</td>
                                <td class="py-2 font-medium"><?php echo htmlspecialchars($owner['email'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600">Alamat:</td>
                                <td class="py-2 font-medium"><?php echo nl2br(htmlspecialchars($owner['alamat'] ?? '-')); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-3">Statistik</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-500 p-4 rounded-lg">
                                <p class="text-sm text-blue-100">Total Hewan</p>
                                <p class="text-2xl font-bold text-white"><?php echo $owner['total_pets']; ?></p>
                            </div>
                            <div class="bg-blue-500 p-4 rounded-lg">
                                <p class="text-sm text-blue-100">Total Kunjungan</p>
                                <p class="text-2xl font-bold text-white"><?php echo $owner['total_appointments']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($owner['catatan']): ?>
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-3">Catatan</h3>
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($owner['catatan'] ?? '-')); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pets List -->
        <div class="bg-white rounded-lg shadow-md mt-6">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Daftar Hewan</h3>
                <a href="/vetclinic/pets/create.php?owner_id=<?php echo $owner_id; ?>" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i> Tambah Hewan
                </a>
            </div>
            <div class="p-4">
                <?php if (empty($pets)): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada data hewan</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($pets as $pet): ?>
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-semibold text-lg">
                                            <?php echo htmlspecialchars($pet['nama_hewan']); ?>
                                            <?php if ($pet['status'] === 'Meninggal'): ?>
                                                <span class="text-sm text-gray-500">(Meninggal)</span>
                                            <?php endif; ?>
                                        </h4>
                                        <p class="text-gray-600">
                                            <?php echo htmlspecialchars($pet['jenis']); ?> - 
                                            <?php echo htmlspecialchars($pet['ras'] ?? 'Tidak ada ras'); ?>
                                        </p>
                                    </div>
                                    <?php if ($pet['foto_url']): ?>
                                        <?php 
                                        $pet_foto = (strpos($pet['foto_url'], 'http') === 0) 
                                            ? $pet['foto_url'] 
                                            : '/vetclinic/assets/images/uploads/' . $pet['foto_url'];
                                        ?>
                                        <img src="<?php echo $pet_foto; ?>" 
                                             alt="<?php echo htmlspecialchars($pet['nama_hewan']); ?>"
                                             class="w-16 h-16 rounded-full object-cover"
                                             onerror="this.src='https://via.placeholder.com/64?text=Pet'">
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 flex gap-4 text-sm">
                                    <span class="text-gray-600">
                                        <i class="fas fa-calendar-check"></i> <?php echo $pet['total_visits']; ?> kunjungan
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <a href="/vetclinic/pets/detail.php?id=<?php echo $pet['pet_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-4 border-b">
                <h3 class="text-lg font-semibold">Aktivitas Terakhir</h3>
            </div>
            <div class="p-4">
                <?php if (empty($recent_appointments)): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada aktivitas</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_appointments as $appt): ?>
                            <div class="border-l-4 <?php echo get_appointment_border_color($appt['status']); ?> pl-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">
                                            <?php echo htmlspecialchars($appt['nama_hewan']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo format_tanggal($appt['tanggal_appointment']); ?> 
                                            <?php echo date('H:i', strtotime($appt['jam_appointment'])); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($appt['layanan']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <?php echo get_status_badge($appt['status']); ?>
                                    </div>
                                </div>
                                <div class="mt-2 text-sm">
                                    <span class="text-gray-600">
                                        <i class="fas fa-user-md"></i> <?php echo htmlspecialchars($appt['nama_dokter']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-4 text-center">
                    <a href="/vetclinic/appointments/create.php?owner_id=<?php echo $owner_id; ?>" 
                       class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-calendar-plus mr-2"></i> Buat Janji Temu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus data pemilik "${name}"? Semua data hewan dan riwayat kunjungan akan ikut terhapus.`,
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

<?php
function get_appointment_border_color($status) {
    switch ($status) {
        case 'Pending':
            return 'border-yellow-500';
        case 'Confirmed':
            return 'border-blue-500';
        case 'Completed':
            return 'border-green-500';
        case 'Cancelled':
            return 'border-red-500';
        case 'No_Show':
            return 'border-gray-500';
        default:
            return 'border-gray-300';
    }
}
?>
</script>

<?php include '../../includes/footer.php'; ?>