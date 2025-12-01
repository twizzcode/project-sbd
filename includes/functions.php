<?php
// Format currency (IDR)
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Format date (Indonesian)
function format_tanggal($date) {
    if (empty($date) || $date === null) {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', $date);
    if (count($split) !== 3) {
        return '-';
    }
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Alias for format_tanggal
function format_date($date) {
    return format_tanggal($date);
}

// Sanitize input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Pagination
function paginate($total_records, $records_per_page, $current_page) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_pages' => $total_pages,
        'offset' => $offset,
        'current_page' => $current_page
    ];
}

// Get status badge HTML
function get_status_badge($status) {
    $colors = [
        'Pending' => 'yellow',
        'Confirmed' => 'blue',
        'Completed' => 'green',
        'Cancelled' => 'red',
        'No_Show' => 'gray',
        'Aktif' => 'green',
        'Meninggal' => 'gray'
    ];
    
    $color = $colors[$status] ?? 'gray';
    return "<span class='px-2 py-1 text-xs rounded-full bg-$color-100 text-$color-800'>$status</span>";
}
?>