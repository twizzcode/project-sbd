<?php
require_once __DIR__ . '/../includes/owner_auth.php';

$page_title = 'My Appointments';

$owner_id = $_SESSION['owner_id'];

// Get all appointments for this owner's pets
// Upcoming = belum ada rekam medis, Completed = sudah ada rekam medis
$result = mysqli_query($conn, "
    SELECT 
        a.*,
        p.nama_hewan,
        p.jenis,
        v.nama_dokter,
        v.spesialisasi,
        mr.record_id
    FROM appointment a
    JOIN pet p ON a.pet_id = p.pet_id
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    LEFT JOIN medical_record mr ON a.appointment_id = mr.appointment_id
    WHERE a.owner_id = '$owner_id'
    ORDER BY a.tanggal_appointment DESC
");

$appointments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Group by medical record status
$upcoming = [];
$completed = [];

foreach ($appointments as $apt) {
    if ($apt['record_id']) {
        // Sudah ada rekam medis = completed
        $completed[] = $apt;
    } else {
        // Belum ada rekam medis = upcoming
        $upcoming[] = $apt;
    }
}
?>
<?php require_once __DIR__ . '/../includes/owner_header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">My Appointments</h1>
            <p class="text-gray-600">Manage your pet's veterinary appointments</p>
        </div>
        <a href="book_appointment.php" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
            <i class="fas fa-plus mr-2"></i>Book New Appointment
        </a>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-8">
            <button onclick="showTab('upcoming')" class="tab-btn py-4 px-1 border-b-2 border-indigo-600 text-indigo-600 font-semibold" id="tab-upcoming">
                Upcoming (<?= count($upcoming) ?>)
            </button>
            <button onclick="showTab('completed')" class="tab-btn py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold" id="tab-completed">
                Completed (<?= count($completed) ?>)
            </button>
        </nav>
    </div>

    <!-- Upcoming Appointments -->
    <div id="content-upcoming" class="tab-content">
        <?php if (empty($upcoming)): ?>
            <div class="bg-white rounded-xl shadow p-12 text-center">
                <i class="fas fa-calendar-plus text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Upcoming Appointments</h3>
                <p class="text-gray-500 mb-6">Schedule a visit for your pet with our veterinarians</p>
                <a href="book_appointment.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-calendar-check mr-2"></i>Book Appointment
                </a>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($upcoming as $apt): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-<?= strtolower($apt['jenis']) === 'anjing' ? 'dog' : 'cat' ?> text-indigo-600 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($apt['nama_hewan']) ?></h3>
                                <span class="inline-block mt-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    Scheduled
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-indigo-600">
                                <?= date('d M Y', strtotime($apt['tanggal_appointment'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4 mt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500 mb-1">Doctor</p>
                                <p class="font-semibold text-gray-800">
                                    <i class="fas fa-user-md text-indigo-600 mr-2"></i>
                                    Dr. <?= htmlspecialchars($apt['nama_dokter']) ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 mb-1">Chief Complaint</p>
                                <p class="text-gray-800"><?= htmlspecialchars($apt['keluhan_awal'] ?? '-') ?></p>
                            </div>
                        </div>
                        <?php if ($apt['catatan']): ?>
                        <div class="mt-3">
                            <p class="text-gray-500 text-sm mb-1">Notes</p>
                            <p class="text-gray-700 text-sm"><?= htmlspecialchars($apt['catatan'] ?? '-') ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Completed Appointments -->
    <div id="content-completed" class="tab-content hidden">
        <?php if (empty($completed)): ?>
            <div class="bg-white rounded-xl shadow p-12 text-center">
                <i class="fas fa-check-circle text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Completed Appointments</h3>
                <p class="text-gray-500">Your appointment history will appear here</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($completed as $apt): ?>
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($apt['nama_hewan']) ?></h3>
                                <p class="text-gray-600"><?= htmlspecialchars($apt['jenis_layanan']) ?></p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Dr. <?= htmlspecialchars($apt['nama_dokter']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-600">
                            <?= date('d M Y', strtotime($apt['tanggal_appointment'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-indigo-600', 'text-indigo-600');
        el.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab
    document.getElementById('content-' + tab).classList.remove('hidden');
    document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-500');
    document.getElementById('tab-' + tab).classList.add('border-indigo-600', 'text-indigo-600');
}
</script>

<?php require_once __DIR__ . '/../includes/owner_footer.php'; ?>
