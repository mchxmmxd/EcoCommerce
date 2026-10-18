<?php require_once __DIR__.'/includes/auth.php'; ?>
<?php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/config/database.php";

$cart = $_SESSION["cart"] ?? [];
$products = [];
$total = 0;

if (!empty($cart)) {

    $ids = array_keys($cart);

    $placeholders = implode(
        ",",
        array_fill(0, count($ids), "?")
    );

    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE id IN ($placeholders)"
    );

    $stmt->execute($ids);

    $products = $stmt->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart | EcoCommerce</title>

    <link rel="stylesheet"
          href="assets/css/style.css">
</head>

<body>

<?php include "includes/navbar.php"; ?>

<section class="cart-section">

    <h1>🛒 Your Shopping Cart</h1>

    <?php if (empty($products)): ?>

        <p>Your cart is empty.</p>

        <a href="index.php" class="product-btn">
            Continue Shopping
        </a>

    <?php else: ?>

        <div class="cart-items">

            <?php foreach ($products as $product): ?>

                <?php

                $id = (int)$product["id"];
                $quantity = (int)$cart[$id];
                $subtotal = $product["price"] * $quantity;

                $total += $subtotal;

                ?>

                <div class="cart-item">

                    <div>
                        <h3>
                            <?= htmlspecialchars($product["name"]) ?>
                        </h3>

                        <p>
                            ৳<?= number_format($product["price"], 2) ?>
                        </p>
                    </div>

                    <form action="actions/update-cart.php"
                          method="POST"><?= csrf() ?>

                        <input type="hidden"
                               name="product_id"
                               value="<?= $id ?>">

                        <input type="number"
                               name="quantity"
                               value="<?= $quantity ?>"
                               min="1"
                               max="<?= (int)$product["stock"] ?>"
                               required>

                        <button type="submit">
                            Update
                        </button>

                    </form>

                    <p>
                        ৳<?= number_format($subtotal, 2) ?>
                    </p>

                    <form action="actions/remove-from-cart.php"
                          method="POST"><?= csrf() ?>

                        <input type="hidden"
                               name="product_id"
                               value="<?= $id ?>">

                        <button type="submit"
                                class="remove-btn">
                            Remove
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="cart-summary">

            <h2>
                Total: ৳<?= number_format($total, 2) ?>
            </h2>

            <a href="checkout.php" class="product-btn">
                Proceed to Checkout
            </a>

        </div>

    <?php endif; ?>

</section>

</body>
</html>