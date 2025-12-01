<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetClinic - Smart Pet Health Management</title>
    
    <!-- Suppress Tailwind Warning -->
    <script>
        (function() {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                const msg = args[0]?.toString() || '';
                if (msg.includes('cdn.tailwindcss.com') || msg.includes('should not be used in production')) {
                    return;
                }
                originalWarn.apply(console, args);
            };
        })();
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome 6.5.1 (Latest) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #f8fafc 0%, #e0e7ff 50%, #dbeafe 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Geometric Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 15% 25%, rgba(59, 130, 246, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 85% 75%, rgba(30, 58, 138, 0.06) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(96, 165, 250, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
            animation: pulse-bg 8s ease-in-out infinite;
        }

        @keyframes pulse-bg {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Animated Shapes Container */
        .animated-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        /* Floating Circles */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(30, 58, 138, 0.05) 100%);
            animation: float-circle 25s infinite ease-in-out;
        }

        .circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 20s;
        }

        .circle:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 20%;
            right: 5%;
            animation-delay: 3s;
            animation-duration: 25s;
        }

        .circle:nth-child(3) {
            width: 250px;
            height: 250px;
            bottom: -100px;
            left: 60%;
            animation-delay: 5s;
            animation-duration: 30s;
        }

        .circle:nth-child(4) {
            width: 180px;
            height: 180px;
            top: 60%;
            left: 5%;
            animation-delay: 2s;
            animation-duration: 22s;
        }

        @keyframes float-circle {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.6;
            }
            25% {
                transform: translate(30px, -50px) scale(1.1);
                opacity: 0.8;
            }
            50% {
                transform: translate(-30px, -100px) scale(0.9);
                opacity: 0.5;
            }
            75% {
                transform: translate(50px, -50px) scale(1.05);
                opacity: 0.7;
            }
        }

        /* Floating Squares */
        .square {
            position: absolute;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-radius: 12px;
            animation: rotate-square 30s infinite linear;
        }

        .square:nth-child(5) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 25%;
            animation-delay: 1s;
        }

        .square:nth-child(6) {
            width: 80px;
            height: 80px;
            top: 50%;
            right: 20%;
            animation-delay: 4s;
            animation-duration: 25s;
        }

        .square:nth-child(7) {
            width: 120px;
            height: 120px;
            bottom: 20%;
            left: 15%;
            animation-delay: 6s;
            animation-duration: 35s;
        }

        @keyframes rotate-square {
            0% {
                transform: rotate(0deg) translate(0, 0);
                opacity: 0.4;
            }
            25% {
                transform: rotate(90deg) translate(20px, -20px);
                opacity: 0.6;
            }
            50% {
                transform: rotate(180deg) translate(0, -40px);
                opacity: 0.3;
            }
            75% {
                transform: rotate(270deg) translate(-20px, -20px);
                opacity: 0.5;
            }
            100% {
                transform: rotate(360deg) translate(0, 0);
                opacity: 0.4;
            }
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Floating Bubbles */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(147, 197, 253, 0.4), rgba(59, 130, 246, 0.1));
            border: 2px solid rgba(59, 130, 246, 0.2);
            animation: rise-bubble 15s infinite ease-in;
            box-shadow: 0 8px 16px rgba(30, 58, 138, 0.1);
        }

        .bubble:nth-child(8) {
            width: 60px;
            height: 60px;
            left: 15%;
            bottom: -60px;
            animation-delay: 0s;
            animation-duration: 12s;
        }

        .bubble:nth-child(9) {
            width: 40px;
            height: 40px;
            left: 35%;
            bottom: -40px;
            animation-delay: 2s;
            animation-duration: 14s;
        }

        .bubble:nth-child(10) {
            width: 80px;
            height: 80px;
            left: 55%;
            bottom: -80px;
            animation-delay: 4s;
            animation-duration: 16s;
        }

        .bubble:nth-child(11) {
            width: 50px;
            height: 50px;
            left: 75%;
            bottom: -50px;
            animation-delay: 1s;
            animation-duration: 13s;
        }

        .bubble:nth-child(12) {
            width: 70px;
            height: 70px;
            left: 90%;
            bottom: -70px;
            animation-delay: 5s;
            animation-duration: 15s;
        }

        .bubble:nth-child(13) {
            width: 45px;
            height: 45px;
            left: 8%;
            bottom: -45px;
            animation-delay: 3s;
            animation-duration: 11s;
        }

        @keyframes rise-bubble {
            0% {
                transform: translateY(0) translateX(0) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
                transform: scale(1);
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100vh) translateX(50px) scale(1.2);
                opacity: 0;
            }
        }

        /* Floating Icons */
        .floating-icon {
            position: absolute;
            font-size: 2.5rem;
            animation: drift-icon 20s infinite ease-in-out;
        }

        .floating-icon:nth-child(14) {
            left: 20%;
            top: 10%;
            color: rgba(59, 130, 246, 0.15);
            animation-delay: 0s;
        }

        .floating-icon:nth-child(15) {
            left: 70%;
            top: 30%;
            color: rgba(30, 64, 175, 0.12);
            animation-delay: 3s;
        }

        .floating-icon:nth-child(16) {
            left: 40%;
            top: 60%;
            color: rgba(96, 165, 250, 0.18);
            animation-delay: 6s;
        }

        @keyframes drift-icon {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 0.3;
            }
            25% {
                transform: translate(20px, -30px) rotate(10deg);
                opacity: 0.5;
            }
            50% {
                transform: translate(-15px, -60px) rotate(-5deg);
                opacity: 0.2;
            }
            75% {
                transform: translate(25px, -30px) rotate(15deg);
                opacity: 0.4;
            }
        }

        /* Pet silhouettes floating */
        .pet-silhouette {
            position: absolute;
            opacity: 0.06;
            animation: drift 30s infinite ease-in-out;
        }

        .pet-silhouette:nth-child(odd) { animation-delay: 5s; }
        .pet-silhouette:nth-child(even) { animation-delay: 10s; }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(30px, -40px) rotate(5deg); }
            50% { transform: translate(-20px, -80px) rotate(-3deg); }
            75% { transform: translate(40px, -120px) rotate(7deg); }
        }

        /* Content wrapper */
        .content-wrapper {
            position: relative;
            z-index: 2;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .slide-in-left {
            animation: slideInLeft 0.8s ease-out forwards;
        }

        .slide-in-right {
            animation: slideInRight 0.8s ease-out forwards;
        }

        .scale-in {
            animation: scaleIn 0.5s ease-out forwards;
        }

        /* Stagger animation delays */
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }

        /* Glass morphism - Indigo Purple Theme */
        .glass {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.2);
        }

        .glass-white {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(168, 85, 247, 0.1);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.15);
        }

        /* Brand color accents - matching portal gradients */
        .accent-indigo {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }

        .accent-purple {
            background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        }

        .accent-orange {
            background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
        }

        .accent-blue-purple {
            background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
        }

        /* Card hover effects */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Button shine effect */
        .btn-shine {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s ease;
        }

        .btn-shine:hover::before {
            left: 100%;
        }

        .btn-shine:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(99, 102, 241, 0.4);
        }

        /* Pulse effect for interactive elements */
        @keyframes gentle-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .pulse-gentle {
            animation: gentle-pulse 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <!-- Animated Shapes Background -->
    <div class="animated-shapes">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="square"></div>
        <div class="square"></div>
        <div class="square"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <i class="fas fa-paw floating-icon"></i>
        <i class="fas fa-heart floating-icon"></i>
        <i class="fas fa-stethoscope floating-icon"></i>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Navigation -->
        <nav class="glass sticky top-0 z-50 shadow-lg fade-in-up" style="background: rgba(30, 58, 138, 0.95); backdrop-filter: blur(10px);">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg float-animation">
                            <i class="fas fa-paw text-2xl text-blue-600"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">VetClinic</h1>
                            <p class="text-xs text-blue-200">Smart Pet Health Platform</p>
                        </div>
                    </div>
                    <div class="hidden md:flex items-center space-x-4">
                        <span class="px-4 py-2 rounded-full text-white text-sm font-medium" style="background: rgba(255, 255, 255, 0.15);">
                            <i class="fas fa-phone-alt mr-2 text-blue-300"></i>+62 123-456-7890
                        </span>
                        <a href="/auth/register.php" class="px-6 py-2 rounded-full text-white text-sm font-bold hover:bg-white hover:text-blue-900 transition-all" style="background: rgba(255, 255, 255, 0.2);">
                            <i class="fas fa-user-plus mr-2"></i>Daftar
                        </a>
                        <a href="/auth/login.php" class="px-6 py-2 rounded-full text-blue-900 bg-white text-sm font-bold hover:shadow-lg transition-all">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="container mx-auto px-6 py-16">
            <div class="text-center mb-16">
                <div class="inline-block px-6 py-2 rounded-full mb-6 fade-in-up" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: white;">
                    <i class="fas fa-award mr-2 text-yellow-300"></i>Platform Kesehatan Hewan #1 di Indonesia
                </div>
                <h2 class="text-5xl md:text-7xl font-bold mb-6 fade-in-up delay-100" style="color: #1e3a8a;">
                    Kelola Kesehatan<br>
                    <span style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Semua Hewan</span> Anda
                </h2>
                <p class="text-xl max-w-2xl mx-auto mb-8 fade-in-up delay-200" style="color: #1e40af;">
                    Sistem manajemen klinik hewan modern dengan fitur Multi-Pet Dashboard yang memudahkan Anda merawat seluruh keluarga berbulu
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8 fade-in-up delay-300">
                    <a href="/auth/login.php" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl font-bold text-lg shadow-xl transition-all btn-shine text-white" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);">
                        <i class="fas fa-calendar-check mr-3 text-xl"></i>
                        <div class="text-left">
                            <div class="text-sm font-normal opacity-90">Jadwalkan Sekarang</div>
                            <div>Booking Appointment</div>
                        </div>
                    </a>
                    <a href="/auth/login.php" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl font-bold text-lg shadow-xl transition-all btn-shine text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        <i class="fas fa-stethoscope mr-3 text-xl"></i>
                        <div class="text-left">
                            <div class="text-sm font-normal opacity-90">Cek Kesehatan</div>
                            <div>Periksa Sekarang</div>
                        </div>
                    </a>
                </div>
            
                <!-- Cute Pet Icons -->
                <div class="flex justify-center gap-6 mb-12 fade-in-up delay-300">
                    <div class="text-center scale-in delay-300">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-xl hover:scale-110 transition-all cursor-pointer pulse-gentle" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                            <i class="fas fa-dog text-blue-700 text-3xl"></i>
                        </div>
                        <p class="text-sm mt-2 font-semibold" style="color: #1e3a8a;">Anjing</p>
                    </div>
                    <div class="text-center scale-in delay-400">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-xl hover:scale-110 transition-all cursor-pointer pulse-gentle" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);">
                            <i class="fas fa-cat text-indigo-700 text-3xl"></i>
                        </div>
                        <p class="text-sm mt-2 font-semibold" style="color: #1e3a8a;">Kucing</p>
                    </div>
                    <div class="text-center scale-in delay-500">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-xl hover:scale-110 transition-all cursor-pointer pulse-gentle" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                            <i class="fas fa-dove text-blue-600 text-3xl"></i>
                        </div>
                        <p class="text-sm mt-2 font-semibold" style="color: #1e3a8a;">Burung</p>
                    </div>
                    <div class="text-center scale-in delay-600">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-xl hover:scale-110 transition-all cursor-pointer pulse-gentle" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);">
                            <i class="fas fa-otter text-indigo-600 text-3xl"></i>
                        </div>
                        <p class="text-sm mt-2 font-semibold" style="color: #1e3a8a;">Lainnya</p>
                    </div>
                </div>
            </div>

        <!-- Unique Features Section -->
        <div class="glass-white rounded-3xl p-12 max-w-6xl mx-auto mb-24 shadow-2xl fade-in-up delay-400">
            <div class="text-center mb-12">
                <div class="inline-block p-4 bg-gradient-to-br from-purple-100 to-pink-100 rounded-3xl mb-4">
                    <i class="fas fa-star text-purple-600 text-5xl"></i>
                </div>
                <h3 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Fitur Unggulan Kami
                </h3>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Sistem <strong>Multi-Pet Family Dashboard</strong> yang membedakan kami dari klinik hewan lainnya
                </p>
            </div>

            <!-- Feature Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Feature 1 - Indigo -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-6 card-hover border-2 border-indigo-200">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-users text-indigo-600 text-3xl"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg text-center mb-2">Akun Keluarga Multi-Hewan</h4>
                    <p class="text-gray-600 text-sm text-center">Kelola semua hewan peliharaan Anda dalam satu akun terpusat</p>
                </div>

                <!-- Feature 2 - Purple -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 card-hover border-2 border-purple-200">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-chart-line text-purple-600 text-3xl"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg text-center mb-2">Timeline Kesehatan Visual</h4>
                    <p class="text-gray-600 text-sm text-center">Lihat riwayat medis lengkap dalam tampilan timeline yang mudah</p>
                </div>

                <!-- Feature 3 - Orange -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 card-hover border-2 border-orange-200">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-bell text-orange-600 text-3xl"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg text-center mb-2">Pengingat Otomatis Cerdas</h4>
                    <p class="text-gray-600 text-sm text-center">Notifikasi untuk vaksinasi dan jadwal pengobatan</p>
                </div>

                <!-- Feature 4 - Pink -->
                <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-2xl p-6 card-hover border-2 border-pink-200">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-heartbeat text-pink-600 text-3xl"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg text-center mb-2">Monitor Kesehatan Real-time</h4>
                    <p class="text-gray-600 text-sm text-center">Dashboard intuitif menampilkan status kesehatan semua hewan</p>
                </div>
            </div>

            <!-- Why This Matters -->
            <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-2xl p-8 border-2 border-purple-200">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <i class="fas fa-lightbulb text-purple-600 text-3xl"></i>
                    <h4 class="font-bold text-gray-800 text-2xl">Mengapa Ini Penting?</h4>
                </div>
                <p class="text-gray-700 text-center text-lg leading-relaxed">
                    Tidak perlu lagi login berkali-kali untuk setiap hewan. Lihat semua rekam medis, jadwal vaksinasi, dan pengingat pemeriksaan dalam <strong>satu dashboard terpusat</strong>. Kesehatan hewan Anda, terorganisir sempurna!
                </p>
            </div>

            <!-- CTA Button -->
            <div class="text-center mt-10">
                <a href="/auth/register.php" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white px-12 py-5 rounded-2xl font-bold text-xl shadow-2xl hover:shadow-3xl transition-all btn-shine">
                    <i class="fas fa-rocket mr-2"></i>Coba Sekarang Gratis
                </a>
                <p class="text-gray-500 text-sm mt-3">Tidak perlu kartu kredit • Setup dalam 2 menit</p>
            </div>
        </div>

        <!-- Demo Credentials -->
        <div class="glass-white rounded-3xl p-10 max-w-4xl mx-auto shadow-2xl fade-in-up delay-500 mb-20">
            <div class="text-center mb-8">
                <div class="inline-block p-4 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-3xl mb-4">
                    <i class="fas fa-rocket text-indigo-600 text-5xl"></i>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 mb-2">Mulai Kelola Kesehatan Pet Anda</h3>
                <p class="text-gray-600 mb-6">Daftar sekarang dan dapatkan akses ke Multi-Pet Dashboard</p>
                
                <a href="/auth/register.php" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white px-12 py-5 rounded-2xl font-bold text-xl shadow-2xl hover:shadow-3xl transition-all btn-shine">
                    <i class="fas fa-user-plus mr-2"></i>Daftar / Login Sekarang
                </a>
                <p class="text-gray-500 text-sm mt-4">Gratis • Tanpa biaya tersembunyi • Setup dalam 2 menit</p>
            </div>
        </div>
    </section>

    </div>
    <!-- End Content Wrapper -->

    <!-- Footer -->
    <footer class="relative mt-20 z-10">
        <div class="glass-white rounded-t-3xl py-12 px-4">
            <div class="container mx-auto max-w-6xl">
                <!-- Footer Content -->
                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    <!-- Brand Section -->
                    <div class="text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start space-x-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-paw text-white text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800">VetClinic</h3>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Sistem manajemen klinik hewan modern dengan Multi-Pet Dashboard untuk keluarga Indonesia
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div class="text-center">
                        <h4 class="font-bold text-gray-800 text-lg mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="/dashboard" class="text-gray-600 hover:text-indigo-600 transition"><i class="fas fa-user-shield mr-2"></i>Admin Portal</a></li>
                            <li><a href="/owners/portal/login.php" class="text-gray-600 hover:text-purple-600 transition"><i class="fas fa-heart mr-2"></i>Owner Portal</a></li>
                            <li><a href="tel:+62123456789" class="text-gray-600 hover:text-orange-600 transition"><i class="fas fa-phone mr-2"></i>+62 123 456 789</a></li>
                        </ul>
                    </div>

                    <!-- Features -->
                    <div class="text-center md:text-right">
                        <h4 class="font-bold text-gray-800 text-lg mb-4">Features</h4>
                        <ul class="space-y-2">
                            <li class="text-gray-600 text-sm"><i class="fas fa-check text-green-500 mr-2"></i>Multi-Pet Management</li>
                            <li class="text-gray-600 text-sm"><i class="fas fa-check text-green-500 mr-2"></i>Health Timeline</li>
                            <li class="text-gray-600 text-sm"><i class="fas fa-check text-green-500 mr-2"></i>Smart Reminders</li>
                            <li class="text-gray-600 text-sm"><i class="fas fa-check text-green-500 mr-2"></i>Real-time Dashboard</li>
                        </ul>
                    </div>
                </div>

                <!-- Divider -->
                <div class="h-px bg-gradient-to-r from-transparent via-purple-300 to-transparent mb-8"></div>

                <!-- Bottom Footer -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <!-- Copyright -->
                    <p class="text-gray-600 text-sm">
                        &copy; <?= date('Y') ?> <strong>VetClinic</strong>. All rights reserved.
                    </p>

                    <!-- Social Links -->
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-indigo-100 hover:bg-indigo-600 rounded-xl flex items-center justify-center transition-all group">
                            <i class="fab fa-facebook text-indigo-600 group-hover:text-white text-lg transition"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-pink-100 hover:bg-pink-600 rounded-xl flex items-center justify-center transition-all group">
                            <i class="fab fa-instagram text-pink-600 group-hover:text-white text-lg transition"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-100 hover:bg-blue-400 rounded-xl flex items-center justify-center transition-all group">
                            <i class="fab fa-twitter text-blue-400 group-hover:text-white text-lg transition"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-green-100 hover:bg-green-600 rounded-xl flex items-center justify-center transition-all group">
                            <i class="fab fa-whatsapp text-green-600 group-hover:text-white text-lg transition"></i>
                        </a>
                    </div>

                    <!-- Made with Love -->
                    <p class="text-gray-500 text-sm flex items-center">
                        Made with <i class="fas fa-heart text-red-500 mx-2 animate-pulse"></i> for pets
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
