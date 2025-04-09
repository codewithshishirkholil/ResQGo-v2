<?php
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

start_session_if_not_started();
require_login();

// Check if user is a driver
if (!is_driver()) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ride_id = sanitize_input($_POST['ride_id']);
    $status = sanitize_input($_POST['status']);
    
    // Validate input
    if (empty($ride_id) || empty($status)) {
        $_SESSION['error'] = "Ride ID and status are required";
        header("Location: ../driver/active-rides.php");
        exit;
    }
    
    // Validate status
    $valid_statuses = ['accepted', 'en_route', 'picked_up', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status";
        header("Location: ../driver/active-rides.php");
        exit;
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Check if ride exists and belongs to this driver
        $stmt = $pdo->prepare("SELECT * FROM rides WHERE ride_id = ? AND driver_id = ?");
        $stmt->execute([$ride_id, $_SESSION['driver_id']]);
        $ride = $stmt->fetch();
        
        if (!$ride) {
            $pdo->rollBack();
            $_SESSION['error'] = "Ride not found or does not belong to you";
            header("Location: ../driver/active-rides.php");
            exit;
        }
        
        // Update ride status
        $stmt = $pdo->prepare("UPDATE rides SET status = ? WHERE ride_id = ?");
        $stmt->execute([$status, $ride_id]);
        
        // If ride is completed or cancelled, update driver status and set completed_at
        if ($status == 'completed' || $status == 'cancelled') {
            $stmt = $pdo->prepare("UPDATE driver_details SET status = 'available' WHERE driver_id = ?");
            $stmt->execute([$_SESSION['driver_id']]);
            
            $stmt = $pdo->prepare("UPDATE rides SET completed_at = NOW() WHERE ride_id = ?");
            $stmt->execute([$ride_id]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Ride status updated successfully!";
        
        // Redirect based on status
        if ($status == 'completed' || $status == 'cancelled') {
            header("Location: ../driver/ride-history.php");
        } else {
            header("Location: ../driver/active-rides.php");
        }
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../driver/active-rides.php");
        exit;
    }
} else {
    header("Location: ../driver/active-rides.php");
    exit;
}
?>