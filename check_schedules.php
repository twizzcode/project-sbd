<?php
require_once __DIR__ . '/config/database.php';

try {
    echo "Checking for doctors without schedules...\n";
    
    $stmt = $pdo->query("
        SELECT v.dokter_id, v.nama_dokter 
        FROM veterinarian v 
        LEFT JOIN doctor_schedule ds ON v.dokter_id = ds.dokter_id 
        WHERE v.status = 'Aktif' AND ds.schedule_id IS NULL
    ");
    
    $missing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($missing) > 0) {
        echo "Found " . count($missing) . " active doctors without schedules:\n";
        foreach ($missing as $doc) {
            echo "- ID: {$doc['dokter_id']}, Name: {$doc['nama_dokter']}\n";
            
            // Optional: Auto-fix
            // echo "  Creating default schedule...\n";
            // ...
        }
        echo "\nPlease re-save these doctors or manually add schedules in the database.\n";
    } else {
        echo "All active doctors have at least one schedule entry.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
