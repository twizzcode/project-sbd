<?php
require_once __DIR__ . '/config/database.php';

try {
    echo "Checking admin user...\n";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'Admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "Admin user found: " . $admin['username'] . "\n";
    } else {
        echo "No admin user found. Creating default admin...\n";
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, nama_lengkap, email, role, status, created_at)
            VALUES ('admin', ?, 'Administrator', 'admin@vetclinic.com', 'Admin', 'Aktif', NOW())
        ");
        $stmt->execute([$password]);
        
        echo "Default admin created.\nUsername: admin\nPassword: admin123\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
