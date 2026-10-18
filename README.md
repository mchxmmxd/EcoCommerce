# EcoCommerce learning demo

PHP 8 + PDO MySQL + MySQL. No real payments are processed.

## Run locally
Start MySQL, then from this folder run `php -S localhost:8000` and open http://localhost:8000. The app expects the project at the server root.

Database settings are in config/database.php (local root account, empty password by default). For a **fresh database only**, import database/schema.sql then database/seed.sql. Do not reimport the schema over your existing database: its final ALTER TABLE is not repeatable.

Register an account through the storefront. In the local MySQL terminal, inspect `SELECT id,name,email,role FROM users;` and promote your chosen account with `UPDATE users SET role='admin' WHERE id=YOUR_ID;`. Never expose role promotion on a public page. Open /admin/index.php.

## Features
Catalog search/category filters, product details, cart, registration/login, profile/order history, checkout with COD or simulated card success/failure/cancellation. Admin dashboard, product/category create/edit/delete, customer overview, order details and controlled status updates. Ordered products cannot be deleted; set stock to zero. Categories can be deleted, leaving products uncategorized.

Stock is reserved at checkout. Failed/cancelled card payments and admin cancellations restore it once. Abandoned pending payments reserve stock until an admin cancels the order. Card orders must be paid before fulfillment. COD is marked paid when delivered. Refund labels are demo bookkeeping only.

Customer management is a read-only account/purchase overview; account editing/deletion and online role changes are intentionally unavailable. Product images use placeholder artwork.

## GitHub copy

This repository contains application source and the fresh-install schema/seed only. Local users, passwords, and orders are not included. Create your own admin account using the setup steps above. Database settings may also be supplied with DB_HOST, DB_NAME, DB_USER, and DB_PASSWORD environment variables.
