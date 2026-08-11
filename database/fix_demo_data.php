<?php
require __DIR__ . '/../config.php';

// Get demo admin id
$stmt = $pdo->prepare("SELECT a.id FROM admins a JOIN restaurants r ON r.id = a.restaurant_id WHERE r.slug = 'demo-restaurant'");
$stmt->execute();
$admin = $stmt->fetch();
if (!$admin) {
    echo "Demo restaurant not found!\n";
    exit(1);
}
$adminId = $admin->id;
echo "Demo admin_id = $adminId\n";

// 1. Fix banner: delete the empty-filename row (fallback shows clean banner with name)
$pdo->prepare("DELETE FROM banners WHERE admin_id = :aid AND filename = ''")->execute([':aid' => $adminId]);
echo "Removed empty banner (fallback will show restaurant name)\n";

// 2. Create a simple SVG logo
$svgLogo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
  <circle cx="100" cy="100" r="90" fill="#b45309"/>
  <text x="100" y="75" text-anchor="middle" font-family="Georgia, serif" font-size="22" fill="white" font-weight="bold">Le Petit</text>
  <text x="100" y="105" text-anchor="middle" font-family="Georgia, serif" font-size="22" fill="white" font-weight="bold">Bistro</text>
  <text x="100" y="135" text-anchor="middle" font-family="Georgia, serif" font-size="16" fill="#fef7ed">Parisien</text>
  <path d="M60 155 Q100 170 140 155" stroke="#fef7ed" stroke-width="1.5" fill="none"/>
</svg>';
$logoFilename = 'demo_logo.svg';
file_put_contents(__DIR__ . '/../public/uploads/logos/' . $logoFilename, $svgLogo);
$pdo->prepare("INSERT INTO logos (admin_id, filename) VALUES (:aid, :fn) ON DUPLICATE KEY UPDATE filename = :fn2")
    ->execute([':aid' => $adminId, ':fn' => $logoFilename, ':fn2' => $logoFilename]);
echo "Logo created: uploads/logos/$logoFilename\n";

// 3. Create a banner image (SVG gradient)
$svgBanner = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 500" width="1200" height="500">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#1a0f00"/>
      <stop offset="100%" style="stop-color:#3d2200"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="500" fill="url(#bg)"/>
  <circle cx="200" cy="400" r="150" fill="#b45309" opacity="0.15"/>
  <circle cx="1000" cy="100" r="200" fill="#b45309" opacity="0.1"/>
  <rect x="50" y="200" width="3" height="100" fill="#b45309" opacity="0.3"/>
  <rect x="1147" y="150" width="3" height="100" fill="#b45309" opacity="0.3"/>
</svg>';
$bannerFilename = 'demo_banner.svg';
file_put_contents(__DIR__ . '/../public/uploads/banners/' . $bannerFilename, $svgBanner);
$pdo->prepare("INSERT INTO banners (admin_id, filename, text) VALUES (:aid, :fn, :txt) ON DUPLICATE KEY UPDATE filename = :fn2, text = :txt2")
    ->execute([':aid' => $adminId, ':fn' => $bannerFilename, ':txt' => 'Cuisine française authentique au cœur de Paris', ':fn2' => $bannerFilename, ':txt2' => 'Cuisine française authentique au cœur de Paris']);
echo "Banner created: uploads/banners/$bannerFilename\n";

// 4. Fix daily menus — use correct {label, value} JSON format with richer content
$menuDejeuner = json_encode([
    ['label' => 'Entrée au choix', 'value' => 'Velouté de butternut aux noisettes OU Salade de chèvre chaud au miel'],
    ['label' => 'Plat au choix', 'value' => 'Pavé de saumon grillé, risotto aux asperges OU Magret de canard, purée de patate douce'],
    ['label' => 'Fromage ou Dessert', 'value' => 'Assiette de fromages affinés OU Fondant au chocolat, glace vanille'],
    ['label' => 'Boisson incluse', 'value' => 'Verre de vin (rouge, blanc ou rosé) OU Eau minérale'],
], JSON_UNESCAPED_UNICODE);

$formuleExpress = json_encode([
    ['label' => 'Plat du jour', 'value' => 'Burger Le Bistro, frites maison OU Risotto crémeux aux cèpes et parmesan'],
    ['label' => 'Dessert au choix', 'value' => 'Tiramisu maison OU Tarte tatin, crème fraîche OU Café gourmand'],
], JSON_UNESCAPED_UNICODE);

$pdo->prepare("UPDATE daily_menus SET items = :items WHERE admin_id = :aid AND title = 'Menu Déjeuner'")->execute([':items' => $menuDejeuner, ':aid' => $adminId]);
$pdo->prepare("UPDATE daily_menus SET items = :items WHERE admin_id = :aid AND title = 'Formule Express'")->execute([':items' => $formuleExpress, ':aid' => $adminId]);

// Add a third menu: Menu Dégustation
$menuDegustation = json_encode([
    ['label' => 'Amuse-bouche', 'value' => 'Mise en bouche du chef selon l\'arrivage'],
    ['label' => 'Entrée', 'value' => 'Tartare de saumon, avocat et agrumes OU Burrata crémeuse, tomates confites au basilic'],
    ['label' => 'Plat', 'value' => 'Filet de bœuf rossini, jus truffé, pommes dauphines OU Souris d\'agneau confite 7h, polenta et herbes'],
    ['label' => 'Pré-dessert', 'value' => 'Sorbet citron-basilic'],
    ['label' => 'Dessert', 'value' => 'Sphère chocolat, cœur fruits rouges et éclats de praliné'],
], JSON_UNESCAPED_UNICODE);

$pdo->prepare("INSERT INTO daily_menus (admin_id, title, description, price, items, display_order, is_active) VALUES (:aid, 'Menu Dégustation', 'L''expérience complète en 5 temps', 42.00, :items, 3, 1)")
    ->execute([':aid' => $adminId, ':items' => $menuDegustation]);

echo "Daily menus updated with rich content + Menu Dégustation added\n";
echo "\nDone! Refresh ?page=display&slug=demo-restaurant\n";
