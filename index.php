<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Owner') {
        header("Location: /owners/portal/");
    } elseif ($_SESSION['role'] === 'Admin') {
        header("Location: /dashboard/");
    } else {
        header("Location: /landing.php");
    }
} else {
    // Show landing page
    header("Location: /landing.php");
}
exit;