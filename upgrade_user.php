<?php
require_once __DIR__ . '/config/database.php';

try {
    echo "Upgrading user 'pau' (Name: dsad) to Admin...\n";
    
    $stmt = $pdo->prepare("UPDATE users SET role = 'Admin' WHERE username = 'pau'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "User 'pau' is now an Admin.\n";
    } else {
        echo "User 'pau' was already an Admin or not found.\n";
    }
    
    // Verify
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'pau'");
    $stmt->execute();
    $user = $stmt->fetch();
    echo "Current Role: " . $user['role'] . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
