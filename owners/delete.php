<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';

// Get owner ID
$owner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Begin transaction
    mysqli_begin_transaction($conn);

    // Check if owner exists
    $result = mysqli_query($conn, "SELECT user_id FROM users WHERE user_id = ? AND role = 'Owner'");
    
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception('Data pemilik tidak ditemukan');
    }

    // Delete owner (will cascade delete pets and related records)
    $result = mysqli_query($conn, "DELETE FROM users WHERE user_id = ? AND role = 'Owner'");
    

    // Commit transaction
    mysqli_commit($conn);

    $_SESSION['success'] = 'Data pemilik berhasil dihapus';

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Gagal menghapus data: ' . $e->getMessage();
}

// Redirect back to index
header('Location: index.php');
exit;