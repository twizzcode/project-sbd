<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Get pet ID from URL
$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pet_id) {
    $_SESSION['error'] = "ID Hewan tidak valid";
    header("Location: index.php");
    exit;
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Delete pet record (CASCADE will delete related appointments and medical records)
    $result = mysqli_query($conn, "DELETE FROM pet WHERE pet_id = '$pet_id'");
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);

    $_SESSION['success'] = "Data hewan berhasil dihapus";

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: index.php");
exit;