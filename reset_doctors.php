<?php
require_once __DIR__ . '/config/database.php';

try {
    echo "Resetting doctor data...\n";
    
    // Disable foreign key checks to allow truncation
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Truncate tables
    $pdo->exec("TRUNCATE TABLE doctor_schedule");
    $pdo->exec("TRUNCATE TABLE veterinarian");
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "All doctor data and schedules have been deleted.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
