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
    "SELECT id, total, payment_method, payment_status, status
     FROM orders
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);

$stmt->execute([$orderId, $_SESSION["user_id"]]);
$order = $stmt->fetch();

if (!$order || $order["payment_method"] !== "card") {
    http_response_code(404);
    exit("Demo card order not found.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$isPending = (
    $order["payment_status"] === "pending" &&
    $order["status"] === "pending"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Demo Card Payment | EcoCommerce</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include __DIR__ . "/../includes/navbar.php"; ?>

<main class="demo-payment-container">
    <div class="demo-payment-card">

        <h1>💳 Demo Card Payment</h1>

        <p class="demo-notice">
            TEST MODE — No real money will be charged.
            Do not enter real card information.
        </p>

        <h2>Order #<?= (int)$order["id"] ?></h2>

        <p class="demo-amount">
            ৳<?= number_format((float)$order["total"], 2) ?>
        </p>

        <p>
            Payment status:
            <strong>
                <?= htmlspecialchars($order["payment_status"]) ?>
            </strong>
        </p>

        <?php if ($isPending): ?>

            <form action="demo-process.php" method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION["csrf_token"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                >

                <input
                    type="hidden"
                    name="order_id"
                    value="<?= (int)$order["id"] ?>"
                >

                <button
                    class="demo-success-btn"
                    type="submit"
                    name="result"
                    value="success"
                >
                    ✅ Simulate Successful Payment
                </button>

                <button
                    class="demo-fail-btn"
                    type="submit"
                    name="result"
                    value="failed"
                >
                    ❌ Simulate Failed Payment
                </button>

                <button
                    class="demo-cancel-btn"
                    type="submit"
                    name="result"
                    value="cancelled"
                >
                    Cancel Payment
                </button>

            </form>

        <?php else: ?>

            <p>This demo payment is no longer pending.</p>

            <a href="../my-orders.php">
                View My Orders
            </a>

        <?php endif; ?>

    </div>
</main>

</body>
</html>