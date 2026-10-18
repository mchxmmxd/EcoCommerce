<?php require_once __DIR__.'/../includes/auth.php'; ?>
<?php

if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

$loggedIn = isset($_SESSION["user_id"]);

$cartCount = array_sum($_SESSION["cart"] ?? []);

?>

<nav class="navbar">

    <div class="logo">
        <a href="/index.php">
            EcoCommerce
        </a>
    </div>

    <div class="nav-links">

        <a href="/index.php">Home</a>

        <a href="/products.php">Products</a>

        <a href="/cart.php">
            🛒 Cart (<?= (int)$cartCount ?>)
        </a>

        <?php if ($loggedIn): ?>

            <a href="/profile.php">
                👤 <?= htmlspecialchars(
                    $_SESSION["user_name"] ?? "Profile"
                ) ?>
            </a>

            <?php if (
                ($_SESSION["user_role"] ?? "") === "admin"
            ): ?>

                <a href="/admin/index.php">
                    Admin
                </a>

            <?php endif; ?>

            <form action="/actions/logout.php"
                  method="POST"
                  class="nav-logout"><?= csrf() ?>

                <button type="submit">
                    Logout
                </button>

            </form>

        <?php else: ?>

            <a href="/login.php">Login</a>

            <a href="/register.php">Register</a>

        <?php endif; ?>

    </div>

</nav>