<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/database.php";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Verify CSRF token
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"] ?? "", $token)
) {
    http_response_code(403);
    exit("Invalid request.");
}

$orderId = filter_var(
    $_POST["order_id"] ?? null,
    FILTER_VALIDATE_INT
);

$result = $_POST["result"] ?? "";

if (
    !$orderId ||
    !in_array(
        $result,
        ["success", "failed", "cancelled"],
        true
    )
) {
    http_response_code(400);
    exit("Invalid payment request.");
}

try {

    $pdo->beginTransaction();

    // Lock order to prevent simultaneous payment changes
    $stmt = $pdo->prepare(
        "SELECT id, user_id, payment_method,
                payment_status, status
         FROM orders
         WHERE id = ? AND user_id = ?
         FOR UPDATE"
    );

    $stmt->execute([
        $orderId,
        $_SESSION["user_id"]
    ]);

    $order = $stmt->fetch();

    if (!$order || $order["payment_method"] !== "card") {
        throw new RuntimeException("Order not found.");
    }

    // Prevent duplicate processing
    if (
        $order["payment_status"] !== "pending" ||
        $order["status"] !== "pending"
    ) {
        throw new RuntimeException(
            "This payment has already been processed."
        );
    }

    if ($result === "success") {

        // Demo only: no actual card payment occurred.
        $stmt = $pdo->prepare(
            "UPDATE orders
             SET payment_status = 'paid'
             WHERE id = ?"
        );

        $stmt->execute([$orderId]);

    } else {

        // Restore stock for failed/cancelled payments
        $stmt = $pdo->prepare(
            "SELECT product_id, quantity
             FROM order_items
             WHERE order_id = ?"
        );

        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();

        $restoreStock = $pdo->prepare(
            "UPDATE products
             SET stock = stock + ?
             WHERE id = ?"
        );

        foreach ($items as $item) {
            $restoreStock->execute([
                $item["quantity"],
                $item["product_id"]
            ]);

            if ($restoreStock->rowCount() !== 1) {
                throw new RuntimeException(
                    "Could not restore product stock."
                );
            }
        }

        $paymentStatus = (
            $result === "failed"
            ? "failed"
            : "failed"
        );

        $stmt = $pdo->prepare(
            "UPDATE orders
             SET payment_status = ?,
                 status = 'cancelled'
             WHERE id = ?"
        );

        $stmt->execute([
            $paymentStatus,
            $orderId
        ]);
    }

    $pdo->commit();

    if ($result === "success") {

        header(
            "Location: ../order-success.php?id=" . $orderId
        );

    } else {

        header(
            "Location: demo-result.php?order_id=" . $orderId
        );
    }

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(400);

    exit(
        "Unable to process demo payment. " .
        "Please return to your order history."
    );
}