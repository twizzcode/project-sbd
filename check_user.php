<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
require_once __DIR__ . '/config/database.php';

try {
    echo "Listing all users (fresh):\n";
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['user_id']}, Username: {$row['username']}, Name: {$row['nama_lengkap']}, Role: {$row['role']}\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
