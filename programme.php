<?php
require 'Includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM programmes WHERE id = ?");
$stmt->execute([$id]);
$programme = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("
    SELECT m.*, s.name as staff_name
    FROM modules m
    JOIN programme_modules pm ON pm.module_id = m.id
    LEFT JOIN module_staff ms ON ms.module_id = m.id
    LEFT JOIN staff s ON s.id = ms.staff_id
    WHERE pm.programme_id = ?
    ORDER BY m.year
");
$stmt2->execute([$id]);
$modules = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($programme['title']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        a:focus, button:focus { outline: 3px solid #ffbf00; }
        header { background: #003366; color: white; padding: 20px; text-align: center; }
        main { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .back { color: #003366; text-decoration: none; display: block; margin-bottom: 15px; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        h2 { color: #003366; margin-bottom: 10px; }
        h3 { color: #003366; margin: 15px 0 8px; }
        .level { background: #e0f0ff; color: #003366; padding: 3px 10px; border-radius: 20px; font-size: 13px; }
        .btn { display: inline-block; margin-top: 15px; background: #003366; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
        .module { background: #f9f9f9; border-left: 4px solid #003366; padding: 10px; margin-bottom: 8px; border-radius: 4px; }
        .module small { color: #666; font-size: 12px; }
        @media (max-width: 600px) { main { padding: 0 10px; } }
    </style>
</head>
<body>
    <header role="banner">
        <h1>Student Course Hub</h1>
    </header>
    <main role="main">
        <a href="index.php" class="back">← Back to Programmes</a>
        <div class="card">
            <h2><?php echo htmlspecialchars($programme['title']); ?></h2>
            <span class="level"><?php echo htmlspecialchars($programme['level']); ?></span>
            <p style="margin-top:10px;"><?php echo htmlspecialchars($programme['description']); ?></p>
            <a href="register_interest.php?id=<?php echo $programme['id']; ?>" class="btn">Register Interest</a>
        </div>
        <div class="card">
            <h2>Modules</h2>
            <?php $year = 0; foreach ($modules as $m): ?>
                <?php if ($m['year'] != $year): $year = $m['year']; ?>
                    <h3>Year <?php echo $year; ?></h3>
                <?php endif; ?>
                <div class="module">
                    <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
                    <small>👨‍🏫 <?php echo htmlspecialchars($m['staff_name']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>