<?php
require_once __DIR__ . '/../includes/owner_auth.php';
require_once __DIR__ . '/../../includes/owner_helper.php';

$pet_id = $_GET['id'] ?? 0;
$months = $_GET['months'] ?? 12; // Default 12 months view

// Get pet details
$stmt = $pdo->prepare("
    SELECT p.*, 
           TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur_tahun,
           TIMESTAMPDIFF(MONTH, p.tanggal_lahir, CURDATE()) % 12 as umur_bulan
    FROM pet p
    WHERE p.pet_id = ? AND p.owner_id = ?
");
$stmt->execute([$pet_id, $_SESSION['owner_id']]);
$pet = $stmt->fetch();

if (!$pet) {
    header('Location: /owners/portal/index.php');
    exit;
}

// Get medical history for timeline
$stmt = $pdo->prepare("
    SELECT 
        'medical' as type,
        mr.tanggal_kunjungan as tanggal,
        mr.diagnosa as title,
        mr.keluhan as description,
        v.nama_dokter as dokter,
        mr.berat_badan_saat_periksa as berat,
        mr.suhu_tubuh as suhu
    FROM medical_record mr
    JOIN veterinarian v ON mr.dokter_id = v.dokter_id
    WHERE mr.pet_id = ?
    AND mr.tanggal_kunjungan >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
    ORDER BY mr.tanggal_kunjungan DESC
");
$stmt->execute([$pet_id, $months]);
$medical_records = $stmt->fetchAll();

// Get vaccination history
$stmt = $pdo->prepare("
    SELECT 
        'vaccination' as type,
        vak.tanggal_vaksin as tanggal,
        vak.jenis_vaksin as title,
        vak.catatan as description,
        v.nama_dokter as dokter,
        vak.tanggal_vaksin_berikutnya as next_date
    FROM vaksinasi vak
    JOIN veterinarian v ON vak.dokter_id = v.dokter_id
    WHERE vak.pet_id = ?
    AND vak.tanggal_vaksin >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
    ORDER BY vak.tanggal_vaksin DESC
");
$stmt->execute([$pet_id, $months]);
$vaccinations = $stmt->fetchAll();

// Get appointments
$stmt = $pdo->prepare("
    SELECT 
        'appointment' as type,
        a.tanggal_appointment as tanggal,
        a.jenis_layanan as title,
        a.keluhan_awal as description,
        v.nama_dokter as dokter,
        a.status
    FROM appointment a
    JOIN veterinarian v ON a.dokter_id = v.dokter_id
    WHERE a.pet_id = ?
    AND a.tanggal_appointment >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
    ORDER BY a.tanggal_appointment DESC
");
$stmt->execute([$pet_id, $months]);
$appointments = $stmt->fetchAll();

// Get weight history
$stmt = $pdo->prepare("
    SELECT 
        tanggal_kunjungan as tanggal,
        berat_badan_saat_periksa as berat
    FROM medical_record
    WHERE pet_id = ? 
    AND berat_badan_saat_periksa IS NOT NULL
    AND tanggal_kunjungan >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
    ORDER BY tanggal_kunjungan ASC
");
$stmt->execute([$pet_id, $months]);
$weight_history = $stmt->fetchAll();

// Combine all events for timeline
$timeline_events = array_merge($medical_records, $vaccinations, $appointments);
usort($timeline_events, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// Calculate health stats
$total_visits = count($medical_records);
$total_vaccinations = count($vaccinations);
$upcoming_appointments = count(array_filter($appointments, function($a) {
    return $a['status'] != 'Completed' && $a['status'] != 'Cancelled';
}));

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
                    <?php if ($pet['foto_url']): ?>
                        <?php 
                        // Check if foto_url is external URL or local path
                        $foto_src = (strpos($pet['foto_url'], 'http') === 0) 
                            ? $pet['foto_url'] 
                            : '/uploads/' . $pet['foto_url'];
                        ?>
                        <img src="<?= $foto_src ?>" 
                             alt="<?= htmlspecialchars($pet['nama_hewan']) ?>"
                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-24 h-24 bg-white rounded-full flex items-center justify-center\'><i class=\'fas fa-<?= strtolower($pet['jenis']) === 'anjing' ? 'dog' : (strtolower($pet['jenis']) === 'kucing' ? 'cat' : 'paw') ?> text-indigo-600 text-4xl\'></i></div>';">
                    <?php else: ?>
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                            <i class="fas fa-<?= strtolower($pet['jenis']) === 'anjing' ? 'dog' : (strtolower($pet['jenis']) === 'kucing' ? 'cat' : 'paw') ?> text-indigo-600 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-4xl font-bold mb-2"><?= htmlspecialchars($pet['nama_hewan']) ?></h1>
                        <p class="text-xl text-indigo-100"><?= htmlspecialchars($pet['ras']) ?></p>
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
                        <p class="text-gray-600 text-sm mb-1">Vaccinations</p>
                        <p class="text-3xl font-bold text-gray-800"><?= $total_vaccinations ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-syringe text-green-600 text-xl"></i>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Health Timeline Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                    Health Timeline
                </h3>
                <div style="height: 300px;">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>

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
                            'vaccination' => ['icon' => 'syringe', 'bg' => 'bg-green-100', 'text' => 'text-green-600'],
                            'appointment' => ['icon' => 'calendar-check', 'bg' => 'bg-purple-100', 'text' => 'text-purple-600']
                        ];
                        $style = $icon_colors[$event['type']];
                    ?>
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="w-12 h-12 <?= $style['bg'] ?> rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-<?= $style['icon'] ?> <?= $style['text'] ?>"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($event['title']) ?></h4>
                                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($event['description'] ?? '') ?></p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="fas fa-user-md mr-1"></i>Dr. <?= htmlspecialchars($event['dokter']) ?>
                                        <?php if (isset($event['berat'])): ?>
                                            <span class="ml-3"><i class="fas fa-weight mr-1"></i><?= $event['berat'] ?> kg</span>
                                        <?php endif; ?>
                                        <?php if (isset($event['suhu'])): ?>
                                            <span class="ml-3"><i class="fas fa-thermometer-half mr-1"></i><?= $event['suhu'] ?>°C</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="text-sm text-gray-500 whitespace-nowrap">
                                    <?= formatIndonesianDate($event['tanggal']) ?>
                                </span>
                            </div>
                            <?php if ($event['type'] === 'vaccination' && isset($event['next_date'])): ?>
                                <div class="mt-2 text-xs text-amber-600">
                                    <i class="fas fa-bell mr-1"></i>Next: <?= formatIndonesianDate($event['next_date']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($event['type'] === 'appointment' && isset($event['status'])): ?>
                                <div class="mt-2">
                                    <span class="text-xs px-2 py-1 rounded <?= $event['status'] === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <?= $event['status'] ?>
                                    </span>
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
    const medicalData = <?= json_encode($medical_records) ?>;
    const vaccinationData = <?= json_encode($vaccinations) ?>;
    const appointmentData = <?= json_encode($appointments) ?>;
    const weightData = <?= json_encode($weight_history) ?>;

    // Check if we have any timeline data
    const hasTimelineData = medicalData.length > 0 || vaccinationData.length > 0 || appointmentData.length > 0;

    // Timeline Chart
    const timelineCtx = document.getElementById('timelineChart');
    if (hasTimelineData) {
        // Combine all events with proper formatting
        const allEvents = [];
        
        medicalData.forEach(item => {
            allEvents.push({
                x: item.tanggal,
                y: 'Medical Visit',
                label: item.title,
                doctor: item.dokter
            });
        });
        
        vaccinationData.forEach(item => {
            allEvents.push({
                x: item.tanggal,
                y: 'Vaccination',
                label: item.title,
                doctor: item.dokter
            });
        });
        
        appointmentData.forEach(item => {
            allEvents.push({
                x: item.tanggal,
                y: 'Appointment',
                label: item.title,
                doctor: item.dokter
            });
        });

        new Chart(timelineCtx, {
            type: 'bar',
            data: {
                datasets: [{
                    label: 'Medical Visits',
                    data: medicalData.map(r => ({x: r.tanggal, y: 1})),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 2,
                    barThickness: 20
                }, {
                    label: 'Vaccinations',
                    data: vaccinationData.map(r => ({x: r.tanggal, y: 1})),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 2,
                    barThickness: 20
                }, {
                    label: 'Appointments',
                    data: appointmentData.map(r => ({x: r.tanggal, y: 1})),
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                    borderColor: 'rgb(168, 85, 247)',
                    borderWidth: 2,
                    barThickness: 20
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
                        stacked: false,
                        title: {
                            display: true,
                            text: 'Timeline'
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return value === 1 ? 'Events' : '';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Health Events'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const date = new Date(context[0].parsed.x);
                                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                            },
                            label: function(context) {
                                return context.dataset.label;
                            }
                        }
                    }
                }
            }
        });
    } else {
        timelineCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400"><div class="text-center"><i class="fas fa-chart-line text-6xl mb-3 opacity-20"></i><p class="text-lg">No health events in selected period</p></div></div>';
    }

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
