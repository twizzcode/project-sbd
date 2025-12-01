<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'VetClinic'; ?></title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    
    <!-- Custom Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <?php $css_version = '2025.11.23'; ?>
    <link href="/assets/css/style.css?v=<?= $css_version ?>" rel="stylesheet">
    <link href="/assets/css/enhanced-ui.css?v=<?= $css_version ?>" rel="stylesheet">
    <link href="/assets/css/tailwind-enhancements.css?v=<?= $css_version ?>" rel="stylesheet">
    
    <!-- SPA Router CSS -->
    <style>
        /* SPA Loading state */
        .spa-loading {
            cursor: wait;
        }
        
        .spa-loading * {
            pointer-events: none;
        }
        
        /* Smooth content transitions */
        main {
            transition: opacity 200ms ease;
        }
        
        /* Loading spinner improvements */
        #loadingSpinner {
            backdrop-filter: blur(4px);
            z-index: 9999;
        }
        
        #loadingSpinner .spinner-container {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        /* Prefetch link hint */
        a[href]:hover {
            cursor: pointer;
        }
        
        /* Progress bar for page loading */
        .spa-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            z-index: 10000;
            transition: width 0.3s ease;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
        
        /* Skeleton loader for content area */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s ease-in-out infinite;
        }
        
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    
    <!-- Critical Styles - Logout Button -->
    <style>
        #sidebar .logout-button {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.4) !important;
            text-decoration: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        #sidebar .logout-button:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.5) !important;
            transform: translateY(-2px) scale(1.02) !important;
        }
        #sidebar .logout-button * {
            color: #ffffff !important;
        }
    </style>
    
    <?php if (isset($use_chart) && $use_chart === true): ?>
    <!-- Chart.js v4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    
    <style>
        @media (min-width: 1024px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 16rem;
            }
            .main-container {
                margin-left: 16rem;
            }
        }
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
            }
            #sidebar.show {
                transform: translateX(0);
            }
            .main-container {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="spinner-container">
            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500 mx-auto"></div>
            <p class="text-gray-700 mt-4 text-center font-medium">Memuat...</p>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div id="spaProgressBar" class="spa-progress-bar" style="width: 0%;"></div>

    <!-- Include Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-container">
        <div class="flex flex-col min-h-screen">
            <!-- Top Header -->
            <header class="bg-white shadow-sm sticky top-0 z-40">
                <div class="px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button id="mobileSidebarToggle" class="lg:hidden mr-4 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <h1 class="text-xl font-semibold text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button id="notificationButton" class="text-gray-600 hover:text-gray-900 relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                    3
                                </span>
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 max-h-96 overflow-y-auto">
                                <div class="px-4 py-2 border-b">
                                    <h3 class="font-semibold text-gray-800">Notifikasi</h3>
                                </div>
                                <div class="divide-y">
                                    <a href="/appointments/" class="block px-4 py-3 hover:bg-gray-50">
                                        <p class="text-sm font-medium text-gray-800">Janji Temu Hari Ini</p>
                                        <p class="text-xs text-gray-600">Ada 3 janji temu yang akan datang</p>
                                        <p class="text-xs text-gray-400 mt-1">2 jam yang lalu</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center space-x-2 hover:opacity-80 transition-opacity">
                                <img class="h-8 w-8 rounded-full ring-2 ring-gray-200" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_lengkap']); ?>&background=random" alt="">
                                <span class="hidden md:inline-block text-gray-700 font-medium"><?php echo $_SESSION['nama_lengkap']; ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-user mr-3"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="/auth/logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 bg-gray-100">
                <div class="container mx-auto px-4 py-6">