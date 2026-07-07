<?php
/**
 * MenuCraft — Configuration
 * Copier ce fichier en config.php et adapter les valeurs.
 */

// Connexion BDD
$host = 'localhost';
$dbname = 'menucraft';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

// URLs et chemins
define('SITE_URL', 'http://localhost/ProjetMenuCraft/public/');
define('BASE_PATH', __DIR__);

// Stripe
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// Mode Beta (true = toutes les features premium gratuites)
define('BETA_MODE', true);
define('BETA_EXPIRES', '2026-09-30');
