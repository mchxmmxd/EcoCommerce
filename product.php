<?php require_once __DIR__.'/includes/auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/config/database.php";

// Get product ID from URL
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

// Reject invalid IDs
if (!$id || $id < 1) {
    http_response_code(404);
    exit("Invalid product.");
}

// Fetch product from database
$stmt = $pdo->prepare(
    "SELECT * FROM products WHERE id = ?"
);

$stmt->execute([$id]);

$product = $stmt->fetch();

// Product doesn't exist
if (!$product) {
    http_response_code(404);
    exit("Product not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($product["name"]) ?>
        | EcoCommerce
    </title>

    <link rel="stylesheet"
          href="assets/css/style.css">
</head>

<body>

<?php include "includes/navbar.php"; ?>

<section class="product-details">

    <div class="product-details-image">
        📦
    </div>

    <div class="product-details-info">

        <h1>
            <?= htmlspecialchars($product["name"]) ?>
        </h1>

        <p class="description">
            <?= nl2br(htmlspecialchars(
                $product["description"] ?? ""
            )) ?>
        </p>

        <h2 class="price">
            ৳<?= number_format($product["price"], 2) ?>
        </h2>

        <p>
            Stock:
            <?= (int)$product["stock"] ?>
            available
        </p>

        <?php if ($product["stock"] > 0): ?>

            <form action="actions/add-to-cart.php"
                  method="POST"><?= csrf() ?>

                <input type="hidden"
                       name="product_id"
                       value="<?= (int)$product["id"] ?>">

                <label for="quantity">
                    Quantity:
                </label>

                <input type="number"
                       id="quantity"
                       name="quantity"
                       value="1"
                       min="1"
                       max="<?= (int)$product["stock"] ?>"
                       required>

                <button type="submit"
                        class="product-btn">

                    🛒 Add to Cart

                </button>

            </form>

        <?php else: ?>

            <p class="out-of-stock">
                Out of Stock
            </p>

        <?php endif; ?>

    </div>

</section>

</body>
</html>