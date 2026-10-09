<?php
require_once __DIR__ . '/../db.php';

class Recipe {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll($search = '', $category_id = 0) {
        $sql = "SELECT r.*, c.name AS category_name, u.username 
                FROM recipes r
                JOIN categories c ON r.category_id = c.id
                JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($category_id > 0) {
            $sql .= " AND r.category_id = ?";
            $params[] = $category_id;
        }

        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT r.*, c.name AS category_name, u.username 
                                    FROM recipes r 
                                    JOIN categories c ON r.category_id = c.id 
                                    JOIN users u ON r.user_id = u.id 
                                    WHERE r.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getIngredients($recipe_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredients WHERE recipe_id = ?");
        $stmt->execute([$recipe_id]);
        return $stmt->fetchAll();
    }

    public function create($user_id, $category_id, $title, $description, $instructions, $budget, $ingredients) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO recipes (user_id, category_id, title, description, instructions, budget_range) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $title, $description, $instructions, $budget]);
            $recipe_id = $this->pdo->lastInsertId();

            $stmtIng = $this->pdo->prepare("INSERT INTO ingredients (recipe_id, ingredient_name) VALUES (?, ?)");
            foreach ($ingredients as $ing) {
                $ing = trim($ing);
                if (!empty($ing)) {
                    $stmtIng->execute([$recipe_id, $ing]);
                }
            }

            $this->pdo->commit();
            return $recipe_id;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function update($recipe_id, $user_id, $category_id, $title, $description, $instructions, $budget, $ingredients) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE recipes SET category_id = ?, title = ?, description = ?, instructions = ?, budget_range = ?, is_edited = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$category_id, $title, $description, $instructions, $budget, $recipe_id, $user_id]);

            // Clear old ingredients and re-insert
            $stmtDel = $this->pdo->prepare("DELETE FROM ingredients WHERE recipe_id = ?");
            $stmtDel->execute([$recipe_id]);

            $stmtIng = $this->pdo->prepare("INSERT INTO ingredients (recipe_id, ingredient_name) VALUES (?, ?)");
            foreach ($ingredients as $ing) {
                $ing = trim($ing);
                if (!empty($ing)) {
                    $stmtIng->execute([$recipe_id, $ing]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function delete($recipe_id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
        return $stmt->execute([$recipe_id, $user_id]);
    }
}