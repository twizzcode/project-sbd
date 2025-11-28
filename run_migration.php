<?php
require_once __DIR__ . '/config/database.php';

try {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    echo "Running migration at " . date('Y-m-d H:i:s') . "...\n";
    
    $sql = file_get_contents(__DIR__ . '/database/migrations/add_doctor_schedule.sql');
    
    if (!$sql) {
        die("Error: Could not read migration file.\n");
    }
    
    // Split by semicolon to execute multiple statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            try {
                echo "Executing: " . substr($stmt, 0, 100) . "...\n";
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // Ignore "Table already exists" or "Duplicate entry" errors if we want to be idempotent
                // But for now let's report them
                echo "Statement failed: " . substr($stmt, 0, 50) . "... -> " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Migration executed successfully.\n";
    
    // Verify table existence
    $stmt = $pdo->query("SHOW TABLES LIKE 'doctor_schedule'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'doctor_schedule' exists.\n";
    } else {
        echo "Error: Table 'doctor_schedule' still does not exist.\n";
    }
    
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
