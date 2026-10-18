<?php
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/config/database.php";

requireLogin();

$cart = $_SESSION["cart"] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$ids = array_keys($cart);

$placeholders = implode(
    ",",
    array_fill(0, count($ids), "?")
);

$stmt = $pdo->prepare(
    "SELECT id, name, price, stock
     FROM products
     WHERE id IN ($placeholders)"
);

$stmt->execute($ids);

$products = $stmt->fetchAll();

$total = 0;

foreach ($products as $product) {
    $quantity = (int)($cart[$product["id"]] ?? 0);
    $total += $product["price"] * $quantity;
}

$error = $_SESSION["checkout_error"] ?? "";
unset($_SESSION["checkout_error"]);

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Checkout | EcoCommerce</title>

    <link rel="stylesheet"
          href="assets/css/style.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<main class="checkout-container">

    <h1>Secure Checkout</h1>

    <?php if ($error): ?>
        <p class="checkout-error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <div class="checkout-grid">

        <section class="checkout-card">

            <h2>📦 Shipping Information</h2>

            <form action="actions/place-order.php"
                  method="POST">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars(
                           $_SESSION["csrf_token"]
                       ) ?>">

                <label for="shipping_name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="shipping_name"
                    name="shipping_name"
                    maxlength="100"
                    required
                >

                <label for="shipping_phone">
                    Phone Number
                </label>

                <input
                    type="tel"
                    id="shipping_phone"
                    name="shipping_phone"
                    maxlength="20"
                    required
                >

                <label for="shipping_city">
                    City
                </label>

                <input
                    type="text"
                    id="shipping_city"
                    name="shipping_city"
                    maxlength="100"
                    required
                >

                <label for="shipping_address">
                    Full Address
                </label>

                <textarea
                    id="shipping_address"
                    name="shipping_address"
                    rows="4"
                    required
                ></textarea>

                <h2>💳 Payment Method</h2>

                <div class="payment-options">

    <label>
        <input
            type="radio"
            name="payment_method"
            value="cod"
            checked
        >
        💵 Cash on Delivery
    </label>

    <label>
        <input
            type="radio"
            name="payment_method"
            value="card"
        >
        💳 Credit/Debit Card (Demo)
    </label>

</div>
                <button type="submit"
                        class="place-order-btn">
                    Place Order
                </button>

            </form>

        </section>

        <aside class="checkout-card">

            <h2>🛒 Order Summary</h2>

            <?php foreach ($products as $product): ?>

                <?php
                $quantity = (int)$cart[$product["id"]];
                $subtotal = $product["price"] * $quantity;
                ?>

                <div class="checkout-item">

                    <span>
                        <?= htmlspecialchars($product["name"]) ?>
                        × <?= $quantity ?>
                    </span>

                    <strong>
                        ৳<?= number_format($subtotal, 2) ?>
                    </strong>

                </div>

            <?php endforeach; ?>

            <hr>

            <div class="checkout-total">
                <span>Total</span>

                <strong>
                    ৳<?= number_format($total, 2) ?>
                </strong>
            </div>

        </aside>

    </div>

</main>

</body>
</html>