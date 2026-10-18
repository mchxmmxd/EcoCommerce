<?php require_once __DIR__.'/includes/auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: profile.php");
    exit;
}

$error = $_SESSION["auth_error"] ?? "";
unset($_SESSION["auth_error"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EcoCommerce</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<div class="auth-container">
    <h1>Welcome Back!</h1>

    <?php if ($error): ?>
        <p class="auth-error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form action="actions/login.php" method="POST"><?= csrf() ?>

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <button type="submit" class="auth-btn">
            Login
        </button>

    </form>

    <p>
        Don't have an account?
        <a href="register.php">Register</a>
    </p>
</div>

</body>
</html>