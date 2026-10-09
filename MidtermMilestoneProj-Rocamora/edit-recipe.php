<?php
require_once 'auth.php';
require_once 'classes/Recipe.php';
require_once 'db.php';
require_auth();

$recipeObj = new Recipe();$pdo = Database::getInstance();

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {$recipe_id = (int)$_POST['recipe_id'];$recipeObj->delete($recipe_id,$_SESSION['user_id']);
    header("Location: index.php");
    exit;
}

$recipe_id = (int)($_GET['id'] ?? 0);$recipe = $recipeObj->getById($recipe_id);

if (!$recipe || $recipe['user_id'] !=$_SESSION['user_id']) {
    header("Location: index.php");
    exit;
}

$ingredients = $recipeObj->getIngredients($recipe_id);
$categories =$pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $title        = trim($_POST['title'] ?? '');
    $category_id  = (int)($_POST['category_id'] ?? 0);
    $budget       = trim($_POST['budget_range'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $ing_list     =$_POST['ingredients'] ?? [];

    if (empty($title) || $category_id <= 0 \vert{}\vert{} empty($description) || empty($instructions)) {$error = "Please fill in all mandatory fields.";
    } else {
        $recipeObj->update($recipe_id,$_SESSION['user_id'], $category_id,$title, $description,$instructions, $budget,$ing_list);
        header("Location: recipe-details.php?id=" . $recipe_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe · The Cook of the Town</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff8f0; margin: 0; padding: 20px; }
        .card { max-width: 650px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        input, select, textarea { width: 100%; padding: 10px; margin: 6px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .ing-row { display: flex; gap: 10px; margin-bottom: 8px; }
        .btn { background: #0275d8; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
<div class="card">
    <h2>Edit Recipe</h2>
    <form method="POST">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>

        <label>Category</label>
        <select name="category_id" required>
            <?php foreach ($categories as$cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $recipe['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Estimated Budget Cost</label>
        <select name="budget_range">
            <option value="Under ₱150" <?= $recipe['budget_range'] == 'Under ₱150' ? 'selected' : '' ?>>Under ₱150</option>
            <option value="₱150 - ₱300" <?= $recipe['budget_range'] == '₱150 - ₱300' ? 'selected' : '' ?>>₱150 - ₱300</option>
            <option value="₱300+" <?= $recipe['budget_range'] == '₱300+' ? 'selected' : '' ?>>₱300+</option>
        </select>

        <label>Description</label>
        <textarea name="description" rows="2" required><?= htmlspecialchars($recipe['description']) ?></textarea>

        <label>Ingredients</label>
        <div id="ingredient-list">
            <?php foreach ($ingredients as$ing): ?>
                <div class="ing-row">
                    <input type="text" name="ingredients[]" value="<?= htmlspecialchars($ing['ingredient_name']) ?>" required>
                    <button type="button" onclick="this.parentElement.remove()" style="background:#d9534f; color:white; border:none; border-radius:4px; padding:0 10px;">X</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addIngredientField()" style="margin-bottom: 15px; background: #f0ad4e; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">+ Add Ingredient</button>

        <label>Instructions</label>
        <textarea name="instructions" rows="5" required><?= htmlspecialchars($recipe['instructions']) ?></textarea>

        <button type="submit" class="btn">Update Recipe</button>
    </form>
</div>
<script>
function addIngredientField() {
    const container = document.getElementById('ingredient-list');
    const div = document.createElement('div');
    div.className = 'ing-row';
    div.innerHTML = `<input type="text" name="ingredients[]" placeholder="Next ingredient..." required>
                     <button type="button" onclick="this.parentElement.remove()" style="background:#d9534f; color:white; border:none; border-radius:4px; padding:0 10px;">X</button>`;
    container.appendChild(div);
}
</script>
</body>
</html>