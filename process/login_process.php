<?php
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

start_session_if_not_started();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: ../index.php");
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // If user is a driver, get driver_id
            if ($user['user_type'] == 'driver') {
                $stmt = $pdo->prepare("SELECT driver_id FROM driver_details WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);
                $driver = $stmt->fetch();
                if ($driver) {
                    $_SESSION['driver_id'] = $driver['driver_id'];
                }
            }
            
            redirect_by_user_type();
        } else {
            // Login failed
            $_SESSION['error'] = "Invalid email or password";
            header("Location: ../index.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../index.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>