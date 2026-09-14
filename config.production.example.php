<?php
/**
 * MenuCraft — Configuration PRODUCTION (o2switch)
 * 
 * 1. Renommer ce fichier en config.php sur le serveur
 * 2. Remplir les valeurs avec celles de votre hébergement o2switch
 * 3. Les identifiants BDD se trouvent dans cPanel > Bases de données MySQL
 */

// ─── Connexion BDD (cPanel > Bases de données MySQL) ───
$host = 'localhost';                    // Toujours localhost sur o2switch
$dbname = 'CPANEL_USER_menucraft';      // Format : utilisateur_nombase
$user   = 'CPANEL_USER_menucraft';      // Format : utilisateur_nomuser
$pass   = 'VOTRE_MOT_DE_PASSE_BDD';    // Mot de passe défini dans cPanel

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

// ─── URLs (adapter selon votre domaine) ───
// Exemples :
//   - Domaine principal  : 'https://votredomaine.com/'
//   - Sous-domaine       : 'https://menucraft.votredomaine.com/'
define('SITE_URL', 'https://VOTRE_DOMAINE.com/');
define('BASE_PATH', __DIR__);

// ─── Stripe (optionnel, pour les paiements) ───
define('STRIPE_SECRET_KEY', 'sk_live_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// ─── Mode Beta ───
define('BETA_MODE', true);
define('BETA_EXPIRES', '2026-09-30');
