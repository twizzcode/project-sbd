<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Owner Portal' ?> - VetClinic</title>
    <script>
        // Suppress Tailwind CDN warning - MUST RUN BEFORE TAILWIND LOADS
        (function() {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                const msg = args[0]?.toString() || '';
                if (msg.includes('cdn.tailwindcss.com') || msg.includes('should not be used in production')) {
                    return; // Block this warning
                }
                originalWarn.apply(console, args);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Owner Portal Styles (NO enhanced-ui.js to avoid toast popups) -->
    <link href="/assets/css/tailwind-enhancements.css" rel="stylesheet">
    
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hover-scale {
            transition: transform 0.2s ease;
        }
        .hover-scale:hover {
            transform: scale(1.02);
        }
        
        /* ===========================
           DARK MODE STYLES - OWNER PORTAL
           =========================== */
        html[data-theme="dark"],
        body[data-theme="dark"],
        [data-theme="dark"] {
            background-color: #1a202c !important;
            color: #e2e8f0;
        }
        
        html[data-theme="dark"] body,
        [data-theme="dark"] body {
            background-color: #1a202c !important;
            color: #e2e8f0;
        }
        
        /* Backgrounds */
        [data-theme="dark"] .bg-gray-50 {
            background-color: #1a202c !important;
        }
        
        [data-theme="dark"] .bg-gray-100 {
            background-color: #2d3748 !important;
        }
        
        [data-theme="dark"] .bg-white {
            background-color: #2d3748 !important;
        }
        
        /* Navigation - Blue gradient matching admin */
        html[data-theme="dark"] nav,
        body[data-theme="dark"] nav,
        [data-theme="dark"] nav {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
        }
        
        html[data-theme="dark"] nav .text-gray-800,
        html[data-theme="dark"] nav .text-gray-700,
        body[data-theme="dark"] nav .text-gray-800,
        body[data-theme="dark"] nav .text-gray-700,
        [data-theme="dark"] nav .text-gray-800,
        [data-theme="dark"] nav .text-gray-700 {
            color: #ffffff !important;
        }
        
        html[data-theme="dark"] nav .text-gray-500,
        body[data-theme="dark"] nav .text-gray-500,
        [data-theme="dark"] nav .text-gray-500 {
            color: #e5e7eb !important;
        }
        
        html[data-theme="dark"] nav a:hover,
        body[data-theme="dark"] nav a:hover,
        [data-theme="dark"] nav a:hover {
            color: #93c5fd !important;
        }
        
        /* Dark mode text label */
        html[data-theme="dark"] nav .text-gray-700,
        body[data-theme="dark"] nav .text-gray-700 {
            color: #ffffff !important;
        }
        
        /* Gradient headers - enhance blue for dark mode */
        [data-theme="dark"] .bg-gradient-to-r.from-indigo-600 {
            background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%) !important;
        }
        
        [data-theme="dark"] .from-indigo-500 {
            --tw-gradient-from: #1e40af !important;
        }
        
        /* Text colors */
        [data-theme="dark"] .text-gray-700,
        [data-theme="dark"] .text-gray-800,
        [data-theme="dark"] .text-gray-900 {
            color: #f9fafb !important;
        }
        
        [data-theme="dark"] .text-gray-600 {
            color: #e5e7eb !important;
        }
        
        [data-theme="dark"] .text-gray-500 {
            color: #d1d5db !important;
        }
        
        [data-theme="dark"] .text-indigo-100 {
            color: #e0e7ff !important;
        }
        
        /* Cards and containers */
        [data-theme="dark"] .shadow,
        [data-theme="dark"] .shadow-md,
        [data-theme="dark"] .shadow-lg,
        [data-theme="dark"] .shadow-xl {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
        }
        
        /* Borders */
        [data-theme="dark"] .border,
        [data-theme="dark"] .border-t,
        [data-theme="dark"] .border-b,
        [data-theme="dark"] .border-gray-200 {
            border-color: #4a5568 !important;
        }
        
        /* Colored backgrounds for stats */
        [data-theme="dark"] .bg-indigo-50 {
            background-color: rgba(79, 70, 229, 0.15) !important;
        }
        
        [data-theme="dark"] .bg-purple-50 {
            background-color: rgba(147, 51, 234, 0.15) !important;
        }
        
        [data-theme="dark"] .bg-blue-50 {
            background-color: rgba(59, 130, 246, 0.15) !important;
        }
        
        [data-theme="dark"] .bg-green-100 {
            background-color: rgba(34, 197, 94, 0.2) !important;
        }
        
        [data-theme="dark"] .bg-yellow-100 {
            background-color: rgba(234, 179, 8, 0.2) !important;
        }
        
        [data-theme="dark"] .bg-blue-100 {
            background-color: rgba(59, 130, 246, 0.2) !important;
        }
        
        /* Status badges keep their colors but with better contrast */
        [data-theme="dark"] .bg-indigo-100 {
            background-color: rgba(79, 70, 229, 0.25) !important;
        }
        
        [data-theme="dark"] .text-indigo-600 {
            color: #818cf8 !important;
        }
        
        [data-theme="dark"] .text-green-600 {
            color: #4ade80 !important;
        }
        
        [data-theme="dark"] .text-yellow-800 {
            color: #fde047 !important;
        }
        
        [data-theme="dark"] .text-blue-800 {
            color: #93c5fd !important;
        }
        
        [data-theme="dark"] .text-green-800 {
            color: #86efac !important;
        }
        
        [data-theme="dark"] .text-purple-600 {
            color: #c084fc !important;
        }
        
        /* Hover states */
        [data-theme="dark"] .hover\:bg-gray-50:hover {
            background-color: #374151 !important;
        }
        
        [data-theme="dark"] .hover\:bg-gray-100:hover {
            background-color: #374151 !important;
        }
        
        [data-theme="dark"] tr.hover\:bg-gray-50:hover {
            background-color: #374151 !important;
        }
        
        /* User dropdown menu */
        [data-theme="dark"] #ownerMenu {
            background-color: #2d3748 !important;
            border-color: #4a5568 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
        }
        
        [data-theme="dark"] #ownerMenu a {
            color: #f9fafb !important;
        }
        
        [data-theme="dark"] #ownerMenu a:hover {
            background-color: #374151 !important;
        }
        
        [data-theme="dark"] #ownerMenu a.text-red-600 {
            color: #fca5a5 !important;
        }
        
        [data-theme="dark"] #ownerMenu a[href*="logout"]:hover {
            background-color: rgba(239, 68, 68, 0.2) !important;
        }
        
        /* Icon containers */
        [data-theme="dark"] .bg-indigo-600 {
            background-color: #4f46e5 !important;
        }
        
        /* Dark Mode Toggle Switch - Matching Admin Design */
        .dark-mode-toggle {
            width: 60px;
            height: 30px;
            background: #d1d5db;
            border-radius: 9999px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .dark-mode-toggle.active {
            background: #4f46e5;
        }
        
        .dark-mode-toggle .toggle-slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .dark-mode-toggle.active .toggle-slider {
            transform: translateX(30px);
        }
        
        [data-theme="dark"] .dark-mode-toggle {
            background: #1e40af;
        }
        
        [data-theme="dark"] .dark-mode-toggle.active {
            background: #6366f1;
        }
        
        /* Help Section - Profile Page */
        html[data-theme="dark"] .help-section,
        body[data-theme="dark"] .help-section,
        [data-theme="dark"] .help-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
        }
        
        html[data-theme="dark"] .help-section h3,
        body[data-theme="dark"] .help-section h3,
        [data-theme="dark"] .help-section h3 {
            color: #ffffff !important;
        }
        
        html[data-theme="dark"] .help-section p,
        body[data-theme="dark"] .help-section p,
        [data-theme="dark"] .help-section p {
            color: #e0e7ff !important;
        }
        
        html[data-theme="dark"] .help-section a,
        body[data-theme="dark"] .help-section a,
        [data-theme="dark"] .help-section a {
            background-color: #ffffff !important;
            color: #1e40af !important;
        }
        
        html[data-theme="dark"] .help-section a:hover,
        body[data-theme="dark"] .help-section a:hover,
        [data-theme="dark"] .help-section a:hover {
            background-color: #e0e7ff !important;
        }
    </style>
