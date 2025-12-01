<?php
// Database configuration - use environment variables with defaults
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'vetclinic_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'vetclinic_password');
define('DB_NAME', getenv('DB_NAME') ?: 'vetclinic');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// Retry logic for database connection (useful for Docker containers)
$max_retries = 10;
$retry_delay = 2; // seconds
$attempt = 1;
$pdo = null;

while ($attempt <= $max_retries) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]);
        
        // Set strict mode for better data integrity
        $pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
        
        break; // Connected successfully
        
    } catch (PDOException $e) {
        if ($attempt < $max_retries) {
            // Log warning if needed, but keep silent for retry
            sleep($retry_delay);
            $attempt++;
        } else {
            // Last attempt failed
            die("Database connection failed after $max_retries attempts: " . $e->getMessage());
        }
    }
}
?>
