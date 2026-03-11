<?php
error_reporting(0);
ini_set('display_errors', 0);
require 'Includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM programmes WHERE id = ?");
$stmt->execute([$id]);
$programme = $stmt->fetch(PDO::FETCH_ASSOC);

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));

    $stmt2 = $pdo->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
    $stmt2->execute([$name, $email]);
    $student_id = $pdo->lastInsertId();

    $stmt3 = $pdo->prepare("INSERT INTO interests (student_id, programme_id) VALUES (?, ?)");
    $stmt3->execute([$student_id, $id]);

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Interest</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        a:focus, button:focus, input:focus { outline: 3px solid #ffbf00; }
        header { background: #003366; color: white; padding: 20px; text-align: center; }
        main { max-width: 600px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; }
        h2 { color: #003366; margin-bottom: 20px; }
        h3 { color: #003366; margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        button { background: #003366; color: white; padding: 10px 25px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0055aa; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .back { color: #003366; text-decoration: none; display: block; margin-bottom: 20px; }
        @media (max-width: 600px) { main { margin: 10px; padding: 15px; } }
    </style>
</head>
<body>
    <header role="banner">
        <h1>Student Course Hub</h1>
    </header>
    <main role="main">
        <a href="programme.php?id=<?php echo $id; ?>" class="back">← Back</a>
        <h2>Register Interest</h2>

        <?php if ($programme): ?>
            <h3><?php echo htmlspecialchars($programme['title']); ?></h3>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success" role="alert">✅ Thank you! Your interest has been registered.</div>
            <a href="index.php" class="back">← Back to all programmes</a>
        <?php else: ?>
            <form method="POST" aria-label="Register interest form">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" 
                       placeholder="Your name" required aria-required="true">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" 
                       placeholder="Your email" required aria-required="true">
                <button type="submit">Register</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>