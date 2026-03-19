<?php
// Start session
session_start();

// If staff not logged in send them to login page
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// Connect to database
require '../Includes/db.php';

// Get the logged in staff member's ID from session
$staff_id = $_SESSION['staff_id'];

// Get all modules this staff member is leading
$stmt = $pdo->prepare("
    SELECT m.id, m.name, m.description, m.year,
           GROUP_CONCAT(p.title SEPARATOR ', ') as programmes
    FROM modules m
    LEFT JOIN programme_modules pm ON m.id = pm.module_id
    LEFT JOIN programmes p ON pm.programme_id = p.id
    WHERE m.staff_id = ?
    GROUP BY m.id
");
$stmt->execute([$staff_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all programmes that use this staff member's modules
$stmt2 = $pdo->prepare("
    SELECT DISTINCT p.id, p.title, p.level, p.description
    FROM programmes p
    JOIN programme_modules pm ON p.id = pm.programme_id
    JOIN modules m ON pm.module_id = m.id
    WHERE m.staff_id = ?
");
$stmt2->execute([$staff_id]);
$programmes = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <style>
        /* Basic reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }

        /* Top navigation bar */
        nav { background: #1a3c5e; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { color: white; font-size: 1.2rem; }
        nav a { color: #ccc; text-decoration: none; margin-left: 1.5rem; }
        nav a:hover { color: white; }

        /* Page content area */
        .container { padding: 2rem; }
        h2 { color: #1a3c5e; margin-bottom: 0.5rem; }
        h3 { color: #1a3c5e; margin-bottom: 1rem; margin-top: 2rem; }

        /* Welcome box at top */
        .welcome { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .welcome p { color: #666; margin-top: 0.3rem; }

        /* Table for modules */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 2rem; }
        th { background: #1a3c5e; color: white; padding: 0.8rem 1rem; text-align: left; }
        td { padding: 0.8rem 1rem; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }

        /* Small label badge */
        .badge { background: #e8f4fd; color: #1a3c5e; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem; }

        /* Programme cards grid */
        .prog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
        .prog-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .prog-card h4 { color: #1a3c5e; margin-bottom: 0.5rem; }
        .prog-card p { color: #666; font-size: 0.9rem; margin-top: 0.3rem; }

        /* Message when no data found */
        .no-data { background: white; padding: 1.5rem; border-radius: 10px; color: #666; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

<!-- Top navigation -->
<nav>
    <h1>Student Course Hub — Staff Portal</h1>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

    <!-- Welcome message showing staff name from session -->
    <div class="welcome">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['staff_name']) ?> 👋</h2>
        <p>Here is an overview of your modules and programmes.</p>
    </div>

    <!-- Section 1 - Modules this staff leads -->
    <h3>📚 Modules You Are Leading</h3>
    <?php if (count($modules) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Module Name</th>
                <th>Description</th>
                <th>Year</th>
                <th>Used In Programmes</th>
            </tr>
        </thead>
        <tbody>
        <!-- Loop through each module and show a row -->
        <?php foreach ($modules as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['description']) ?></td>
                <td>Year <?= $m['year'] ?></td>
                <td>
                    <?php if ($m['programmes']): ?>
                        <span class="badge"><?= htmlspecialchars($m['programmes']) ?></span>
                    <?php else: ?>
                        <span style="color:#999;">Not assigned</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="no-data">You are not currently leading any modules.</div>
    <?php endif; ?>

    <!-- Section 2 - Programmes this staff is involved in -->
    <h3>🎓 Programmes You Are Involved In</h3>
    <?php if (count($programmes) > 0): ?>
    <div class="prog-grid">
        <?php foreach ($programmes as $p): ?>
        <div class="prog-card">
            <h4><?= htmlspecialchars($p['title']) ?></h4>
            <p><span class="badge"><?= $p['level'] ?></span></p>
            <p><?= htmlspecialchars($p['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="no-data">You are not involved in any programmes yet.</div>
    <?php endif; ?>

</div>
</body>
</html>