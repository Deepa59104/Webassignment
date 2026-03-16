<?php
// Start the session to allow login state to be tracked
session_start();

// Include the database connection file
require '../Includes/db.php';

// Initialise error message as empty
$error = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize username input to prevent XSS attacks
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    
    // Get the password from the form
    $password = trim($_POST['password']);

    // Query the database for the username
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the password matches the hashed password in the database
    if ($admin && password_verify($password, $admin['password'])) {
        
        // Store admin details in session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        // Redirect to dashboard on successful login
        header('Location: dashboard.php');
        exit;
    } else {
        // Set error message if login fails
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #1a1a2e; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: white; padding: 2.5rem; border-radius: 10px; width: 360px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #1a1a2e; }
        label { display: block; margin-bottom: 0.3rem; font-size: 0.9rem; color: #333; }
        input { width: 100%; padding: 0.7rem; margin-bottom: 1.2rem; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; }
        input:focus { outline: none; border-color: #1a1a2e; }
        button { width: 100%; padding: 0.8rem; background: #1a1a2e; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #16213e; }
        .error { background: #ffe0e0; color: #c0392b; padding: 0.7rem; border-radius: 5px; margin-bottom: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
<!-- Login box container -->
<div class="login-box">
    <h2>🔐 Admin Login</h2>
    
    <!-- Show error message if login failed -->
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <!-- Login form - submits via POST -->
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>