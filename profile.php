<?php require_once __DIR__.'/includes/auth.php'; ?>
<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/config/database.php";

requireLogin();

$stmt = $pdo->prepare(
    "SELECT id, name, email, role, created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION["user_id"]]);

$user = $stmt->fetch();

if (!$user) {
    header("Location: actions/logout.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Profile | EcoCommerce</title>

    <link rel="stylesheet"
          href="assets/css/style.css">
</head>

<body>

<?php include "includes/navbar.php"; ?>

<section class="profile-section">

    <div class="profile-card">

        <div class="profile-avatar">👤</div>

        <h1>
            <?= htmlspecialchars($user["name"]) ?>
        </h1>

        <p class="profile-subtitle">
            Welcome to your EcoCommerce account!
        </p>

        <div class="profile-info">

            <p>
                <strong>Email:</strong>
                <?= htmlspecialchars($user["email"]) ?>
            </p>

            <p>
                <strong>Account Type:</strong>
                <?= htmlspecialchars($user["role"]) ?>
            </p>

            <p>
                <strong>Joined:</strong>
                <?= htmlspecialchars($user["created_at"]) ?>
            </p>

        </div>

        <a href="my-orders.php" class="product-btn">
            📦 My Orders
        </a>

        <form action="actions/logout.php"
              method="POST"><?= csrf() ?>

            <button type="submit" class="logout-btn">
                Logout
            </button>

        </form>

    </div>

</section>

</body>
</html>