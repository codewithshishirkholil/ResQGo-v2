<?php
// Start session if not already started
function start_session_if_not_started() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    start_session_if_not_started();
    return isset($_SESSION['user_id']);
}

// Function to check if user is a driver
function is_driver() {
    start_session_if_not_started();
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'driver';
}

// Function to check if user is a customer
function is_customer() {
    start_session_if_not_started();
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'customer';
}

// Function to redirect to login if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: ../index.php");
        exit;
    }
}

// Function to redirect based on user type
function redirect_by_user_type() {
    start_session_if_not_started();
    if (is_driver()) {
        header("Location: driver/ride-requests.php");
        exit;
    } elseif (is_customer()) {
        header("Location: customer/request-ride.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}
?>