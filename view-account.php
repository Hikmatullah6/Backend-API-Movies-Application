<?php
session_start();
include 'includes/library.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $pdo = connectdb();
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

if (isset($_POST['regenerate_api'])) {
    $newApiKey = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare('UPDATE assn2_users SET api_key = ?, api_date = NOW() WHERE user_id = ?');
    if ($stmt->execute([$newApiKey, $_SESSION['user_id']])) {
        $_SESSION['api_key'] = $newApiKey;
    }
}

$stmt = $pdo->prepare('SELECT username, email, api_key, api_date FROM assn2_users WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Account</title>
    <link rel="stylesheet" href="./styles/viewAccount.css">
</head>

<body>
    <h2>Your Account Details</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>API Key:</strong> <?php echo htmlspecialchars($user['api_key']); ?></p>
    <p><strong>API Key Last Updated:</strong> <?php echo htmlspecialchars($user['api_date']); ?></p>

    <form method="POST">
        <button type="submit" name="regenerate_api">Regenerate API Key</button>
    </form>
    <p><a href="logout.php">Logout</a></p>
</body>

</html>