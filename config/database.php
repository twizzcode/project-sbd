<?php
// Database configuration - use environment variables for Railway
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'vetclinic_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'vetclinic_password');
define('DB_NAME', getenv('DB_NAME') ?: 'vetclinic');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// Retry logic for database connection
$max_retries = 10;
$retry_delay = 2; // seconds
$attempt = 1;
$conn = false;

while ($attempt <= $max_retries) {
    try {
        // Suppress warnings to handle them manually
        $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($conn) {
            break; // Connected successfully
        }
    } catch (Exception $e) {
        // Connection failed, wait and retry
    }
    
    // If not connected, wait
    if ($attempt < $max_retries) {
        sleep($retry_delay);
        $attempt++;
    } else {
        // Last attempt failed
        die("Connection failed after $max_retries attempts: " . mysqli_connect_error());
    }
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// For prepared statements, use PDO
$pdo = null;
$attempt = 1;

while ($attempt <= $max_retries) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        break; // Connected successfully
    } catch (PDOException $e) {
        if ($attempt < $max_retries) {
            sleep($retry_delay);
            $attempt++;
        } else {
            die("PDO Connection failed after $max_retries attempts: " . $e->getMessage());
        }
    }
}
?>
