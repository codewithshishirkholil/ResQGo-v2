<?php
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

start_session_if_not_started();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize_input($_POST['user_type']);
    
    // Validate input
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../" . $user_type . "/signup.php");
        exit;
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: ../" . $user_type . "/signup.php");
        exit;
    }
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already exists";
            header("Location: ../" . $user_type . "/signup.php");
            exit;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$email, $hashed_password, $full_name, $phone, $user_type]);
        
        $user_id = $pdo->lastInsertId();
        
        // If user is a driver, insert driver details
        if ($user_type == 'driver') {
            $license_number = sanitize_input($_POST['license_number']);
            $experience_years = sanitize_input($_POST['experience_years']);
            
            if (empty($license_number) || empty($experience_years)) {
                $pdo->rollBack();
                $_SESSION['error'] = "Driver details are required";
                header("Location: ../driver/signup.php");
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO driver_details (user_id, license_number, experience_years) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $license_number, $experience_years]);
            
            $driver_id = $pdo->lastInsertId();
            
            // Set driver_id in session
            $_SESSION['driver_id'] = $driver_id;
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['full_name'] = $full_name;
        
        // Redirect based on user type
        redirect_by_user_type();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../" . $user_type . "/signup.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>