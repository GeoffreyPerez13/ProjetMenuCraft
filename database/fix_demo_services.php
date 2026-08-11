<?php
require __DIR__ . '/../config.php';

$stmt = $pdo->prepare("SELECT a.id FROM admins a JOIN restaurants r ON r.id = a.restaurant_id WHERE r.slug = 'demo-restaurant'");
$stmt->execute();
$admin = $stmt->fetch();
if (!$admin) { echo "Demo not found!\n"; exit(1); }
$adminId = $admin->id;

$options = [
    // Services
    'service_sur_place' => '1',
    'service_a_emporter' => '1',
    'service_livraison_ubereats' => '1',
    'service_wifi' => '1',
    'service_climatisation' => '1',
    'service_pmr' => '1',
    'service_animaux' => '1',
    // Paiements
    'payment_visa' => '1',
    'payment_mastercard' => '1',
    'payment_cb' => '1',
    'payment_especes' => '1',
    'payment_cheques' => '1',
    'payment_tickets_restaurant' => '1',
    // Réseaux sociaux (liens fictifs)
    'social_instagram' => 'https://instagram.com/petitbistroparisien',
    'social_facebook' => 'https://facebook.com/petitbistroparisien',
    'social_x' => 'https://x.com/petitbistro',
    'social_tiktok' => 'https://tiktok.com/@petitbistro',
];

$stmt = $pdo->prepare("INSERT INTO admin_options (admin_id, option_name, option_value) VALUES (:aid, :k, :v) ON DUPLICATE KEY UPDATE option_value = :v2");
foreach ($options as $k => $v) {
    $stmt->execute([':aid' => $adminId, ':k' => $k, ':v' => $v, ':v2' => $v]);
}

echo "All services, payments, and socials enabled for demo restaurant (admin_id=$adminId)\n";
