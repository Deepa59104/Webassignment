<?php
// Start session to remember who is logged in
session_start();

// Connect to the database
require '../Includes/db.php';

// Empty error message to start
$error = '';

// Check if the login form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get email from form and clean it to prevent XSS attacks
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    
    // Get password from form
    $staff_password = trim($_POST['password']);

    // Look for staff member with this email in database
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if staff found and password is correct
    if ($staff && password_verify($staff_password, $staff['password'])) {
        
        // Save staff id and name in session
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['staff_name'] = $staff['name'];
        
        // Go to dashboard page
        header('Location: dashboard.php');
        exit;
    } else {
        // Show error if email or password is wrong
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <style>
        /* Page background */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #1a3c5e; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        /* Login box in the middle */
        .login-box { background: white; padding: 2.5rem; border-radius: 10px; width: 360px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #1a3c5e; }
        
        /* Form inputs */
        label { display: block; margin-bottom: 0.3rem; font-size: 0.9rem; color: #333; }
        input { width: 100%; padding: 0.7rem; margin-bottom: 1.2rem; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; }
        input:focus { outline: none; border-color: #1a3c5e; }
        
        /* Login button */
        button { width: 100%; padding: 0.8rem; background: #1a3c5e; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #15324f; }
        
        /* Error message in red */
        .error { background: #ffe0e0; color: #c0392b; padding: 0.7rem; border-radius: 5px; margin-bottom: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>

<!-- Login box -->
<div class="login-box">
    <h2>👤 Staff Login</h2>
    
    <!-- Show error if login failed -->
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <!-- Login form -->
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>