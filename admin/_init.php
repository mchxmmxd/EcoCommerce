<?php
require_once __DIR__.'/../includes/auth.php';
requireAdmin();
require_once __DIR__.'/../config/database.php';
function adminHeader($title) { ?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> | EcoCommerce</title><link rel="stylesheet" href="/assets/css/admin.css"></head><body><aside><h2>🌱 EcoCommerce</h2><p>STORE ADMINISTRATION</p><nav><a href="/admin/index.php">Dashboard</a><a href="/admin/products/index.php">Products</a><a href="/admin/categories/index.php">Categories</a><a href="/admin/orders/index.php">Orders</a><a href="/admin/users/index.php">Customers</a><a href="/index.php">Visit storefront ↗</a></nav></aside><main><h1><?= e($title) ?></h1><?php if (!empty($_SESSION['admin_message'])): ?><p class="notice"><?= e($_SESSION['admin_message']) ?></p><?php unset($_SESSION['admin_message']); endif; }
function adminFooter() { echo '</main></body></html>'; }
function message($m,$url) { $_SESSION['admin_message']=$m; header('Location: '.$url); exit; }
