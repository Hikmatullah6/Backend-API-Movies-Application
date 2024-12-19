<?php
session_start();
include './includes/library.php';

try {
    $pdo = connectdb();
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare('SELECT * FROM assn2_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['api_key'] = $user['api_key'];
            header("Location: view-account.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Username and password are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="./styles/main.css">
</head>

<body>
    <form method="POST" action="login.php">
        <h2>Login</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required value="<?= $username ?>">

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>
</body>

</html>