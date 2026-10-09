<?php
require_once 'auth.php';
require_once 'db.php';
require_auth();

$pdo = Database::getInstance();
$comment_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$comment_id, $_SESSION['user_id']]);
$comment = $stmt->fetch();

if (!$comment) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $stmtDel = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmtDel->execute([$comment_id, $_SESSION['user_id']]);
    } else {
        $content = trim($_POST['content'] ?? '');
        if (!empty($content)) {
            $stmtUpd = $pdo->prepare("UPDATE comments SET content = ?, is_edited = 1 WHERE id = ? AND user_id = ?");
            $stmtUpd->execute([$content, $comment_id, $_SESSION['user_id']]);
        }
    }
    header("Location: recipe-details.php?id=" . $comment['recipe_id']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Comment · The Cook of the Town</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff8f0; padding: 20px; }
        .card { max-width: 450px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        textarea { width: 100%; padding: 10px; box-sizing: border-box; }
        .btn { background: #0275d8; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; }
        .btn-del { background: #d9534f; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; margin-left: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h3>Edit Your Comment</h3>
    <form method="POST">
        <textarea name="content" rows="4" required><?= htmlspecialchars($comment['content']) ?></textarea>
        <div style="margin-top: 15px;">
            <button type="submit" class="btn">Save Changes</button>
            <button type="submit" name="action" value="delete" class="btn-del" onclick="return confirm('Delete this comment?');">Delete Comment</button>
        </div>
    </form>
</div>
</body>
</html>