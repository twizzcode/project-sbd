<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-white shadow-lg transition-transform duration-300 ease-in-out fixed inset-y-0 left-0 z-50 lg:translate-x-0 flex flex-col">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b flex-shrink-0">
        <a href="/dashboard/" class="text-xl font-bold text-gray-800">
            <i class="fas fa-paw text-blue-500 mr-2"></i>
            VetClinic
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4"  style="flex: 1 1 0%; min-height: 0;">
            <ul class="space-y-2 px-3">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Owner'): ?>
                <!-- Dashboard -->
                <li>
                    <a href="/dashboard/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/dashboard/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-home w-5 h-5 mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Owners -->
                <li>
                    <a href="/owners/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/owners/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-users w-5 h-5 mr-3"></i>
                        <span>Pemilik Hewan</span>
                    </a>
                </li>

                <!-- Pets -->
                <li>
                    <a href="/pets/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/pets/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-paw w-5 h-5 mr-3"></i>
                        <span>Hewan</span>
                    </a>
                </li>

                <!-- Appointments -->
                <li>
                    <a href="/appointments/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/appointments/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-calendar-alt w-5 h-5 mr-3"></i>
                        <span>Janji Temu</span>
                    </a>
                </li>

                <!-- Medical Records -->
                <li>
                    <a href="/medical-records/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/medical-records/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-notes-medical w-5 h-5 mr-3"></i>
                        <span>Rekam Medis</span>
                    </a>
                </li>

                <!-- Medicines -->
                <li>
                    <a href="/inventory/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/inventory/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-pills w-5 h-5 mr-3"></i>
                        <span>Obat-obatan</span>
                    </a>
                </li>

                <!-- Services -->
                <li>
                    <a href="/kategori/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/kategori/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                        <span>Layanan</span>
                    </a>
                </li>

                <!-- Veterinarians -->
                <li>
                    <a href="/supplier/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/supplier/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-user-md w-5 h-5 mr-3"></i>
                        <span>Dokter Hewan</span>
                    </a>
                </li>

                <!-- Vaccinations -->
                <li>
                    <a href="/vaccinations/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/vaccinations/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-syringe w-5 h-5 mr-3"></i>
                        <span>Vaksinasi</span>
                    </a>
                </li>

                <!-- Reports -->
                <?php if (isset($_SESSION['role']) && (($_SESSION['role'] === 'Admin') || ($_SESSION['role'] === 'Staff'))): ?>
                <li>
                    <a href="/reports/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/reports/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                        <span>Laporan</span>
                    </a>
                </li>

                <!-- Users -->
                <li>
                    <a href="/users/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/users/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-user-cog w-5 h-5 mr-3"></i>
                        <span>Users</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php else: ?>
                <!-- Owner Portal Dashboard -->
                <li>
                    <a href="/owners/portal/" class="flex items-center px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg <?php echo strpos($_SERVER['REQUEST_URI'], '/owners/portal/') !== false ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-home w-5 h-5 mr-3"></i>
                        <span>Portal Saya</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- LOGOUT MENU ITEM - SUPER VISIBLE -->
                <li class="mt-4 pt-4 border-t-2 border-gray-300">
                    <a href="/auth/logout.php" class="logout-button flex items-center justify-center px-4 py-3 rounded-lg" style="background: #ef4444 !important; color: white !important; font-weight: bold !important; box-shadow: 0 4px 6px rgba(0,0,0,0.2) !important; text-decoration: none !important;">
                        <i class="fas fa-sign-out-alt" style="margin-right: 8px; font-size: 18px; color: white !important;"></i>
                        <span style="font-size: 16px; color: white !important; font-weight: 700 !important;">LOGOUT</span>
                    </a>
                </li>
        </ul>
    </nav>

    <!-- Footer - Sticky at Bottom -->
    <div class="p-4 border-t bg-white flex-shrink-0">
        <!-- User Profile -->
        <div class="flex items-center mb-3">
            <img class="h-8 w-8 rounded-full mr-3" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_lengkap']); ?>&background=random" alt="Profile">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-700 truncate"><?php echo $_SESSION['nama_lengkap']; ?></p>
                <p class="text-xs text-gray-500"><?php echo ucfirst($_SESSION['role']); ?></p>
            </div>
        </div>
        
        <!-- Dark Mode Toggle -->
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">
                <i class="fas fa-moon mr-2"></i>Dark Mode
            </span>
            <div class="dark-mode-toggle" onclick="toggleDarkMode()" id="darkModeToggle">
                <div class="toggle-slider">
                    <i class="fas fa-sun text-yellow-500 text-xs"></i>
                </div>
            </div>
        </div>
    </div>
</aside>