<?php
require_once __DIR__.'/../includes/auth.php';

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}
checkCsrf();


$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$stmt = $pdo->prepare(
    "SELECT id, name, password, role
     FROM users
     WHERE email = ?
     LIMIT 1"
);

$stmt->execute([$email]);

$user = $stmt->fetch();

if (
    !$user ||
    !password_verify($password, $user["password"])
) {
    $_SESSION["auth_error"] = "Invalid email or password.";

    header("Location: ../login.php");
    exit;
}

session_regenerate_id(true);

$_SESSION["user_id"] = (int)$user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_role"] = $user["role"];

header("Location: ../profile.php");
exit;