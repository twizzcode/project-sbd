<?php
require_once __DIR__ . '/../config/database.php';

try {
    echo "Resetting database using init.sql...\n";

    // Read init.sql
    $sql = file_get_contents(__DIR__ . '/init.sql');
    
    if (!$sql) {
        die("Error: Could not read init.sql\n");
    }

    // Remove comments to avoid issues with some drivers (optional but safer)
    // $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split into individual queries (this is a simple split, might need more robust parsing for complex stored procs)
    // However, PDO doesn't support multiple queries in one execute call by default usually.
    // But since we have DELIMITER // in the file, simple splitting by ; won't work for procedures.
    
    // Alternative: Use the mysql command line if available, but it's not.
    // So we must parse it carefully or try to execute it as a big block if the driver allows.
    // PDO MySQL driver *can* support multiple queries if MYSQL_ATTR_MULTI_STATEMENTS is true.
    // But we didn't enable it in config/database.php.
    
    // Let's try to enable it temporarily for this script.
    // We need to create a new connection with that option.
    
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4"; // Connect without DB name first to allow DROP DATABASE
    $pdo_setup = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true // Enable multiple statements
    ]);

    $pdo_setup->exec($sql);
    
    echo "Database reset successfully!\n";

} catch (PDOException $e) {
    echo "Error resetting database: " . $e->getMessage() . "\n";
}
?>
