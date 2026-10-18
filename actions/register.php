<?php
require_once __DIR__.'/../includes/auth.php';

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}
checkCsrf();


$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm = $_POST["confirm_password"] ?? "";

function registrationError($message) {
    $_SESSION["auth_error"] = $message;
    header("Location: ../register.php");
    exit;
}

if ($name === "" || strlen($name) > 100) {
    registrationError("Enter a valid name.");
}

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150
) {
    registrationError("Enter a valid email.");
}

if (strlen($password) < 8) {
    registrationError("Password must have at least 8 characters.");
}

if ($password !== $confirm) {
    registrationError("Passwords do not match.");
}

$check = $pdo->prepare(
    "SELECT id FROM users WHERE email = ?"
);

$check->execute([$email]);

if ($check->fetch()) {
    registrationError("Email already registered.");
}

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password, role)
         VALUES (?, ?, ?, 'user')"
    );

    $stmt->execute([
        $name,
        $email,
        $hashedPassword
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    registrationError("Registration failed. Please try again.");
}

session_regenerate_id(true);

$_SESSION["user_id"] = (int)$pdo->lastInsertId();
$_SESSION["user_name"] = $name;
$_SESSION["user_role"] = "user";

header("Location: ../profile.php");
exit;