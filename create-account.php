<?php
// Start a new session or resume the existing session
session_start();

// Include the library file, which likely contains the database connection function and other utilities
include './includes/library.php';
// Establish a database connection using the connectdb() function
$pdo = connectdb();

try {
    // Attempt to connect to the database by calling the connectdb() function
    $pdo = connectdb();
} catch (PDOException $e) {
    // If a PDOException occurs (e.g., database connection error), display a custom error message and stop the script
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// Initialize form fields to empty strings, preventing undefined variable errors
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Initialize an empty array to store error messages
$errors = [];

// Check if the form was submitted via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Username validation
    if (empty($username)) {
        // If username is empty, add an error message to the errors array
        $errors['username'] = "Username is required.";
    } else {
        // Prepare a SQL query to check if the username already exists in the database
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM assn2_users WHERE username = ?');
        // Execute the query with the provided username
        $stmt->execute([$username]);
        // If a record with this username exists, add an error message
        if ($stmt->fetchColumn() > 0) {
            $errors['username'] = "Username is already taken.";
        }
    }

    // Email validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // If email is empty or not in a valid format, add an error message
        $errors['email'] = "You must enter a valid email.";
    } else {
        // Prepare a SQL query to check if the email already exists in the database
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM assn2_users WHERE email = ?');
        // Execute the query with the provided email
        $stmt->execute([$email]);
        // If a record with this email exists, add an error message
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "Email is already in use.";
        }
    }

    // Password validation
    if (empty($password)) {
        // If password is empty, add an error message
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8 || !preg_match('/[0-9]/', $password)) {
        // If password is shorter than 8 characters or lacks a number, add an error message
        $errors['password'] = "Password must be at least 8 characters long and include at least one number.";
    }

    // Check if there were no validation errors
    if (empty($errors)) {
        // Hash the password using a secure hash algorithm
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Generate a unique API key for the user by creating a random 16-byte hex string
        $apiKey = bin2hex(random_bytes(16));
        // Set the API creation date to the current date and time
        $apiDate = date('Y-m-d H:i:s');

        // Prepare a SQL query to insert the new user into the database
        $stmt = $pdo->prepare('INSERT INTO assn2_users (username, email, password, api_key, api_date) VALUES (?, ?, ?, ?, ?)');
        // Execute the query with the provided data (username, email, hashed password, API key, and date)
        if ($stmt->execute([$username, $email, $hashedPassword, $apiKey, $apiDate])) {
            // If the insertion is successful, display a success message with the user's API key and stop the script
            echo "Account created successfully! Your API key is: " . $apiKey;
            exit;
        } else {
            // If insertion fails, display an error message and stop the script
            echo "Account creation failed. Please try again later.";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link rel="stylesheet" href="./styles/main.css">
</head>

<body>
    <form method="POST" action="create-account.php">
        <h2>Create Account</h2>

        <?php if (!empty($errors)): ?>
            <!-- Display all error messages, if any exist -->
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Input for Username -->
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>

        <!-- Input for Email -->
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <!-- Input for Password -->
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <!-- Submit button for the form -->
        <button type="submit">Create Account</button>
    </form>
</body>

</html>