<?php
require_once 'auth.php';
require_once 'classes/Recipe.php';
require_once 'classes/Favorite.php';
require_once 'db.php';
require_auth();

$pdo = Database::getInstance();
$recipeObj = new Recipe();
$favoriteObj = new Favorite();

$search = trim($_GET['search'] ?? '');
$category_id = (int)($_GET['category_id'] ?? 0);

$recipes = $recipeObj->getAll($search, $category_id);
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Feed · The Cook of the Town</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff8f0; margin: 0; padding: 0; }
        .navbar { background: #d9534f; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .container { max-width: 900px; margin: 20px auto; padding: 0 15px; }
        .filter-box { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .filter-box input, .filter-box select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; flex: 1; }
        .recipe-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: relative; display: flex; flex-direction: column; justify-content: space-between; }
        .badge { background: #f0ad4e; color: white; font-size: 0.75em; padding: 3px 8px; border-radius: 12px; display: inline-block; }
        .fav-btn { background: none; border: none; font-size: 1.5em; cursor: pointer; float: right; }
        .btn-add { background: #5cb85c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<div class="navbar">
    <h2> The Cook of the town </h2>
    <div>
        <span>Mabuhay, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</span>
        <a href="add-recipe.php" class="btn-add">+ Share Recipe</a>
        <a href="favorites.php">My Saved Favorites</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <form class="filter-box" method="GET">
        <input type="text" name="search" placeholder="Search dish or ingredient..." value="<?= htmlspecialchars($search) ?>">
        <select name="category_id">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="padding: 8px 15px; background: #d9534f; color: white; border: none; border-radius: 4px;">Filter</button>
    </form>

    <div class="recipe-grid">
        <?php foreach ($recipes as $r): ?>
            <?php $isFav = $favoriteObj->isFavorite($_SESSION['user_id'], $r['id']); ?>
            <div class="card">
                <div>
                    <button class="fav-btn" onclick="toggleFav(<?= $r['id'] ?>, this)">
                        <?= $isFav ? '❤️' : '🤍' ?>
                    </button>
                    <span class="badge"><?= htmlspecialchars($r['category_name']) ?></span>
                    <h3 style="margin: 10px 0 5px;"><?= htmlspecialchars($r['title']) ?></h3>
                    <small style="color: #666;">By <?= htmlspecialchars($r['username']) ?> | Est: <?= htmlspecialchars($r['budget_range']) ?></small>
                    <p style="color: #444; font-size: 0.9em; margin-top: 10px;">
                        <?= htmlspecialchars(substr($r['description'], 0, 100)) ?>...
                    </p>
                </div>
                <a href="recipe-details.php?id=<?= $r['id'] ?>" style="color: #d9534f; font-weight: bold; text-decoration: none; margin-top: 15px; display: inline-block;">View Full Recipe & Instructions &rarr;</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleFav(recipeId, btn) {
    fetch('toggle-favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'recipe_id=' + recipeId
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'added') {
            btn.innerHTML = '❤️';
        } else if (data.status === 'removed') {
            btn.innerHTML = '🤍';
        }
    });
}
</script>

</body>
</html>