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
    $pdo->beginTransaction();

    // Delete pet record (CASCADE will delete related appointments and medical records)
    $stmt = $pdo->prepare("DELETE FROM pet WHERE pet_id = ?");
    $stmt->execute([$pet_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Data hewan berhasil dihapus";

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: index.php");
exit;