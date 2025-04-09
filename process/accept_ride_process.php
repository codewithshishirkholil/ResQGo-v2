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
    
    // Validate input
    if (empty($ride_id)) {
        $_SESSION['error'] = "Ride ID is required";
        header("Location: ../driver/ride-requests.php");
        exit;
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Check if ride exists and is still available
        $stmt = $pdo->prepare("SELECT * FROM rides WHERE ride_id = ? AND status = 'requested'");
        $stmt->execute([$ride_id]);
        $ride = $stmt->fetch();
        
        if (!$ride) {
            $pdo->rollBack();
            $_SESSION['error'] = "Ride is no longer available";
            header("Location: ../driver/ride-requests.php");
            exit;
        }
        
        // Update ride status and assign driver
        $stmt = $pdo->prepare("UPDATE rides SET driver_id = ?, status = 'accepted' WHERE ride_id = ?");
        $stmt->execute([$_SESSION['driver_id'], $ride_id]);
        
        // Update driver status
        $stmt = $pdo->prepare("UPDATE driver_details SET status = 'busy' WHERE driver_id = ?");
        $stmt->execute([$_SESSION['driver_id']]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Ride accepted successfully!";
        header("Location: ../driver/active-rides.php");
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../driver/ride-requests.php");
        exit;
    }
} else {
    header("Location: ../driver/ride-requests.php");
    exit;
}
?>