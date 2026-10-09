<?php
require_once 'auth.php';
require_once 'classes/Recipe.php';
require_once 'db.php';
require_auth();

$pdo = Database::getInstance();
$recipeObj = new Recipe();

$recipe_id = (int)($_GET['id'] ?? 0);
$recipe = $recipeObj->getById($recipe_id);

if (!$recipe) {
    header("Location: index.php");
    exit;
}

$ingredients = $recipeObj->getIngredients($recipe_id);

// Comment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $comment_text = trim($_POST['comment_content']);
    if (!empty($comment_text)) {
        $stmt = $pdo->prepare("INSERT INTO comments (recipe_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$recipe_id, $_SESSION['user_id'], $comment_text]);
        header("Location: recipe-details.php?id=" . $recipe_id);
        exit;
    }
}

// Fetch comments
$stmtC = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.recipe_id = ? ORDER BY c.created_at ASC");
$stmtC->execute([$recipe_id]);
$comments = $stmtC->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?> · The Cook of the Town</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff8f0; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .badge { background: #f0ad4e; color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.8em; }
        .comment { background: #fdfaf7; border-left: 4px solid #d9534f; padding: 10px 15px; margin-bottom: 10px; border-radius: 4px; }
        .btn-edit { color: #337ab7; text-decoration: none; margin-right: 10px; }
        .btn-delete { color: #d9534f; background: none; border: none; cursor: pointer; text-decoration: underline; padding: 0; }
    </style>
</head>
<body>
<div class="container">
    <p><a href="index.php">&larr; Back to Feed</a></p>
    
    <h2>
        <?= htmlspecialchars($recipe['title']) ?>
        <?php if ($recipe['is_edited']): ?><small style="color:#888; font-size:0.5em;">(edited)</small><?php endif; ?>
    </h2>
    <p><span class="badge"><?= htmlspecialchars($recipe['category_name']) ?></span> | Estimated Cost: <strong><?= htmlspecialchars($recipe['budget_range']) ?></strong></p>
    <p><em>Shared by <?= htmlspecialchars($recipe['username']) ?> on <?= $recipe['created_at'] ?></em></p>

    <?php if ($recipe['user_id'] == $_SESSION['user_id']): ?>
        <p>
            <a href="edit-recipe.php?id=<?= $recipe['id'] ?>" class="btn-edit">Edit Recipe</a>
            <form method="POST" action="edit-recipe.php" style="display:inline;" onsubmit="return confirm('Delete this recipe entirely?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                <button type="submit" class="btn-delete">Delete Recipe</button>
            </form>
        </p>
    <?php endif; ?>

    <hr>
    <h3>Description</h3>
    <p><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>

    <h3>Ingredients</h3>
    <ul>
        <?php foreach ($ingredients as $ing): ?>
            <li><?= htmlspecialchars($ing['ingredient_name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Cooking Steps</h3>
    <p><?= nl2br(htmlspecialchars($recipe['instructions'])) ?></p>

    <hr style="margin: 30px 0;">

    <h3>Neighbor Feedback & Comments</h3>
    <?php foreach ($comments as $c): ?>
        <div class="comment">
            <strong><?= htmlspecialchars($c['username']) ?></strong> 
            <small style="color:#777;"><?= $c['created_at'] ?></small>
            <?php if ($c['is_edited']): ?><small style="color:#888;">(edited)</small><?php endif; ?>
            <p style="margin: 5px 0;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
            
            <?php if ($c['user_id'] == $_SESSION['user_id']): ?>
                <a href="edit-comment.php?id=<?= $c['id'] ?>" style="font-size: 0.85em;">Edit</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <form method="POST" style="margin-top: 20px;">
        <textarea name="comment_content" rows="3" placeholder="Leave a feedback or tip for this recipe..." required style="width: 100%; box-sizing: border-box; padding: 10px;"></textarea>
        <button type="submit" style="margin-top: 8px; background: #d9534f; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Post Comment</button>
    </form>
</div>
</body>
</html>