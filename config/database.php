<?php
// Database configuration
define('DB_HOST', 'db');
define('DB_USER', 'vetclinic_user');
define('DB_PASS', 'vetclinic_password');
define('DB_NAME', 'vetclinic');

// Connect to database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
