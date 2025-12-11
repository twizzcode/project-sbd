<?php
require_once __DIR__ . '/../includes/owner_auth.php';

$pet_id = $_GET['id'];
$months = $_GET['months'] ?? 12;
$owner_id = $_SESSION['owner_id'];

// Get pet details
$result = mysqli_query($conn, "SELECT p.*, 
    TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur_tahun,
    TIMESTAMPDIFF(MONTH, p.tanggal_lahir, CURDATE()) % 12 as umur_bulan
    FROM pet p WHERE p.pet_id = '$pet_id' AND p.owner_id = '$owner_id'");
$pet = mysqli_fetch_assoc($result);

if (!$pet) {
    header('Location: /owners/portal/index.php');
    exit;
}

// Get medical history
$result = mysqli_query($conn, "SELECT 'medical' as type, mr.tanggal_kunjungan as tanggal,
    mr.diagnosis as title, mr.keluhan as description, v.nama_dokter as dokter,
    mr.berat_badan as berat, mr.suhu_tubuh as suhu
    FROM medical_record mr
    JOIN veterinarian v ON mr.dokter_id = v.dokter_id
    WHERE mr.pet_id = '$pet_id'
    AND mr.tanggal_kunjungan >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
    ORDER BY mr.tanggal_kunjungan DESC");
$medical_records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $medical_records[] = $row;
}

// Get appointments
$result = mysqli_query($conn, "SELECT 'appointment' as type, a.tanggal_appointment as tanggal,
    'Appointment' as title, a.keluhan_awal as description, v.nama_dokter as dokter
    FROM appointment a
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE a.pet_id = '$pet_id'
    AND a.tanggal_appointment >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
    ORDER BY a.tanggal_appointment DESC");
$appointments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $appointments[] = $row;
}

// Get weight history
$result = mysqli_query($conn, "SELECT tanggal_kunjungan as tanggal, berat_badan as berat
    FROM medical_record WHERE pet_id = '$pet_id' 
    AND berat_badan IS NOT NULL
    AND tanggal_kunjungan >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
    ORDER BY tanggal_kunjungan ASC");
$weight_history = [];
while ($row = mysqli_fetch_assoc($result)) {
    $weight_history[] = $row;
}

// Combine all events for timeline
$timeline_events = array_merge($medical_records, $appointments);
usort($timeline_events, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// Calculate health stats
$total_visits = count($medical_records);
$upcoming_appointments = count($appointments);

$page_title = "Health Timeline - " . $pet['nama_hewan'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - VetClinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php require_once __DIR__ . '/../includes/owner_header.php'; ?>

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Back Button & Edit -->
        <div class="flex items-center justify-between mb-6">
            <a href="/owners/portal/index.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <a href="edit_pet.php?id=<?= $pet_id ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-edit mr-2"></i>Edit Pet Info
            </a>
        </div>

        <!-- Pet Header Card -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl shadow-xl p-8 text-white mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="flex items-center space-x-6 mb-4 md:mb-0">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                            <i class="fas fa-paw text-indigo-600 text-4xl"></i>
                        </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-2"><?= htmlspecialchars($pet['nama_hewan']) ?></h1>
                        <p class="text-xl text-indigo-100"><?= htmlspecialchars($pet['ras'] ?? '-') ?></p>
                        <div class="flex items-center space-x-4 mt-2 text-sm">
                            <span><i class="fas fa-venus-mars mr-1"></i><?= $pet['jenis_kelamin'] ?></span>
                            <span><i class="fas fa-birthday-cake mr-1"></i><?= $pet['umur_tahun'] ?>y <?= $pet['umur_bulan'] ?>m</span>
                            <span><i class="fas fa-weight mr-1"></i><?= $pet['berat_badan'] ?> kg</span>
                        </div>
                    </div>
                </div>
                
                <!-- Time Period Filter -->
                <div class="flex space-x-2">
                    <a href="?id=<?= $pet_id ?>&months=3" class="px-4 py-2 <?= $months == 3 ? 'bg-white text-indigo-600' : 'bg-indigo-400 text-white hover:bg-indigo-300' ?> rounded-lg transition">3M</a>
                    <a href="?id=<?= $pet_id ?>&months=6" class="px-4 py-2 <?= $months == 6 ? 'bg-white text-indigo-600' : 'bg-indigo-400 text-white hover:bg-indigo-300' ?> rounded-lg transition">6M</a>
                    <a href="?id=<?= $pet_id ?>&months=12" class="px-4 py-2 <?= $months == 12 ? 'bg-white text-indigo-600' : 'bg-indigo-400 text-white hover:bg-indigo-300' ?> rounded-lg transition">12M</a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Total Visits</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $total_visits ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-stethoscope text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Upcoming</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $upcoming_appointments ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 gap-8 mb-8">
            <!-- Weight Progress Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-weight text-green-600 mr-2"></i>
                    Weight Progress
                </h3>
                <div style="height: 300px;">
                    <canvas id="weightChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Timeline Events List -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">
                <i class="fas fa-history text-indigo-600 mr-2"></i>
                Complete Health History
            </h3>

            <?php if (empty($timeline_events)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-inbox text-6xl mb-4 opacity-20"></i>
                    <p>No health records found for the selected period</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($timeline_events as $event): 
                        $icon_colors = [
                            'medical' => ['icon' => 'stethoscope', 'bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                            'appointment' => ['icon' => 'calendar-check', 'bg' => 'bg-purple-100', 'text' => 'text-purple-600']
                        ];
                        $colors = $icon_colors[$event['type']];
                    ?>
                        <div class="flex items-start space-x-4 p-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="<?= $colors['bg'] ?> w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-<?= $colors['icon'] ?> <?= $colors['text'] ?>"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($event['title']) ?></h4>
                                    <span class="text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($event['tanggal'])) ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($event['description'] ?? '-') ?></p>
                                <p class="text-gray-500 text-xs mt-1">
                                    <i class="fas fa-user-md mr-1"></i><?= htmlspecialchars($event['dokter']) ?>
                                </p>
                                <?php if ($event['type'] === 'medical' && isset($event['berat'])): ?>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                        <?php if ($event['berat']): ?>
                                            <span><i class="fas fa-weight mr-1"></i><?= $event['berat'] ?> kg</span>
                                        <?php endif; ?>
                                        <?php if (isset($event['suhu']) && $event['suhu']): ?>
                                            <span><i class="fas fa-thermometer-half mr-1"></i><?= $event['suhu'] ?>°C</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Prepare data for charts
    const weightData = <?= json_encode($weight_history) ?>;

    // Weight Chart
    const weightCtx = document.getElementById('weightChart');
    if (weightData.length > 0) {
        new Chart(weightCtx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Weight (kg)',
                    data: weightData.map(r => ({x: r.tanggal, y: parseFloat(r.berat)})),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'month',
                            displayFormats: {
                                month: 'MMM yyyy'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Weight (kg)'
                        },
                        beginAtZero: false
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const date = new Date(context[0].parsed.x);
                                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                            },
                            label: function(context) {
                                return 'Weight: ' + context.parsed.y.toFixed(1) + ' kg';
                            }
                        }
                    }
                }
            }
        });
    } else {
        weightCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400"><div class="text-center"><i class="fas fa-weight text-6xl mb-3 opacity-20"></i><p class="text-lg">No weight data available</p></div></div>';
    }
    </script>

    <?php require_once __DIR__ . '/../includes/owner_footer.php'; ?>
</body>
</html>
