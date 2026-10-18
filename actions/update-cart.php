<?php

require_once __DIR__.'/../includes/auth.php';

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../cart.php");
    exit;
}
checkCsrf();


$productId = filter_input(
    INPUT_POST,
    "product_id",
    FILTER_VALIDATE_INT
);

$quantity = filter_input(
    INPUT_POST,
    "quantity",
    FILTER_VALIDATE_INT
);

if (!$productId || !$quantity || $quantity < 1) {
    http_response_code(400);
    exit("Invalid quantity.");
}

if (!isset($_SESSION["cart"][$productId])) {
    header("Location: ../cart.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT stock FROM products WHERE id = ?"
);

$stmt->execute([$productId]);

$product = $stmt->fetch();

if (!$product || $quantity > $product["stock"]) {
    http_response_code(400);
    exit("Requested quantity is unavailable.");
}

$_SESSION["cart"][$productId] = $quantity;

header("Location: ../cart.php");
exit;