<?php

require_once __DIR__.'/../includes/auth.php';

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

if ($productId && isset($_SESSION["cart"][$productId])) {
    unset($_SESSION["cart"][$productId]);
}

header("Location: ../cart.php");
exit;