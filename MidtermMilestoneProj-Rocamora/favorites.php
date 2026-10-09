<?php
require_once 'auth.php';
require_once 'classes/Favorite.php';
require_auth();

$favObj = new Favorite();
$favorites = $favObj->getUserFavorites($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Favorites · The Cook of the Town</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff8f0; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .item { background: #fdfaf7; border-left: 4px solid #d9534f; padding: 15px; margin-bottom: 12px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
<div class="container">
    <p><a href="index.php">&larr; Back to Main Feed</a></p>
    <h2> My Saved Barangay Recipes</h2>

    <?php if (empty($favorites)): ?>
        <p>You have not saved any recipes yet.</p>
    <?php endif; ?>

    <?php foreach ($favorites as $f): ?>
        <div class="item">
            <div>
                <h3 style="margin: 0;"><?= htmlspecialchars($f['title']) ?></h3>
                <small>By <?= htmlspecialchars($f['username']) ?> | <?= htmlspecialchars($f['category_name']) ?></small>
            </div>
            <a href="recipe-details.php?id=<?= $f['id'] ?>" style="color: #d9534f; font-weight: bold; text-decoration: none;">View Recipe &rarr;</a>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>