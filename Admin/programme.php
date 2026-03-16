<?php
// Start session and check admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Include database connection
require '../Includes/db.php';

$message = '';

// Handle DELETE request - remove a programme by ID
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM programmes WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $message = 'Programme deleted successfully.';
}

// Handle TOGGLE publish/unpublish request
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE programmes SET published = NOT published WHERE id = ?");
    $stmt->execute([(int)$_GET['toggle']]);
    header('Location: programmes.php');
    exit;
}

// Handle ADD or EDIT form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize all inputs to prevent XSS
    $title       = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $level       = $_POST['level'];
    $image       = htmlspecialchars(trim($_POST['image']), ENT_QUOTES, 'UTF-8');
    $published   = isset($_POST['published']) ? 1 : 0;
    $id          = (int)$_POST['id'];

    if ($id > 0) {
        // Update existing programme
        $stmt = $pdo->prepare("UPDATE programmes SET title=?, description=?, level=?, image=?, published=? WHERE id=?");
        $stmt->execute([$title, $description, $level, $image, $published, $id]);
        $message = 'Programme updated successfully.';
    } else {
        // Insert new programme
        $stmt = $pdo->prepare("INSERT INTO programmes (title, description, level, image, published) VALUES (?,?,?,?,?)");
        $stmt->execute([$title, $description, $level, $image, $published]);
        $message = 'Programme added successfully.';
    }
    header('Location: programmes.php');
    exit;
}

// Load programme data if editing
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM programmes WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all programmes from database
$programmes = $pdo->query("SELECT * FROM programmes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programmes</title>
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
        .form-row { display: flex; gap: 1rem; flex-wrap: wrap; }
        .form-row > div { flex: 1; }
        button[type=submit] { background: #1a1a2e; color: white; padding: 0.7rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; }
        button[type=submit]:hover { background: #16213e; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        th { background: #1a1a2e; color: white; padding: 0.8rem 1rem; text-align: left; font-size: 0.9rem; }
        td { padding: 0.8rem 1rem; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .badge { padding: 0.3rem 0.7rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .published { background: #d4edda; color: #155724; }
        .unpublished { background: #f8d7da; color: #721c24; }
        .actions a { margin-right: 0.5rem; text-decoration: none; font-size: 0.85rem; padding: 0.3rem 0.7rem; border-radius: 4px; }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-toggle { background: #17a2b8; color: white; }
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
    <h2>Manage Programmes</h2>

    <!-- Success message after add/edit/delete -->
    <?php if ($message): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Add / Edit Programme Form -->
    <form method="POST">
        <h3><?= $edit ? 'Edit Programme' : 'Add New Programme' ?></h3>
        <!-- Hidden ID - 0 means new, >0 means edit -->
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required>
        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        <div class="form-row">
            <div>
                <!-- Dropdown for programme level -->
                <label>Level</label>
                <select name="level">
                    <option value="Undergraduate" <?= ($edit['level'] ?? '') === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
                    <option value="Postgraduate" <?= ($edit['level'] ?? '') === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
                </select>
            </div>
            <div>
                <label>Image filename (e.g. cs.jpg)</label>
                <input type="text" name="image" value="<?= htmlspecialchars($edit['image'] ?? '') ?>">
            </div>
        </div>
        <!-- Checkbox to publish or unpublish -->
        <label>
            <input type="checkbox" name="published" value="1" <?= ($edit['published'] ?? 1) ? 'checked' : '' ?>>
            Published
        </label><br><br>
        <button type="submit"><?= $edit ? 'Update Programme' : 'Add Programme' ?></button>
        <?php if ($edit): ?>
            <a href="programmes.php" style="margin-left:1rem; color:#666;">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Table listing all programmes -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Level</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <!-- Loop through each programme -->
        <?php foreach ($programmes as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= $p['level'] ?></td>
                <td>
                    <!-- Published or unpublished badge -->
                    <span class="badge <?= $p['published'] ? 'published' : 'unpublished' ?>">
                        <?= $p['published'] ? 'Published' : 'Unpublished' ?>
                    </span>
                </td>
                <td class="actions">
                    <!-- Edit, toggle and delete buttons -->
                    <a href="?edit=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                    <a href="?toggle=<?= $p['id'] ?>" class="btn-toggle"><?= $p['published'] ? 'Unpublish' : 'Publish' ?></a>
                    <a href="?delete=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('Delete this programme?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>