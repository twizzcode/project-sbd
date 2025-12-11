<?php
/**
 * Validate appointment date and time
 * 
 * @param string $date Date in Y-m-d format
 * @param string $time Time in H:i format
 * @return bool
 */
function validate_appointment_datetime($date, $time) {
    // Convert to DateTime objects
    $appointment_date = new DateTime($date . ' ' . $time);
    $now = new DateTime();
    
    // Check if appointment is in the past
    if ($appointment_date < $now) {
        return false;
    }
    
    // Check if date is too far in the future (3 months)
    $max_date = clone $now;
    $max_date->modify('+3 months');
    if ($appointment_date > $max_date) {
        return false;
    }
    
    // Check if time is within business hours (8:00 - 20:00)
    $hour = (int)$appointment_date->format('H');
    if ($hour < 8 || $hour >= 20) {
        return false;
    }
    
    return true;
}

/**
 * Check if doctor is available at the given time
 */
function is_doctor_available($conn, $dokter_id, $date, $start_time, $exclude_appointment_id = null) {
    $query = "SELECT COUNT(*) as count FROM appointment
        WHERE dokter_id = '$dokter_id'
        AND tanggal_appointment = '$date'
        AND jam_appointment = '$start_time'
        AND status NOT IN ('Cancelled', 'No_Show')";
    
    if ($exclude_appointment_id) {
        $query .= " AND appointment_id != '$exclude_appointment_id'";
    }
    
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] == 0;
}

/**
 * Create a notification for a user
 */
function create_notification($conn, $user_id, $type, $reference_id) {
    mysqli_query($conn, "INSERT INTO notifications (user_id, type, reference_id, created_at, status)
        VALUES ('$user_id', '$type', '$reference_id', NOW(), 'Unread')");
    return mysqli_affected_rows($conn) > 0;
}

/**
 * Get appointment status badge HTML
 * 
 * @param string $status Appointment status
 * @return string HTML for status badge
 */
function get_appointment_status_badge($status) {
    $colors = [
        'Pending' => 'bg-yellow-100 text-yellow-800',
        'Confirmed' => 'bg-green-100 text-green-800',
        'Completed' => 'bg-blue-100 text-blue-800',
        'Cancelled' => 'bg-red-100 text-red-800',
        'No_Show' => 'bg-gray-100 text-gray-800'
    ];
    
    $labels = [
        'Pending' => 'Menunggu',
        'Confirmed' => 'Dikonfirmasi',
        'Completed' => 'Selesai',
        'Cancelled' => 'Dibatalkan',
        'No_Show' => 'Tidak Hadir'
    ];
    
    $color_class = $colors[$status] ?? 'bg-gray-100 text-gray-800';
    $label = $labels[$status] ?? $status;
    
    return sprintf(
        '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full %s">%s</span>',
        $color_class,
        $label
    );
}