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

// Get all modules for this programme with staff name
// Using staff_id directly from modules table
$stmt2 = $pdo->prepare("
    SELECT m.*, s.name as staff_name
    FROM modules m
    JOIN programme_modules pm ON pm.module_id = m.id
    LEFT JOIN staff s ON s.id = m.staff_id
    WHERE pm.programme_id = ?
    ORDER BY m.year
");
$stmt2->execute([$id]);
$modules = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Person 4 feature - Get shared modules
// Find modules that appear in MORE than one programme
$stmt3 = $pdo->prepare("
    SELECT m.id, m.name, m.description,
           GROUP_CONCAT(p.title SEPARATOR ', ') as shared_with,
           COUNT(pm.programme_id) as programme_count
    FROM modules m
    JOIN programme_modules pm ON m.id = pm.module_id
    JOIN programmes p ON pm.programme_id = p.id
    WHERE m.id IN (
        SELECT module_id FROM programme_modules WHERE programme_id = ?
    )
    GROUP BY m.id
    HAVING programme_count > 1
    ORDER BY m.name
");
$stmt3->execute([$id]);
$shared_modules = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($programme['title'] ?? 'Programme'); ?> - Student Course Hub</title>
    <style>
        /* Basic reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }

        /* Accessibility focus styles - WCAG2 */
        a:focus, button:focus, input:focus { outline: 3px solid #ffbf00; }

        /* Header */
        header { background: #003366; color: white; padding: 20px; text-align: center; }

        /* Main content */
        main { max-width: 900px; margin: 30px auto; padding: 0 20px; }

        /* Back link */
        .back { color: #003366; text-decoration: none; display: block; margin-bottom: 15px; }

        /* Card style for sections */
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }

        /* Headings */
        h2 { color: #003366; margin-bottom: 10px; }
        h3 { color: #003366; margin: 15px 0 8px; }

        /* Level badge */
        .level { background: #e0f0ff; color: #003366; padding: 3px 10px; border-radius: 20px; font-size: 13px; }

        /* Register button */
        .btn { display: inline-block; margin-top: 15px; background: #003366; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
        .btn:hover { background: #0055aa; }

        /* Module card styling */
        .module { background: #f9f9f9; border-left: 4px solid #003366; padding: 10px; margin-bottom: 8px; border-radius: 4px; }
        .module small { color: #666; font-size: 12px; }

        /* Shared module badge - Person 4 feature */
        .shared-badge { background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 8px; }

        /* Shared modules section - Person 4 feature */
        .shared-module { background: #fff8e1; border-left: 4px solid #ffc107; padding: 10px; margin-bottom: 8px; border-radius: 4px; }
        .shared-module strong { color: #003366; }
        .shared-module small { color: #666; font-size: 12px; display: block; margin-top: 4px; }
        .shared-programmes { color: #856404; font-size: 12px; margin-top: 4px; }

        /* Mobile friendly */
        @media (max-width: 600px) { main { padding: 0 10px; } }
    </style>
</head>
<body>

<!-- Header -->
<header role="banner">
    <h1>Student Course Hub</h1>
</header>

<main role="main">
    <!-- Back to all programmes -->
    <a href="index.php" class="back">← Back to Programmes</a>

    <?php if ($programme): ?>

    <!-- Programme details card -->
    <div class="card">
        <h2><?php echo htmlspecialchars($programme['title']); ?></h2>
        <span class="level"><?php echo htmlspecialchars($programme['level']); ?></span>
        <p style="margin-top:10px;"><?php echo htmlspecialchars($programme['description']); ?></p>
        <!-- Register interest button -->
        <a href="register_interest.php?id=<?php echo $programme['id']; ?>" class="btn">
            Register Interest
        </a>
    </div>

    <!-- Modules list card -->
    <?php if (!empty($modules)): ?>
    <div class="card">
        <h2>Modules</h2>
        <?php $year = 0; foreach ($modules as $m): ?>
            <?php if ($m['year'] != $year): $year = $m['year']; ?>
                <h3>Year <?php echo $year; ?></h3>
            <?php endif; ?>
            <div class="module">
                <strong><?php echo htmlspecialchars($m['name']); ?></strong>
                <!-- Show shared badge if module is in multiple programmes -->
                <?php foreach ($shared_modules as $sm): ?>
                    <?php if ($sm['id'] == $m['id']): ?>
                        <span class="shared-badge">🔗 Shared Module</span>
                    <?php endif; ?>
                <?php endforeach; ?>
                <br>
                <!-- Show staff name if available -->
                <small>👨‍🏫 <?php echo htmlspecialchars($m['staff_name'] ?? 'Not assigned'); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Person 4 feature - Shared Modules Section -->
    <?php if (!empty($shared_modules)): ?>
    <div class="card">
        <h2>🔗 Modules Shared With Other Programmes</h2>
        <p style="color:#666; margin-bottom:15px; font-size:0.9rem;">
            These modules are also taught in other programmes —
            you may find similar content if you study these courses!
        </p>
        <?php foreach ($shared_modules as $sm): ?>
        <div class="shared-module">
            <strong><?php echo htmlspecialchars($sm['name']); ?></strong>
            <?php if ($sm['description']): ?>
                <small><?php echo htmlspecialchars($sm['description']); ?></small>
            <?php endif; ?>
            <!-- Show which other programmes share this module -->
            <p class="shared-programmes">
                📚 Also in: <?php echo htmlspecialchars($sm['shared_with']); ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
        <p>Programme not found!</p>
    <?php endif; ?>

</main>
</body>
</html>
