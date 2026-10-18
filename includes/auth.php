<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION["user_id"]);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }
}

function isAdmin() {
    return isLoggedIn() &&
        ($_SESSION["user_role"] ?? "") === "admin";
}
require_once __DIR__.'/functions.php';
function requireAdmin() {
    requireLogin();
    global $pdo;
    require_once __DIR__.'/../config/database.php';
    $s = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $s->execute([$_SESSION['user_id']]);
    $role = $s->fetchColumn();
    $_SESSION['user_role'] = $role;
    if ($role !== 'admin') { http_response_code(403); exit('403 Forbidden — Administrator access required.'); }
}
