<?php
session_start();

$page_title = '404 - Halaman Tidak Ditemukan';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - VetClinic</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/enhanced-ui.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            padding: 2rem;
            animation: fadeIn 0.6s ease-in-out;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
            animation: slideDown 0.6s ease-out;
        }
        
        .paw-icon {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        
        .card-404 {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 3rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f3f4f6;
            color: #374151;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-left: 1rem;
        }
        
        .btn-back:hover {
            background: #e5e7eb;
        }
        
        .suggestions {
            text-align: left;
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 0.75rem;
        }
        
        .suggestions ul {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0 0;
        }
        
        .suggestions li {
            padding: 0.5rem 0;
            color: #6b7280;
        }
        
        .suggestions li a {
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .suggestions li a:hover {
            color: #2563eb;
            text-decoration: underline;
        }
        
        .suggestions li i {
            color: #10b981;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="card-404">
            <div class="paw-icon">
                <i class="fas fa-paw"></i>
            </div>
            
            <h1 class="error-code">404</h1>
            
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                Oops! Halaman Tidak Ditemukan
            </h2>
            
            <p class="text-gray-600 text-lg mb-6">
                Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman telah dipindahkan atau URL-nya salah.
            </p>
            
            <div class="mb-6">
                <?php if ($is_logged_in): ?>
                    <a href="/dashboard/" class="btn-home">
                        <i class="fas fa-home"></i>
                        Kembali ke Dashboard
                    </a>
                <?php else: ?>
                    <a href="/auth/login.php" class="btn-home">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                <?php endif; ?>
                
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
            
            <?php if ($is_logged_in): ?>
            <div class="suggestions">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Halaman yang Mungkin Anda Cari:
                </h3>
                <ul>
                    <li>
                        <i class="fas fa-paw"></i>
                        <a href="/pets/">Manajemen Hewan</a>
                    </li>
                    <li>
                        <i class="fas fa-users"></i>
                        <a href="/owners/">Manajemen Pemilik</a>
                    </li>
                    <li>
                        <i class="fas fa-calendar-alt"></i>
                        <a href="/appointments/">Janji Temu</a>
                    </li>
                    <li>
                        <i class="fas fa-notes-medical"></i>
                        <a href="/medical-records/">Rekam Medis</a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="mt-6 text-sm text-gray-500">
                <p>Error Code: 404 | VetClinic Management System</p>
                <p class="mt-1">Jika masalah berlanjut, hubungi administrator sistem</p>
            </div>
        </div>
    </div>
    
    <script>
        // Track 404 errors (optional - for analytics)
        console.log('404 Error: ' + window.location.pathname);
    </script>
</body>
</html>
