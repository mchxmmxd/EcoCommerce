<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/database.php";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../checkout.php");
    exit;
}

function checkoutError($message) {
    $_SESSION["checkout_error"] = $message;
    header("Location: ../checkout.php");
    exit;
}

// Verify CSRF token
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"] ?? "", $token)
) {
    http_response_code(403);
    exit("Invalid checkout request.");
}

// Shipping information
$name = trim($_POST["shipping_name"] ?? "");
$phone = trim($_POST["shipping_phone"] ?? "");
$city = trim($_POST["shipping_city"] ?? "");
$address = trim($_POST["shipping_address"] ?? "");

$paymentMethod = $_POST["payment_method"] ?? "";

if (
    $name === "" ||
    $phone === "" ||
    $city === "" ||
    $address === ""
) {
    checkoutError("Please complete all shipping fields.");
}

if (
    strlen($name) > 100 ||
    strlen($phone) > 20 ||
    strlen($city) > 100 ||
    strlen($address) > 2000
) {
    checkoutError("Shipping information is too long.");
}

if (!in_array($paymentMethod, ["cod", "card"], true)) {
    checkoutError("Invalid payment method.");
}

$cart = $_SESSION["cart"] ?? [];

if (empty($cart)) {
    header("Location: ../cart.php");
    exit;
}

try {

    $pdo->beginTransaction();

    $total = 0;
    $orderProducts = [];

    // Sort IDs to lock products consistently
    $productIds = array_keys($cart);
    sort($productIds, SORT_NUMERIC);

    foreach ($productIds as $productId) {

        $productId = filter_var(
            $productId,
            FILTER_VALIDATE_INT
        );

        $quantity = filter_var(
            $cart[$productId],
            FILTER_VALIDATE_INT
        );

        if (!$productId || !$quantity || $quantity < 1) {
            throw new RuntimeException("Invalid cart contents.");
        }

        // Lock product row until transaction finishes
        $stmt = $pdo->prepare(
            "SELECT id, price, stock
             FROM products
             WHERE id = ?
             FOR UPDATE"
        );

        $stmt->execute([$productId]);

        $product = $stmt->fetch();

        if (!$product) {
            throw new RuntimeException("A product no longer exists.");
        }

        if ($quantity > $product["stock"]) {
            throw new RuntimeException(
                "Not enough stock for product #" . $productId
            );
        }

        $total += (float)$product["price"] * $quantity;

        $orderProducts[] = [
            "id" => $productId,
            "quantity" => $quantity,
            "price" => $product["price"]
        ];
    }

    // Create order
    $stmt = $pdo->prepare(
    "INSERT INTO orders (
        user_id,
        total,
        status,
        shipping_name,
        shipping_phone,
        shipping_address,
        shipping_city,
        payment_method,
        payment_status
    ) VALUES (
        ?, ?, 'pending', ?, ?, ?, ?, ?, 'pending'
    )"
);

$stmt->execute([
    $_SESSION["user_id"],
    number_format($total, 2, ".", ""),
    $name,
    $phone,
    $address,
    $city,
    $paymentMethod
]);

    $orderId = (int)$pdo->lastInsertId();

    // Save purchased products
    $insertItem = $pdo->prepare(
        "INSERT INTO order_items (
            order_id,
            product_id,
            quantity,
            price
        ) VALUES (?, ?, ?, ?)"
    );

    $updateStock = $pdo->prepare(
        "UPDATE products
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?"
    );

    foreach ($orderProducts as $item) {

        $insertItem->execute([
            $orderId,
            $item["id"],
            $item["quantity"],
            $item["price"]
        ]);

        $updateStock->execute([
            $item["quantity"],
            $item["id"],
            $item["quantity"]
        ]);

        if ($updateStock->rowCount() !== 1) {
            throw new RuntimeException("Stock update failed.");
        }
    }

    $pdo->commit();

    // Clear cart only after successful transaction
    unset($_SESSION["cart"]);

    // Rotate token after successful checkout
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

    if ($paymentMethod === "card") {
    header(
        "Location: ../payments/demo-card.php?order_id=" . $orderId
    );
} else {
    header(
        "Location: ../order-success.php?id=" . $orderId
    );
}

exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    $_SESSION["checkout_error"] =
        "Unable to place your order. Please check your cart and try again.";

    header("Location: ../checkout.php");
    exit;
}