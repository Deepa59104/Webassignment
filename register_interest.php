<?php
error_reporting(0);
ini_set('display_errors', 0);
require 'Includes/db.php';

// Get programme ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get programme details from database
$stmt = $pdo->prepare("SELECT * FROM programmes WHERE id = ?");
$stmt->execute([$id]);
$programme = $stmt->fetch(PDO::FETCH_ASSOC);

$success = false;
$withdrawn = false;
$error = '';

// Handle WITHDRAW interest - Person 4 feature
if (isset($_GET['withdraw'])) {
    
    // Get email from URL and clean it to prevent XSS
    $email = htmlspecialchars(trim($_GET['withdraw']), ENT_QUOTES, 'UTF-8');
    
    // Find student by email
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        // Delete interest from interests table
        $stmt = $pdo->prepare("DELETE FROM interests WHERE student_id = ? AND programme_id = ?");
        $stmt->execute([$student['id'], $id]);
        $withdrawn = true;
    } else {
        $error = 'Email not found. Please check and try again.';
    }
}

// Handle REGISTER interest - Person 1 feature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    
    // Clean inputs to prevent XSS
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));

    // Check if student already registered interest
    $check = $pdo->prepare("
        SELECT s.id FROM students s 
        JOIN interests i ON s.id = i.student_id 
        WHERE s.email = ? AND i.programme_id = ?
    ");
    $check->execute([$email, $id]);
    
    if ($check->fetch()) {
        // Already registered
        $error = 'You have already registered interest in this programme!';
    } else {
        // Insert student into students table
        $stmt2 = $pdo->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
        $stmt2->execute([$name, $email]);
        $student_id = $pdo->lastInsertId();

        // Insert interest into interests table
        $stmt3 = $pdo->prepare("INSERT INTO interests (student_id, programme_id) VALUES (?, ?)");
        $stmt3->execute([$student_id, $id]);

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Interest</title>
    <style>
        /* Basic reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }

        /* Accessibility focus styles - WCAG2 */
        a:focus, button:focus, input:focus { outline: 3px solid #ffbf00; }

        /* Header */
        header { background: #003366; color: white; padding: 20px; text-align: center; }

        /* Main content */
        main { max-width: 600px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; }
        h2 { color: #003366; margin-bottom: 20px; }
        h3 { color: #003366; margin-bottom: 15px; }

        /* Form styles */
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        button { background: #003366; color: white; padding: 10px 25px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0055aa; }

        /* Success message */
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }

        /* Error message */
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }

        /* Warning message for withdraw */
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; }

        /* Back link */
        .back { color: #003366; text-decoration: none; display: block; margin-bottom: 20px; }

        /* Divider between sections */
        .divider { border: none; border-top: 2px solid #eee; margin: 30px 0; }

        /* Withdraw section styling */
        .withdraw-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .withdraw-section h3 { color: #dc3545; margin-bottom: 10px; }
        .btn-withdraw { background: #dc3545; color: white; padding: 10px 25px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn-withdraw:hover { background: #c82333; }

        /* Mobile friendly */
        @media (max-width: 600px) { main { margin: 10px; padding: 15px; } }
    </style>
</head>
<body>

<!-- Header -->
<header role="banner">
    <h1>Student Course Hub</h1>
</header>

<main role="main">
    <!-- Back link -->
    <a href="programme.php?id=<?php echo $id; ?>" class="back">← Back</a>
    
    <h2>Register Interest</h2>

    <!-- Show programme title -->
    <?php if ($programme): ?>
        <h3><?php echo htmlspecialchars($programme['title']); ?></h3>
    <?php endif; ?>

    <!-- Show error message -->
    <?php if ($error): ?>
        <div class="error" role="alert">❌ <?= $error ?></div>
    <?php endif; ?>

    <!-- Show success message after registering -->
    <?php if ($success): ?>
        <div class="success" role="alert">
            ✅ Thank you! Your interest has been registered.
        </div>
        <a href="index.php" class="back">← Back to all programmes</a>

    <!-- Show withdrawn message after withdrawing -->
    <?php elseif ($withdrawn): ?>
        <div class="warning" role="alert">
            ⚠️ Your interest has been withdrawn successfully.
        </div>
        <a href="index.php" class="back">← Back to all programmes</a>

    <?php else: ?>

        <!-- Register Interest Form - Person 1 feature -->
        <form method="POST" aria-label="Register interest form">
            <!-- Hidden field to identify this as register action -->
            <input type="hidden" name="action" value="register">
            
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name"
                   placeholder="Your name" required aria-required="true">
            
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   placeholder="Your email" required aria-required="true">
            
            <button type="submit">Register Interest</button>
        </form>

        <!-- Divider between register and withdraw sections -->
        <hr class="divider">

        <!-- Withdraw Interest Section - Person 4 feature -->
        <div class="withdraw-section">
            <h3>🚫 Withdraw Interest</h3>
            <p style="margin-bottom:15px; color:#666;">
                Already registered? Enter your email below to withdraw your interest.
            </p>
            
            <!-- Withdraw form - submits email as GET parameter -->
            <form method="GET" aria-label="Withdraw interest form">
                <!-- Keep programme ID in URL -->
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="withdraw" value="">
                
                <label for="withdraw_email">Email Address</label>
                <input type="email" id="withdraw_email" name="withdraw"
                       placeholder="Your registered email" required aria-required="true">
                
                <button type="submit" class="btn-withdraw">Withdraw Interest</button>
            </form>
        </div>

    <?php endif; ?>
</main>

</body>
</html>
