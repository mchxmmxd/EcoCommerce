<?php

require_once __DIR__.'/../includes/auth.php';

require_once __DIR__ . "/../config/database.php";

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}
checkCsrf();


// Validate inputs
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
    exit("Invalid product or quantity.");
}

// Find product
$stmt = $pdo->prepare(
    "SELECT id, stock FROM products WHERE id = ?"
);

$stmt->execute([$productId]);

$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    exit("Product not found.");
}

// Initialize cart
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// Current quantity in cart
$currentQuantity = $_SESSION["cart"][$productId] ?? 0;

$newQuantity = $currentQuantity + $quantity;

// Check stock
if ($newQuantity > $product["stock"]) {
    http_response_code(400);
    exit("Not enough stock available.");
}

// Add product
$_SESSION["cart"][$productId] = $newQuantity;

// Redirect to cart
header("Location: ../cart.php");
exit;