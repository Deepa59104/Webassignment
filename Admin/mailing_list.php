<?php
// Start session and check admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Include database connection
require '../Includes/db.php';

// Handle CSV export request
if (isset($_GET['export'])) {
    $programme_id = (int)$_GET['export'];

    // Get programme title for filename
    $prog = $pdo->prepare("SELECT title FROM programmes WHERE id = ?");
    $prog->execute([$programme_id]);
    $progTitle = $prog->fetchColumn();

    // Fetch all students interested in this programme
    $stmt = $pdo->prepare("
        SELECT s.name, s.email
        FROM students s
        JOIN interests i ON s.id = i.student_id
        WHERE i.programme_id = ?
    ");
    $stmt->execute([$programme_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers to force CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="mailing_list_' . $progTitle . '.csv"');

    // Write CSV data
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Email']); // Column headers
    foreach ($students as $student) {
        fputcsv($output, [$student['name'], $student['email']]);
    }
    fclose($output);
    exit;
}

// Handle DELETE interest registration
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM interests WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: mailing_list.php');
    exit;
}

// Fetch all programmes with interested student counts
$programmes = $pdo->query("
    SELECT p.id, p.title, COUNT(i.id) as total
    FROM programmes p
    LEFT JOIN interests i ON p.id = i.programme_id
    GROUP BY p.id
    ORDER BY p.title ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Get selected programme from URL
$selected = isset($_GET['programme']) ? (int)$_GET['programme'] : null;

// Fetch students for selected programme
$students = [];
if ($selected) {
    $stmt = $pdo->prepare("
        SELECT i.id as interest_id, s.name, s.email, p.title as programme
        FROM interests i
        JOIN students s ON i.student_id = s.id
        JOIN programmes p ON i.programme_id = p.id
        WHERE i.programme_id = ?
        ORDER BY s.name ASC
    ");
    $stmt->execute([$selected]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailing List</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #1a1a2e; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { color: white; font-size: 1.1rem; }
        nav a { color: #ccc; text-decoration: none; margin-left: 1.5rem; font-size: 0.9rem; }
        nav a:hover { color: white; }
        .container { padding: 2rem; }
        h2 { color: #1a1a2e; margin-bottom: 1rem; }
        .prog-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .prog-card { background: white; padding: 1.2rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .prog-card h4 { color: #1a1a2e; margin-bottom: 0.5rem; }
        .prog-card p { font-size: 0.85rem; color: #666; margin-bottom: 0.8rem; }
        .prog-card a { text-decoration: none; font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 4px; margin-right: 0.4rem; }
        .btn-view { background: #1a1a2e; color: white; }
        .btn-export { background: #28a745; color: white; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        th { background: #1a1a2e; color: white; padding: 0.8rem 1rem; text-align: left; }
        td { padding: 0.8rem 1rem; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .btn-delete { background: #dc3545; color: white; text-decoration: none; font-size: 0.85rem; padding: 0.3rem 0.7rem; border-radius: 4px; }
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
    <h2>Mailing List</h2>

    <!-- Programme cards with student counts -->
    <div class="prog-list">
        <?php foreach ($programmes as $p): ?>
        <div class="prog-card">
            <h4><?= htmlspecialchars($p['title']) ?></h4>
            <!-- Number of interested students -->
            <p><?= $p['total'] ?> student(s) interested</p>
            <!-- View and export buttons -->
            <a href="?programme=<?= $p['id'] ?>" class="btn-view">View List</a>
            <a href="?export=<?= $p['id'] ?>" class="btn-export">Export CSV</a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Show students if a programme is selected -->
    <?php if ($selected && count($students) > 0): ?>
        <h3 style="margin-bottom:1rem; color:#1a1a2e;">
            Students interested in: <?= htmlspecialchars($students[0]['programme']) ?>
        </h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <!-- Loop through interested students -->
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td>
                        <!-- Remove interest button -->
                        <a href="?delete=<?= $s['interest_id'] ?>" class="btn-delete" onclick="return confirm('Remove this interest?')">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($selected): ?>
        <!-- No students found message -->
        <p>No students have registered interest in this programme yet.</p>
    <?php endif; ?>
</div>
</body>
</html>