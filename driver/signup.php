<?php
require_once '../includes/functions.php';
start_session_if_not_started();

// If user is already logged in, redirect to appropriate page
if (is_logged_in()) {
    redirect_by_user_type();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Driver Sign Up - ResQGo</title>
<link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
    <header>
        <h1>ResQGo</h1>
        <p>Emergency at Your Fingertips</p>
    </header>
    
    <div>
        <h2>Driver Sign Up</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="../process/register_process.php" method="post">
            <input type="hidden" name="user_type" value="driver">
            
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="license_number">Driver's License Number:</label>
                <input type="text" id="license_number" name="license_number" required>
            </div>
            
            <div class="form-group">
                <label for="experience_years">Years of Experience:</label>
                <input type="number" id="experience_years" name="experience_years" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">Sign Up</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Already have an account? <a href="../index.php">Login</a></p>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>