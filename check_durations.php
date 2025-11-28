<?php
require_once __DIR__ . '/config/database.php';

try {
    echo "Checking for invalid schedule durations...\n";
    
    $stmt = $pdo->query("
        SELECT * FROM doctor_schedule 
        WHERE durasi_slot IS NULL OR durasi_slot <= 0
    ");
    
    $bad = $stmt->fetchAll();
    
    if (count($bad) > 0) {
        echo "Found " . count($bad) . " schedules with invalid duration:\n";
        print_r($bad);
        
        // Fix them
        $pdo->exec("UPDATE doctor_schedule SET durasi_slot = 30 WHERE durasi_slot IS NULL OR durasi_slot <= 0");
        echo "Fixed invalid durations.\n";
    } else {
        echo "All schedules have valid durations.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
