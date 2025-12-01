<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';

// Get owner ID
$owner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if owner exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'Owner'");
    $stmt->execute([$owner_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Data pemilik tidak ditemukan');
    }

    // Delete owner (will cascade delete pets and related records)
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'Owner'");
    $stmt->execute([$owner_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = 'Data pemilik berhasil dihapus';

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = 'Gagal menghapus data: ' . $e->getMessage();
}

// Redirect back to index
header('Location: index.php');
exit;