</head>
<body class="bg-gray-50" data-theme="light">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-paw text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800">VetClinic</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="/owners/portal/index.php" class="text-gray-700 hover:text-indigo-600 transition flex items-center">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="/owners/portal/add_pet.php" class="text-gray-700 hover:text-indigo-600 transition flex items-center">
                        <i class="fas fa-paw mr-2"></i>Add Pet
                    </a>
                    <a href="/owners/portal/appointments.php" class="text-gray-700 hover:text-indigo-600 transition flex items-center">
                        <i class="fas fa-calendar mr-2"></i>Appointments
                    </a>
                    <a href="/owners/portal/book_appointment.php" class="text-gray-700 hover:text-indigo-600 transition flex items-center">
                        <i class="fas fa-calendar-plus mr-2"></i>Book Now
                    </a>
                </div>

                <!-- Dark Mode Toggle -->
                <div class="flex items-center space-x-3 mr-3">
                    <span class="text-sm text-gray-700 hidden md:inline">
                        <i class="fas fa-moon mr-1"></i>Dark Mode
                    </span>
                    <div class="dark-mode-toggle" onclick="toggleDarkMode()" id="darkModeToggle">
                        <div class="toggle-slider">
                            <i class="fas fa-sun text-yellow-500 text-xs"></i>
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-3">
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button id="ownerMenuButton" class="flex items-center space-x-2 hover:opacity-80 transition-opacity">
                            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">
                                    <?= strtoupper(substr($_SESSION['owner_name'] ?? 'O', 0, 1)) ?>
                                </span>
                            </div>
                            <span class="hidden md:inline-block text-gray-700 font-medium"><?= htmlspecialchars($_SESSION['owner_name'] ?? 'Owner') ?></span>
                            <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="ownerMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <a href="/owners/portal/profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
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
        </div>
    </nav>
