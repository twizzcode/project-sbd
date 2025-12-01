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
        
    </style>
</head>
<body class="bg-gray-50">
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
                    <a href="/owners/portal/chatbot.php" class="text-gray-700 hover:text-indigo-600 transition flex items-center">
                        <i class="fas fa-robot mr-2"></i>AI Assistant
                    </a>
                </div>
                
                <!-- User Info & Logout -->
                <div class="flex items-center space-x-4">
                    <!-- User Info -->
                    <div class="hidden md:flex items-center space-x-2">
                        <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                <?= strtoupper(substr($_SESSION['owner_name'] ?? 'O', 0, 1)) ?>
                            </span>
                        </div>
                        <span class="text-gray-700 font-medium"><?= htmlspecialchars($_SESSION['owner_name'] ?? 'Owner') ?></span>
                    </div>
                    
                    <!-- Profile Button -->
                    <a href="/owners/portal/profile.php" class="text-gray-700 hover:text-indigo-600 transition" title="My Profile">
                        <i class="fas fa-user text-lg"></i>
                    </a>
                    
                    <!-- Logout Button -->
                    <a href="/auth/logout.php" class="px-4 py-2 rounded-lg transition flex items-center" style="background-color: #ef4444 !important; color: white !important;" onmouseover="this.style.backgroundColor='#dc2626'" onmouseout="this.style.backgroundColor='#ef4444'" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span class="hidden md:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
