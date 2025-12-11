<?php
session_start();
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/medical_record_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'");

// Check role authorization
if (!in_array($_SESSION['role'], ['Admin', 'Dokter'])) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman tersebut";
    header("Location: index.php");
    exit;
}

// Get IDs from URL
$attachment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$record_id = isset($_GET['record_id']) ? (int)$_GET['record_id'] : 0;

if (!$attachment_id || !$record_id) {
    $_SESSION['error'] = "Parameter tidak valid";
    header("Location: index.php");
    exit;
}

// Get attachment info
try {
    $result = mysqli_query($conn, "
        SELECT ma.*, mr.status as record_status 
        FROM medical_record_attachment ma
        JOIN medical_record mr ON ma.record_id = mr.record_id
        WHERE ma.attachment_id = ? AND ma.record_id = ?
    ");
    
    $attachment = mysqli_fetch_assoc($result);

    if (!$attachment) {
        $_SESSION['error'] = "Data lampiran tidak ditemukan";
        header("Location: edit.php?id=" . $record_id);
        exit;
    }

    // Check if medical record is active
    if ($attachment['record_status'] !== 'Active') {
        $_SESSION['error'] = "Tidak dapat menghapus lampiran dari rekam medis yang sudah diarsipkan";
        header("Location: edit.php?id=" . $record_id);
        exit;
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    // Delete file from storage
    $filepath = "../assets/uploads/medical_records/{$record_id}/{$attachment['stored_name']}";
    if (file_exists($filepath)) {
        if (!unlink($filepath)) {
            throw new Exception("Gagal menghapus file fisik");
        }
    }

    // Delete from database
    $result = mysqli_query($conn, "
        DELETE FROM medical_record_attachment 
        WHERE attachment_id = ? AND record_id = ?
    ");
    if (!$stmt->execute([$attachment_id, $record_id])) {
        throw new Exception("Gagal menghapus data lampiran");
    }

    // Create history record
    $result = mysqli_query($conn, "
        INSERT INTO medical_record_history (
            record_id, action, notes, 
            performed_by, performed_at
        ) VALUES (
            ?, 'UPDATE', ?,
            ?, NOW()
        )
    ");
    if (!$stmt->execute([
        $record_id,
        "Menghapus lampiran: " . $attachment['original_name'],
        $_SESSION['user_id']
    ])) {
        throw new Exception("Gagal mencatat history");
    }

    mysqli_commit($conn);
    $_SESSION['success'] = "Lampiran berhasil dihapus";

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
}

header("Location: edit.php?id=" . $record_id);
exit;