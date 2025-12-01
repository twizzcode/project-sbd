<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (!headers_sent()) {
        header('Location: /auth/login.php');
        exit;
    } else {
        echo '<script>window.location.href = "/auth/login.php";</script>';
        exit();
    }
}

// Get current path
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Define role-based access control
$owner_allowed_paths = [
    '/owners/portal/',
    '/auth/logout.php',
    '/api/dashboard_stats.php'
];

$admin_allowed_paths = [
    '/dashboard/',
    '/auth/logout.php',
    '/api/',
    '/assets/'
];

// Check if Owner is trying to access admin areas
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Owner') {
    $is_allowed = false;
    
    // Check if current path is in allowed paths
    foreach ($owner_allowed_paths as $allowed) {
        if (strpos($current_path, $allowed) !== false || $current_path === $allowed) {
            $is_allowed = true;
            break;
        }
    }
    
    // Redirect to owner portal if trying to access restricted area
    if (!$is_allowed) {
        if (!headers_sent()) {
            header('Location: /owners/portal/');
            exit();
        } else {
            echo '<script>window.location.href = "/owners/portal/";</script>';
            exit();
        }
    }
}

// Check if Admin is trying to access owner portal
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    // Allow admin to access everything
    // No restrictions for admin
}

// Check role-based access (Admin only now since Staff removed)
function check_role($required_role) {
    if ($_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'Admin') {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied');
    }
}

// Check if user has any of the required roles
function check_roles($required_roles) {
    if (!in_array($_SESSION['role'], $required_roles) && $_SESSION['role'] !== 'Admin') {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied');
    }
}

// CSRF Token generation
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token validation
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>