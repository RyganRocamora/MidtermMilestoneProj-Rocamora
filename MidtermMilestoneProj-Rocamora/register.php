<?php
require_once 'auth.php';
require_once 'classes/User.php';
require_guest();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || strlen($password) < 6) {
        $error = "Please fill in all fields (Password min 6 chars).";
    } else {
        $userObj = new User();
        $result = $userObj->register($username, $email, $password);
        if ($result === true) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register · The Cook of the Town</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff8f0; margin: 0; padding: 20px; }
        .card { max-width: 400px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); border-top: 5px solid #d9534f; }
        input, select { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; background: #d9534f; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .error { color: #d9534f; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>The Cook of the Town</h2>
        <p>Join our home cooking community!</p>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Email Address</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" minlength="6" required>
            <button type="submit" class="btn">Create Account</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">Already a member? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>