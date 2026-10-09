<?php
require_once __DIR__ . '/../db.php';

class Favorite {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function isFavorite($user_id, $recipe_id) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        return (bool)$stmt->fetch();
    }

    public function toggle($user_id, $recipe_id) {
        if ($this->isFavorite($user_id, $recipe_id)) {
            $stmt = $this->pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$user_id, $recipe_id]);
            return false; // Removed
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $recipe_id]);
            return true; // Saved
        }
    }

    public function getUserFavorites($user_id) {
        $stmt = $this->pdo->prepare("SELECT r.*, c.name as category_name, u.username 
                                     FROM favorites f 
                                     JOIN recipes r ON f.recipe_id = r.id 
                                     JOIN categories c ON r.category_id = c.id
                                     JOIN users u ON r.user_id = u.id
                                     WHERE f.user_id = ? 
                                     ORDER BY f.created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
}