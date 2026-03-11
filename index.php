<?php
require 'Includes/db.php';

$level = isset($_GET['level']) ? $_GET['level'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM programmes WHERE published = 1";
if ($level) $sql .= " AND level = '$level'";
if ($search) $sql .= " AND title LIKE '%$search%'";

$programmes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Course Hub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        /* Skip link for keyboard users */
        .skip-link { position: absolute; top: -40px; left: 0; background: #003366; color: white; padding: 8px; z-index: 100; }
        .skip-link:focus { top: 0; }
        header { background: #003366; color: white; padding: 20px; text-align: center; }
        main { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { margin: 20px 0; color: #003366; }
        .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters input { padding: 10px; border: 1px solid #ccc; border-radius: 5px; flex: 1; font-size: 16px; }
        .filters select { padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        .filters button { background: #003366; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .filters button:focus { outline: 3px solid #ffbf00; }
        .programmes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .programme-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .programme-card h3 { color: #003366; margin-bottom: 10px; }
        .level { background: #e0f0ff; color: #003366; padding: 3px 10px; border-radius: 20px; font-size: 13px; }
        .programme-card p { margin: 10px 0; font-size: 14px; }
        .programme-card a { display: inline-block; margin-top: 10px; background: #003366; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; }
        .programme-card a:focus { outline: 3px solid #ffbf00; }
        .no-results { text-align: center; color: #666; margin-top: 40px; }
        /* Mobile friendly */
        @media (max-width: 600px) {
            .filters { flex-direction: column; }
            .programmes-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Skip link for keyboard navigation -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header role="banner">
        <h1>Student Course Hub</h1>
        <p>Find your perfect degree programme</p>
    </header>

    <main id="main-content" role="main">
        <h2>Available Programmes</h2>

        <form method="GET" class="filters" role="search" aria-label="Search and filter programmes">
            <label for="search" class="sr-only">Search programmes</label>
            <input type="text" id="search" name="search" placeholder="Search programmes..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   aria-label="Search programmes by keyword">
            <label for="level" class="sr-only">Filter by level</label>
            <select id="level" name="level" aria-label="Filter by level">
                <option value="">All Levels</option>
                <option value="Undergraduate" <?php echo $level === 'Undergraduate' ? 'selected' : ''; ?>>Undergraduate</option>
                <option value="Postgraduate" <?php echo $level === 'Postgraduate' ? 'selected' : ''; ?>>Postgraduate</option>
            </select>
            <button type="submit" aria-label="Search programmes">Search</button>
        </form>

        <?php if (empty($programmes)): ?>
            <p class="no-results" role="alert">No programmes found. Try a different search!</p>
        <?php else: ?>
            <div class="programmes-grid" role="list">
                <?php foreach ($programmes as $programme): ?>
                    <div class="programme-card" role="listitem">
                        <h3><?php echo htmlspecialchars($programme['title']); ?></h3>
                        <span class="level" aria-label="Level: <?php echo htmlspecialchars($programme['level']); ?>">
                            <?php echo htmlspecialchars($programme['level']); ?>
                        </span>
                        <p><?php echo htmlspecialchars($programme['description']); ?></p>
                        <a href="programme.php?id=<?php echo $programme['id']; ?>"
                           aria-label="View details for <?php echo htmlspecialchars($programme['title']); ?>">
                            View Programme
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>