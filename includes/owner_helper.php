<?php
/**
 * Owner Portal Helper Functions
 */

/**
 * Generate secure password for new owner
 */
function generateOwnerPassword($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
    $password = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }
    return $password;
}

/**
 * Create user account for existing owner
 */
function createOwnerUserAccount($conn, $owner_id) {
    // Get owner details
    $result = mysqli_query($conn, "SELECT * FROM owner WHERE owner_id = '$owner_id'");
    
    $owner = mysqli_fetch_assoc($result);
    
    if (!$owner) {
        return ['success' => false, 'message' => 'Owner not found'];
    }
    
    // Check if user already exists
    if ($owner['user_id']) {
        return ['success' => false, 'message' => 'User account already exists'];
    }
    
    // Generate username from email
    $username = strtolower(explode('@', $owner['email'])[0]);
    $original_username = $username;
    $counter = 1;
    
    // Ensure unique username
    while (true) {
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) == 0) break;
        $username = $original_username . $counter++;
    }
    
    // Generate temporary password
    $temp_password = generateOwnerPassword(10);
    
    // Create user account
    $result = mysqli_query($conn, "
        INSERT INTO users (username, password, nama_lengkap, email, role, status)
        VALUES ('$username', '$temp_password', '{$owner['nama_lengkap']}', '{$owner['email']}', 'Owner', 'Aktif')
    ");
    
    $user_id = mysqli_insert_id($conn);
    
    // Link user to owner
    $result = mysqli_query($conn, "UPDATE owner SET user_id = '$user_id' WHERE owner_id = '$owner_id'");
    
    return [
        'success' => true,
        'username' => $username,
        'password' => $temp_password,
        'user_id' => $user_id
    ];
}

/**
 * Get all pets for an owner with health summary
 */
function getOwnerPetsWithHealth($conn, $owner_id) {
    $result = mysqli_query($conn, "SELECT p.*,
        TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur_tahun,
        TIMESTAMPDIFF(MONTH, p.tanggal_lahir, CURDATE()) % 12 as umur_bulan,
        (SELECT COUNT(*) FROM appointment WHERE pet_id = p.pet_id) as total_appointments,
        (SELECT tanggal_appointment FROM appointment 
         WHERE pet_id = p.pet_id AND status IN ('Pending', 'Confirmed') 
         AND tanggal_appointment >= CURDATE()
         ORDER BY tanggal_appointment ASC LIMIT 1) as next_appointment,
        (SELECT mr.tanggal_kunjungan FROM medical_record mr
         WHERE mr.pet_id = p.pet_id
         ORDER BY mr.tanggal_kunjungan DESC LIMIT 1) as last_checkup
        FROM pet p
        WHERE p.owner_id = '$owner_id'");
    
    $pets = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pets[] = $row;
    }
    return $pets;
}

/**
 * Get pet health status badge
 */
function getPetHealthStatus($pet) {
    $today = new DateTime();
    
    // Check if had recent checkup
    if ($pet['last_checkup']) {
        $last_check = new DateTime($pet['last_checkup']);
        $months_since = $today->diff($last_check)->m + ($today->diff($last_check)->y * 12);
        
        if ($months_since <= 3) {
            return ['status' => 'healthy', 'label' => 'Up to Date', 'class' => 'bg-green-100 text-green-800'];
        }
    }
    
    return ['status' => 'checkup_due', 'label' => 'Checkup Recommended', 'class' => 'bg-blue-100 text-blue-800'];
}



/**
 * Format Indonesian date
 */
function formatIndonesianDate($date) {
    if (!$date) return '-';
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $d = new DateTime($date);
    return $d->format('d') . ' ' . $months[(int)$d->format('m')] . ' ' . $d->format('Y');
}
?>
