<?php
require_once 'auth.php';
require_once 'classes/Recipe.php';
require_once 'db.php';
require_auth();

$pdo = Database::getInstance();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $category_id  = (int)($_POST['category_id'] ?? 0);
    $budget       = trim($_POST['budget_range'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $ingredients  = $_POST['ingredients'] ?? [];

    if (empty($title) || $category_id <= 0 || empty($description) || empty($instructions) || empty($ingredients)) {
        $error = "Please fill out all recipe fields and include at least one ingredient.";
    } else {
        $recipeObj = new Recipe();
        $recipe_id = $recipeObj->create($_SESSION['user_id'], $category_id, $title, $description, $instructions, $budget, $ingredients);
        if ($recipe_id) {
            header("Location: recipe-details.php?id=" . $recipe_id);
            exit;
        } else {
            $error = "Failed to create recipe. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Recipe · The Cook of the Town</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff8f0; margin: 0; padding: 20px; }
        .card { max-width: 650px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        input, select, textarea { width: 100%; padding: 10px; margin: 6px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .ing-row { display: flex; gap: 10px; margin-bottom: 8px; }
        .btn { background: #5cb85c; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; }
        .error { color: #d9534f; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Post a New Family Recipe</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="add-recipe.php">
        <label for="title">Recipe Title</label>
        <input type="text" id="title" name="title" placeholder="e.g. Special Pork Adobo sa Gata" required>

        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="budget_range">Estimated Budget Cost</label>
        <select id="budget_range" name="budget_range">
            <option value="Under ₱150">Under ₱150 (Budget Meal)</option>
            <option value="₱150 - ₱300">₱150 - ₱300 (Standard Ulam)</option>
            <option value="₱300+">₱300+ (Special Fiesta / Occasion)</option>
        </select>

        <label for="description">Short Description</label>
        <textarea id="description" name="description" rows="2" required></textarea>

        <label>Ingredients (Dynamic)</label>
        <div id="ingredient-list">
            <div class="ing-row"><input type="text" name="ingredients[]" placeholder="e.g., 1/2 kg Pork Belly" required></div>
        </div>
        <button type="button" onclick="addIngredientField()" style="margin-bottom: 15px; background: #f0ad4e; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">+ Add More Ingredients</button>

        <label for="instructions">Cooking Steps / Instructions</label>
        <textarea id="instructions" name="instructions" rows="5" required></textarea>

        <button type="submit" class="btn">Publish Recipe</button>
        <p><a href="index.php">Cancel & Return</a></p>
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