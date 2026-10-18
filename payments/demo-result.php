<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/database.php";

requireLogin();

$orderId = filter_input(
    INPUT_GET,
    "order_id",
    FILTER_VALIDATE_INT
);

if (!$orderId) {
    http_response_code(404);
    exit("Order not found.");
}

$stmt = $pdo->prepare(
    "SELECT id, total, payment_status, status
     FROM orders
     WHERE id = ? AND user_id = ?
     LIMIT 1"
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

if ($order["payment_status"] === "paid") {
    header(
        "Location: ../order-success.php?id=" . $orderId
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

    <title>Payment Result | EcoCommerce</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">
</head>

<body>

<?php include __DIR__ . "/../includes/navbar.php"; ?>

<main class="demo-payment-container">

    <div class="demo-payment-card">

        <h1>Payment Not Completed</h1>

        <p>
            Your demo payment was unsuccessful
            or was cancelled.
        </p>

        <p>
            Order #<?= (int)$order["id"] ?>
        </p>

        <p>
            Status:
            <?= htmlspecialchars($order["status"]) ?>
        </p>

        <p>
            No real money was charged.
        </p>

        <a href="../my-orders.php">
            View My Orders
        </a>

    </div>

</main>

</body>
</html>