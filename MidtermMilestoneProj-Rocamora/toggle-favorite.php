<?php
require_once 'auth.php';
require_once 'classes/Favorite.php';
require_auth();

header('Content-Type: application/json');

$recipe_id = (int)($_POST['recipe_id'] ?? 0);
if ($recipe_id <= 0) {
    echo json_encode(['status' => 'error']);
    exit;
}

$favObj = new Favorite();
$isAdded = $favObj->toggle($_SESSION['user_id'], $recipe_id);

echo json_encode(['status' => $isAdded ? 'added' : 'removed']);