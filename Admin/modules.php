<?php
// Start session and check admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Include database connection
require '../Includes/db.php';

$message = '';

// Handle DELETE request
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $message = 'Module deleted successfully.';
}

// Handle ADD or EDIT form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs to prevent XSS
    $name        = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $year        = (int)$_POST['year'];
    $id          = (int)$_POST['id'];

    if ($id > 0) {
        // Update existing module
        $stmt = $pdo->prepare("UPDATE modules SET name=?, description=?, year=? WHERE id=?");
        $stmt->execute([$name, $description, $year, $id]);
        $message = 'Module updated successfully.';
    } else {
        // Insert new module
        $stmt = $pdo->prepare("INSERT INTO modules (name, description, year) VALUES (?,?,?)");
        $stmt->execute([$name, $description, $year]);
        $message = 'Module added successfully.';
    }
    header('Location: modules.php');
    exit;
}

// Load module data if editing
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all modules from database
$modules = $pdo->query("SELECT * FROM modules ORDER BY year ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Modules</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #1a1a2e; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { color: white; font-size: 1.1rem; }
        nav a { color: #ccc; text-decoration: none; margin-left: 1.5rem; font-size: 0.9rem; }
        nav a:hover { color: white; }
        .container { padding: 2rem; }
        h2 { color: #1a1a2e; margin-bottom: 1rem; }
        .success { background: #d4edda; color: #155724; padding: 0.7rem 1rem; border-radius: 5px; margin-bottom: 1rem; }
        form { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        form h3 { margin-bottom: 1rem; color: #1a1a2e; }
        label { display: block; margin-bottom: 0.3rem; font-size: 0.9rem; color: #444; }
        input[type=text], textarea, select { width: 100%; padding: 0.6rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 5px; font-size: 0.95rem; }
        textarea { height: 80px; resize: vertical; }
        button[type=submit] { background: #1a1a2e; color: white; padding: 0.7rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; }
        button[type=submit]:hover { background: #16213e; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        th { background: #1a1a2e; color: white; padding: 0.8rem 1rem; text-align: left; font-size: 0.9rem; }
        td { padding: 0.8rem 1rem; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .actions a { margin-right: 0.5rem; text-decoration: none; font-size: 0.85rem; padding: 0.3rem 0.7rem; border-radius: 4px; }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-delete { background: #dc3545; color: white; }
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
    <h2>Manage Modules</h2>

    <!-- Success message -->
    <?php if ($message): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Add / Edit Module Form -->
    <form method="POST">
        <h3><?= $edit ? 'Edit Module' : 'Add New Module' ?></h3>
        <!-- Hidden ID - 0 = new, >0 = edit -->
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <label>Module Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        <!-- Year of study dropdown -->
        <label>Year of Study</label>
        <select name="year">
            <option value="1" <?= ($edit['year'] ?? '') == 1 ? 'selected' : '' ?>>Year 1</option>
            <option value="2" <?= ($edit['year'] ?? '') == 2 ? 'selected' : '' ?>>Year 2</option>
            <option value="3" <?= ($edit['year'] ?? '') == 3 ? 'selected' : '' ?>>Year 3</option>
            <option value="4" <?= ($edit['year'] ?? '') == 4 ? 'selected' : '' ?>>Year 4</option>
        </select>
        <button type="submit"><?= $edit ? 'Update Module' : 'Add Module' ?></button>
        <?php if ($edit): ?>
            <a href="modules.php" style="margin-left:1rem; color:#666;">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Table of all modules -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Module Name</th>
                <th>Description</th>
                <th>Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <!-- Loop through each module -->
        <?php foreach ($modules as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['description']) ?></td>
                <td>Year <?= $m['year'] ?></td>
                <td class="actions">
                    <!-- Edit and delete buttons -->
                    <a href="?edit=<?= $m['id'] ?>" class="btn-edit">Edit</a>
                    <a href="?delete=<?= $m['id'] ?>" class="btn-delete" onclick="return confirm('Delete this module?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>