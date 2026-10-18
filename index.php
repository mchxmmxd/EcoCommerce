<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/config/database.php";

$stmt = $pdo->prepare(
    "SELECT * FROM products ORDER BY id DESC"
);

$stmt->execute();

$products = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>EcoCommerce | Online Store</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

<?php include "includes/navbar.php"; ?>

<section class="hero">

    <h1>Welcome to EcoCommerce</h1>

    <p>
        Discover amazing products at affordable prices.
    </p>

    <a href="#products" class="hero-btn">
        Shop Now
    </a>

</section>

<section class="products-section" id="products">

    <h2>Featured Products</h2>

    <div class="product-grid">

        <?php foreach ($products as $product): ?>

            <div class="product-card">

                <div class="product-image">

                    <span>📦</span>

                </div>

                <h3>
                    <?= htmlspecialchars($product["name"]) ?>
                </h3>

                <p class="description">
                    <?= htmlspecialchars(
                        $product["description"] ?? ""
                    ) ?>
                </p>

                <p class="price">
                    ৳<?= number_format($product["price"], 2) ?>
                </p>

                <a
                    href="product.php?id=<?= (int)$product["id"] ?>"
                    class="product-btn"
                >
                    View Product
                </a>

            </div>

        <?php endforeach; ?>

    </div>

</section>

</body>
</html>