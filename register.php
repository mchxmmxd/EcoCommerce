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
    <title>Register | EcoCommerce</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<div class="auth-container">
    <h1>Create Account</h1>

    <?php if ($error): ?>
        <p class="auth-error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form action="actions/register.php" method="POST"><?= csrf() ?>

        <label for="name">Full Name</label>
        <input
            type="text"
            id="name"
            name="name"
            maxlength="100"
            required
        >

        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="150"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >

        <label for="confirm_password">
            Confirm Password
        </label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            required
        >

        <button type="submit" class="auth-btn">
            Register
        </button>
    </form>

    <p>
        Already have an account?
        <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>