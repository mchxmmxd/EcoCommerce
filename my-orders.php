<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/config/database.php";

requireLogin();

$stmt = $pdo->prepare(
    "SELECT id, total, status,
            payment_method, payment_status,
            created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC"
);

$stmt->execute([$_SESSION["user_id"]]);

$orders = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Orders | EcoCommerce</title>

    <link rel="stylesheet"
          href="assets/css/style.css">
</head>

<body>

<?php include "includes/navbar.php"; ?>

<main class="orders-container">

    <h1>📦 My Orders</h1>

    <?php if (empty($orders)): ?>

        <p>You haven't placed any orders yet.</p>

        <a href="index.php" class="product-btn">
            Start Shopping
        </a>

    <?php else: ?>

        <div class="orders-list">

            <?php foreach ($orders as $order): ?>

                <div class="order-card">

                    <h3>
                        Order #<?= (int)$order["id"] ?>
                    </h3>

                    <p>
                        <strong>Date:</strong>
                        <?= htmlspecialchars($order["created_at"]) ?>
                    </p>

                    <p>
                        <strong>Total:</strong>
                        ৳<?= number_format($order["total"], 2) ?>
                    </p>

                    <p>
                        <strong>Payment:</strong>
                        <?= htmlspecialchars(
                            strtoupper($order["payment_method"])
                        ) ?>
                    </p>

                    <p>
                        <strong>Payment Status:</strong>
                        <?= htmlspecialchars(
                            ucfirst($order["payment_status"])
                        ) ?>
                    </p>

                    <p>
                        <strong>Order Status:</strong>
                        <?= htmlspecialchars(
                            ucfirst($order["status"])
                        ) ?>
                    </p>

                    <a
                        href="order-success.php?id=<?= (int)$order["id"] ?>"
                        class="product-btn"
                    >
                        View Order
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

</body>
</html>