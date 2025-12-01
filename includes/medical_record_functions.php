<?php
require_once 'functions.php';

/**
 * Get medical record status badge HTML
 */
function get_medical_record_status_badge($status) {
    $badges = [
        'Draft' => 'bg-gray-100 text-gray-800',
        'Active' => 'bg-green-100 text-green-800',
        'Archived' => 'bg-yellow-100 text-yellow-800',
        'Deleted' => 'bg-red-100 text-red-800'
    ];

    $badgeClass = $badges[$status] ?? 'bg-gray-100 text-gray-800';
    return "<span class=\"px-2 py-1 text-sm font-medium rounded-full {$badgeClass}\">{$status}</span>";
}

/**
 * Validate medical record data
 */
function validate_medical_record($data, $isNew = true) {
    $errors = [];

    if (empty($data['pet_id'])) {
        $errors[] = "ID Hewan harus diisi";
    }

    if (empty($data['dokter_id'])) {
        $errors[] = "ID Dokter harus diisi";
    }

    if (empty($data['tanggal'])) {
        $errors[] = "Tanggal harus diisi";
    }

    if (empty($data['diagnosis'])) {
        $errors[] = "Diagnosis harus diisi";
    }

    if (empty($data['tindakan'])) {
        $errors[] = "Tindakan harus diisi";
    }

    return $errors;
}

/**
 * Create medical record history entry
 */
function create_medical_record_history($pdo, $record_id, $action, $old_status = null, $new_status = null, $notes = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO medical_record_history (
                record_id, action, old_status, new_status, 
                notes, performed_by, performed_at
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, NOW()
            )
        ");

        return $stmt->execute([
            $record_id,
            $action,
            $old_status,
            $new_status,
            $notes,
            $_SESSION['user_id']
        ]);
    } catch (PDOException $e) {
        error_log("Error creating medical record history: " . $e->getMessage());
        return false;
    }
}

/**
 * Get medical record by ID with related data
 */
function get_medical_record($pdo, $record_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                mr.*,
                mr.tanggal_kunjungan as tanggal,
                mr.diagnosis,
                mr.resep,
                mr.catatan_dokter as catatan,
                'Active' as status,
                mr.tanggal_kunjungan as created_at,
                mr.biaya,
                NULL as created_by,
                NULL as updated_by,
                NULL as updated_at,
                '' as created_by_name,
                '' as updated_by_name,
                p.nama_hewan,
                p.jenis as jenis_hewan,
                p.ras as ras_hewan,
                p.foto_url as pet_foto,
                o.nama_lengkap as owner_name,
                o.no_telepon as owner_phone,
                o.email as owner_email,
                v.nama_dokter as dokter_name,
                v.spesialisasi as dokter_spesialisasi,
                v.foto_url as dokter_foto,
                a.tanggal_appointment as appointment_date,
                a.jam_appointment as appointment_time
            FROM medical_record mr
            JOIN pet p ON mr.pet_id = p.pet_id
            JOIN users o ON p.owner_id = o.user_id
            JOIN veterinarian v ON mr.dokter_id = v.dokter_id
            LEFT JOIN appointment a ON mr.appointment_id = a.appointment_id
            WHERE mr.record_id = ?
        ");
        
        $stmt->execute([$record_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting medical record: " . $e->getMessage());
        return null;
    }
}

/**
 * Upload and process medical record attachments
 */
function handle_medical_record_attachments($files, $record_id) {
    $uploaded_files = [];
    $errors = [];
    
    // Create upload directory if it doesn't exist
    $upload_dir = "../assets/uploads/medical_records/{$record_id}";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($files['attachment']['tmp_name'] as $key => $tmp_name) {
        if ($files['attachment']['error'][$key] === 0) {
            $filename = $files['attachment']['name'][$key];
            $filesize = $files['attachment']['size'][$key];
            $filetype = $files['attachment']['type'][$key];

            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($filetype, $allowed_types)) {
                $errors[] = "File {$filename} tidak diizinkan. Hanya file JPG, PNG, GIF, dan PDF yang diperbolehkan.";
                continue;
            }

            // Validate file size (5MB max)
            if ($filesize > 5 * 1024 * 1024) {
                $errors[] = "File {$filename} terlalu besar. Maksimal ukuran file adalah 5MB.";
                continue;
            }

            // Generate unique filename
            $new_filename = uniqid() . '_' . sanitize_filename($filename);
            $filepath = $upload_dir . '/' . $new_filename;

            if (move_uploaded_file($tmp_name, $filepath)) {
                $uploaded_files[] = [
                    'original_name' => $filename,
                    'stored_name' => $new_filename,
                    'file_type' => $filetype,
                    'file_size' => $filesize
                ];
            } else {
                $errors[] = "Gagal mengupload file {$filename}";
            }
        }
    }

    return [
        'uploaded_files' => $uploaded_files,
        'errors' => $errors
    ];
}

/**
 * Save medical record attachments to database
 */
function save_medical_record_attachments($pdo, $record_id, $attachments) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO medical_record_attachment (
                record_id, original_name, stored_name, 
                file_type, file_size, uploaded_by, 
                uploaded_at
            ) VALUES (
                ?, ?, ?, 
                ?, ?, ?, 
                NOW()
            )
        ");

        foreach ($attachments as $file) {
            $stmt->execute([
                $record_id,
                $file['original_name'],
                $file['stored_name'],
                $file['file_type'],
                $file['file_size'],
                $_SESSION['user_id']
            ]);
        }

        return true;
    } catch (PDOException $e) {
        error_log("Error saving medical record attachments: " . $e->getMessage());
        return false;
    }
}

/**
 * Get medical record attachments
 */
function get_medical_record_attachments($pdo, $record_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ma.*,
                u.nama_lengkap as uploaded_by_name
            FROM medical_record_attachment ma
            LEFT JOIN users u ON ma.uploaded_by = u.user_id
            WHERE ma.record_id = ?
            ORDER BY ma.uploaded_at DESC
        ");
        
        $stmt->execute([$record_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting medical record attachments: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete medical record attachment
 */
function delete_medical_record_attachment($pdo, $attachment_id) {
    try {
        // Get attachment info first
        $stmt = $pdo->prepare("
            SELECT * FROM medical_record_attachment 
            WHERE attachment_id = ?
        ");
        $stmt->execute([$attachment_id]);
        $attachment = $stmt->fetch();

        if ($attachment) {
            // Delete file
            $filepath = "../assets/uploads/medical_records/{$attachment['record_id']}/{$attachment['stored_name']}";
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Delete from database
            $stmt = $pdo->prepare("
                DELETE FROM medical_record_attachment 
                WHERE attachment_id = ?
            ");
            return $stmt->execute([$attachment_id]);
        }

        return false;
    } catch (PDOException $e) {
        error_log("Error deleting medical record attachment: " . $e->getMessage());
        return false;
    }
}

/**
 * Sanitize filename
 */
function sanitize_filename($filename) {
    // Remove any character that is not a letter, number, dot, hyphen or underscore
    $filename = preg_replace("/[^a-zA-Z0-9.-_]/", "", $filename);
    // Remove any dots except the last one
    $filename = preg_replace("/\.(?=.*\.)/", "", $filename);
    return $filename;
}