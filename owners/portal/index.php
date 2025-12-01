<?php
require_once __DIR__ . '/../includes/owner_auth.php';
require_once __DIR__ . '/../../includes/owner_helper.php';

$page_title = 'My Pets Dashboard';

// Get all pets with health summary
$pets = getOwnerPetsWithHealth($pdo, $_SESSION['owner_id']);

// Calculate statistics
$total_pets = count($pets);
$upcoming_appointments = 0;

foreach ($pets as $pet) {
    if ($pet['next_appointment']) {
        $days_until = (new DateTime($pet['next_appointment']))->diff(new DateTime())->days;
        if ($days_until <= 7) $upcoming_appointments++;
    }
}

require_once __DIR__ . '/../includes/owner_header.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-8">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-2">Welcome back, <?= htmlspecialchars($_SESSION['owner_name']) ?>!</h1>
            <p class="text-indigo-100">Manage your pets' health and appointments</p>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Pets -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover-scale">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Pets</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1"><?= $total_pets ?></p>
                    </div>
                    <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-paw text-indigo-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover-scale">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Upcoming Visits</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1"><?= $upcoming_appointments ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- AI Assistant - NEW -->
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg p-6 hover-scale cursor-pointer" onclick="window.location.href='chatbot.php'">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-sm font-medium mb-1">AI Assistant</p>
                        <p class="text-white text-lg font-bold">Ask PetCare AI</p>
                    </div>
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-robot text-white text-2xl"></i>
                    </div>
                </div>
                <p class="text-white text-xs mt-3 opacity-90">
                    <i class="fas fa-sparkles mr-1"></i>Get instant answers about pet health
                </p>
            </div>
        </div>

        <!-- Pets Grid -->
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">My Pets</h2>
            <a href="add_pet.php" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
                <i class="fas fa-plus mr-2"></i>Add New Pet
            </a>
        </div>

        <?php if (empty($pets)): ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <i class="fas fa-paw text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Pets Yet</h3>
            <p class="text-gray-500 mb-6">Start by registering your first pet to access all features</p>
            <a href="add_pet.php" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-lg hover:shadow-xl">
                <i class="fas fa-plus mr-2"></i>
                Register Your First Pet
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($pets as $pet): 
                $health_status = getPetHealthStatus($pet);
            ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow hover-scale fade-in">
                <!-- Pet Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-6 text-white">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold"><?= htmlspecialchars($pet['nama_hewan']) ?></h3>
                            <p class="text-indigo-100"><?= htmlspecialchars($pet['ras'] ?? '-') ?></p>
                        </div>
                        <div class="ml-4">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                                <i class="fas fa-paw text-indigo-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 text-sm">
                        <span><i class="fas fa-venus-mars mr-1"></i><?= $pet['jenis_kelamin'] ?></span>
                        <span><i class="fas fa-birthday-cake mr-1"></i><?= $pet['umur_tahun'] ?>y <?= $pet['umur_bulan'] ?>m</span>
                    </div>
                </div>

                <!-- Pet Info -->
                <div class="p-6">
                    <!-- Health Status Badge -->
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $health_status['class'] ?>">
                            <i class="fas fa-heartbeat mr-1"></i>
                            <?= $health_status['label'] ?>
                        </span>
                    </div>

                    <!-- Health Stats -->
                    <div class="space-y-3 mb-4">
                        <?php if ($pet['last_checkup']): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-stethoscope text-gray-400 w-5"></i>
                            <span class="text-gray-600 ml-2">Last Checkup:</span>
                            <span class="text-gray-800 ml-auto font-medium">
                                <?= formatIndonesianDate($pet['last_checkup']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if ($pet['next_appointment']): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-calendar text-gray-400 w-5"></i>
                            <span class="text-gray-600 ml-2">Next Visit:</span>
                            <span class="text-gray-800 ml-auto font-medium">
                                <?= formatIndonesianDate($pet['next_appointment']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <div class="flex items-center text-sm">
                            <i class="fas fa-weight text-gray-400 w-5"></i>
                            <span class="text-gray-600 ml-2">Weight:</span>
                            <span class="text-gray-800 ml-auto font-medium"><?= $pet['berat_badan'] ?> kg</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <a href="pet_profile.php?id=<?= $pet['pet_id'] ?>" 
                       class="block w-full text-center px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">
                        <i class="fas fa-eye mr-2"></i>
                        View Profile
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/owner_footer.php'; ?>
