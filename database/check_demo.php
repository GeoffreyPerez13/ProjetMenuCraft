<?php
require __DIR__ . '/../config.php';

echo "=== Restaurants ===\n";
$stmt = $pdo->query('SELECT id, name, slug FROM restaurants');
foreach ($stmt as $r) echo $r->id . ' | ' . $r->slug . ' | ' . $r->name . "\n";

echo "\n=== Admins ===\n";
$stmt = $pdo->query('SELECT id, username, role, restaurant_id FROM admins');
foreach ($stmt as $r) echo $r->id . ' | ' . $r->username . ' | ' . $r->role . ' | rest_id=' . $r->restaurant_id . "\n";

echo "\n=== Categories (admin_id check) ===\n";
$stmt = $pdo->query('SELECT admin_id, COUNT(*) as cnt FROM categories GROUP BY admin_id');
foreach ($stmt as $r) echo 'admin_id=' . $r->admin_id . ' => ' . $r->cnt . " categories\n";

