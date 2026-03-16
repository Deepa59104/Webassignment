<?php
// Start session and check admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
require '../Includes/db.php';

// Get total counts for dashboard stats
$programmes = $pdo->query("SELECT COUNT(*) FROM programmes")->fetchColumn();
$modules = $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
$students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$interests = $pdo->query("SELECT COUNT(*) FROM interests")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #1a1a2e; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { color: white; font-size: 1.2rem; }
        nav a { color: #ccc; text-decoration: none; margin-left: 1.5rem; font-size: 0.95rem; }
        nav a:hover { color: white; }
        .container { padding: 2rem; }
        h2 { margin-bottom: 1.5rem; color: #1a1a2e; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; }
        .card { background: white; padding: 1.5rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card h3 { font-size: 2.5rem; color: #1a1a2e; }
        .card p { color: #666; margin-top: 0.5rem; }
        .actions { margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn { padding: 0.8rem 1.5rem; background: #1a1a2e; color: white; border-radius: 6px; text-decoration: none; font-size: 0.95rem; }
        .btn:hover { background: #16213e; }
    </style>
</head>
<body>
<!-- Navigation bar -->
<nav>
    <h1>Student Course Hub — Admin</h1>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="programmes.php">Programmes</a>
        <a href="modules.php">Modules</a>
        <a href="mailing_list.php">Mailing List</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<div class="container">
    <!-- Welcome message -->
    <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?> 👋</h2>
    
    <!-- Stats cards -->
    <div class="stats">
        <div class="card"><h3><?= $programmes ?></h3><p>Programmes</p></div>
        <div class="card"><h3><?= $modules ?></h3><p>Modules</p></div>
        <div class="card"><h3><?= $students ?></h3><p>Students</p></div>
        <div class="card"><h3><?= $interests ?></h3><p>Interests Registered</p></div>
    </div>
    
    <!-- Quick action buttons -->
    <div class="actions">
        <a href="programmes.php" class="btn">Manage Programmes</a>
        <a href="modules.php" class="btn">Manage Modules</a>
        <a href="mailing_list.php" class="btn">View Mailing List</a>
    </div>
</div>
</body>
</html>
