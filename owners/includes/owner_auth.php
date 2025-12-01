<?php
/**
 * Owner Portal Authentication Check
 * Updated for unified users table with role-based access
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /auth/login.php');
    exit;
}

// Check if user is an owner
if ($_SESSION['role'] !== 'Owner') {
    header('Location: /auth/login.php?error=access_denied');
    exit;
}

// Verify user record exists
require_once __DIR__ . '/../../config/database.php';

$stmt = $pdo->prepare("SELECT user_id, nama_lengkap, email, username FROM users WHERE user_id = ? AND role = 'Owner' AND status = 'Aktif'");
$stmt->execute([$_SESSION['user_id']]);
$owner = $stmt->fetch();

if (!$owner) {
    session_destroy();
    header('Location: /auth/login.php?error=invalid_owner');
    exit;
}

// Store/update owner info in session
$_SESSION['user_id'] = $owner['user_id'];
$_SESSION['owner_id'] = $owner['user_id']; // Backward compatibility
$_SESSION['owner_name'] = $owner['nama_lengkap'];
$_SESSION['owner_email'] = $owner['email'];
$_SESSION['username'] = $owner['username'];

// Update last activity
$stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
$stmt->execute([$owner['user_id']]);
?>
