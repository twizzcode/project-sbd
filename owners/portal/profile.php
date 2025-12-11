<?php
require_once __DIR__ . '/../includes/owner_auth.php';

$page_title = 'My Profile';

$owner_id = $_SESSION['owner_id'];

// Get owner details
$result = mysqli_query($conn, "
    SELECT * 
    FROM users 
    WHERE user_id = '$owner_id' AND role = 'Owner'
");

$owner = mysqli_fetch_assoc($result);

// Count pets
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM pet WHERE owner_id = '$owner_id'");
$pet_count = mysqli_fetch_row($result)[0];

// Count appointments
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointment WHERE owner_id = '$owner_id'");
$appointment_count = mysqli_fetch_row($result)[0];
?>
<?php require_once __DIR__ . '/../includes/owner_header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">My Profile</h1>
        <p class="text-gray-600">View and manage your account information</p>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-8 text-white">
            <div class="flex items-center space-x-6">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                    <span class="text-5xl font-bold text-indigo-600">
                        <?= strtoupper(substr($owner['nama_lengkap'], 0, 1)) ?>
                    </span>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-2"><?= htmlspecialchars($owner['nama_lengkap']) ?></h2>
                    <p class="text-indigo-100">Pet Owner</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div class="text-center p-6 bg-indigo-50 rounded-xl">
                    <div class="text-4xl font-bold text-indigo-600 mb-2"><?= $pet_count ?></div>
                    <div class="text-gray-600">Total Pets</div>
                </div>
                <div class="text-center p-6 bg-purple-50 rounded-xl">
                    <div class="text-4xl font-bold text-purple-600 mb-2"><?= $appointment_count ?></div>
                    <div class="text-gray-600">Total Appointments</div>
                </div>
            </div>

            <!-- Contact Information -->
            <h3 class="text-xl font-bold text-gray-800 mb-4">Contact Information</h3>
            <div class="space-y-4 mb-8">
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-envelope text-indigo-600 text-xl mr-4 w-8"></i>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($owner['email'] ?? '-') ?></p>
                    </div>
                </div>
                
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-phone text-indigo-600 text-xl mr-4 w-8"></i>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($owner['no_telepon']) ?></p>
                    </div>
                </div>
                
                <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-map-marker-alt text-indigo-600 text-xl mr-4 w-8 mt-1"></i>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="text-gray-800"><?= htmlspecialchars($owner['alamat'] ?? 'Not provided') ?></p>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <h3 class="text-xl font-bold text-gray-800 mb-4">Account Information</h3>
            <div class="space-y-4">
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-user text-indigo-600 text-xl mr-4 w-8"></i>
                    <div>
                        <p class="text-sm text-gray-500">Username</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($owner['username']) ?></p>
                    </div>
                </div>
                
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-calendar text-indigo-600 text-xl mr-4 w-8"></i>
                    <div>
                        <p class="text-sm text-gray-500">Member Since</p>
                        <p class="text-gray-800 font-semibold">
                            <?= date('F d, Y', strtotime($owner['tanggal_registrasi'])) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 pt-8 border-t flex gap-4">
                <a href="/owners/portal/index.php" class="flex-1 text-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-home mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="help-section rounded-xl p-6 text-center" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);">
        <h3 class="text-lg font-semibold text-white mb-2">Need to update your information?</h3>
        <p class="text-indigo-100 mb-4">Please contact our clinic to update your profile details</p>
        <a href="tel:+62123456789" class="inline-block px-6 py-3 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 transition font-semibold">
            <i class="fas fa-phone mr-2"></i>Call Clinic
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/owner_footer.php'; ?>
