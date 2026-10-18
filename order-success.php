<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/config/database.php";

requireLogin();

$orderId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$orderId) {
    http_response_code(404);
    exit("Order not found.");
}

$stmt = $pdo->prepare(
    "SELECT *
     FROM orders
     WHERE id = ? AND user_id = ?"
);

$stmt->execute([
    $orderId,
    $_SESSION["user_id"]
]);

$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    exit("Order not found.");
}
if (
    $order["payment_method"] === "card" &&
    $order["payment_status"] !== "paid"
) {
    header(
        "Location: payments/" . ($order["status"] === "cancelled" ? "demo-result.php" : "demo-card.php") . "?order_id=" .
        $orderId
    );
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Confirmed | EcoCommerce</title>

    <link rel="stylesheet"
          href="assets/css/style.css">
</head>

<body>

<?php include "includes/navbar.php"; ?>

<section class="success-container">

    <div class="success-card">

        <div class="success-icon">✅</div>

        <h1><?= $order["status"] === "cancelled" ? "Order Cancelled" : "Order Placed Successfully!" ?></h1>

        <p>
            Thank you for shopping with EcoCommerce.
        </p>

        <div class="order-details">

            <p>
                <strong>Order ID:</strong>
                #<?= (int)$order["id"] ?>
            </p>

            <p>
                <strong>Total:</strong>
                ৳<?= number_format($order["total"], 2) ?>
            </p>

            <p>
    <strong>Payment Method:</strong>

    <?php if ($order["payment_method"] === "card"): ?>
        Credit/Debit Card (Demo)
    <?php else: ?>
        Cash on Delivery
    <?php endif; ?>
</p>

            <p>
                <strong>Payment Status:</strong>
                <?= htmlspecialchars($order["payment_status"]) ?>
            </p>

            <p>
                <strong>Order Status:</strong>
                <?= htmlspecialchars($order["status"]) ?>
            </p>

        </div>

        <a href="my-orders.php" class="product-btn">
            View My Orders
        </a>

        <a href="index.php" class="continue-link">
            Continue Shopping
        </a>

    </div>

</section>

</body>
</html>