<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';

// Only Admin can delete doctors
if ($_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menghapus dokter";
    header("Location: index.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        // Check if doctor has appointments
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointment WHERE dokter_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Tidak dapat menghapus dokter yang memiliki riwayat janji temu. Silakan nonaktifkan status dokter saja.");
        }

        $stmt = $pdo->prepare("DELETE FROM veterinarian WHERE dokter_id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Data dokter berhasil dihapus";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: index.php");
exit;
