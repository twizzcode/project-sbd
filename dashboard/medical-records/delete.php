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

// Get record ID
$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$record_id) {
    $_SESSION['error'] = "ID rekam medis tidak valid";
    header("Location: index.php");
    exit;
}

// Get record data
$record = get_medical_record($conn, $record_id);

if (!$record) {
    $_SESSION['error'] = "Data rekam medis tidak ditemukan";
    header("Location: index.php");
    exit;
}

// Check if record can be deleted
if ($record['status'] !== 'Active') {
    $_SESSION['error'] = "Hanya rekam medis dengan status Active yang dapat dihapus";
    header("Location: detail.php?id=" . $record_id);
    exit;
}

// Handle deletion
try {
    mysqli_begin_transaction($conn);

    // Get all attachments
    $result = mysqli_query($conn, "
        SELECT * FROM medical_record_attachment 
        WHERE record_id = ?
    ");
    
    $attachments = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Delete all attachment files
    foreach ($attachments as $attachment) {
        $filepath = "../assets/uploads/medical_records/{$record_id}/{$attachment['stored_name']}";
        if (file_exists($filepath)) {
            if (!unlink($filepath)) {
                throw new Exception("Gagal menghapus file: " . $attachment['original_name']);
            }
        }
    }

    // Delete directory if exists
    $dir = "../assets/uploads/medical_records/{$record_id}";
    if (is_dir($dir)) {
        rmdir($dir);
    }

    // Delete all attachments from database
    $result = mysqli_query($conn, "
        DELETE FROM medical_record_attachment 
        WHERE record_id = ?
    ");
    

    // Create history record
    create_medical_record_history(
        $pdo, 
        $record_id, 
        'UPDATE', 
        $record['status'],
        'Deleted',
        'Rekam medis dihapus oleh ' . $_SESSION['nama_lengkap']
    );

    // Delete the medical record permanently
    $result = mysqli_query($conn, "
        DELETE FROM medical_record 
        WHERE rekam_id = ?
    ");
    

    mysqli_commit($conn);
    $_SESSION['success'] = "Rekam medis berhasil dihapus";
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: detail.php?id=" . $record_id);
    exit;
}