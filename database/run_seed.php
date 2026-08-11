<?php
require __DIR__ . '/../config.php';

// Vérifier si demo-restaurant existe déjà
$check = $pdo->prepare('SELECT id FROM restaurants WHERE slug = :s');
$check->execute([':s' => 'demo-restaurant']);
if ($check->fetch()) {
    echo "demo-restaurant already exists.\n";
    exit;
}

// 1. Restaurant
$pdo->exec("INSERT INTO restaurants (name, slug) VALUES ('Le Petit Bistro Parisien', 'demo-restaurant')");
$restoId = (int)$pdo->lastInsertId();
echo "Restaurant created (id=$restoId)\n";

// 2. Admin
$hash = password_hash('Admin123!', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, carte_mode, email_verified) VALUES ('demo-admin', 'demo@menucraft.local', :pwd, 'ADMIN', 'Le Petit Bistro Parisien', :rid, 'editable', 1)");
$stmt->execute([':pwd' => $hash, ':rid' => $restoId]);
$adminId = (int)$pdo->lastInsertId();
echo "Admin created (id=$adminId)\n";

// 3. Subscription
$pdo->prepare("INSERT INTO client_subscriptions (admin_id, plan_type, status, price_per_month, started_at) VALUES (:aid, 'premium', 'active', 0, NOW())")->execute([':aid' => $adminId]);

// 4. Premium features
foreach (['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'] as $f) {
    $pdo->prepare("INSERT INTO premium_features (admin_id, feature_name, is_active, activated_at) VALUES (:aid, :fn, 1, NOW())")->execute([':aid' => $adminId, ':fn' => $f]);
}

// 5. Options
$options = ['site_online' => '1', 'site_palette' => 'classic', 'site_layout' => 'standard', 'email_notifications' => '1'];
foreach ($options as $k => $v) {
    $pdo->prepare("INSERT INTO admin_options (admin_id, option_name, option_value) VALUES (:aid, :k, :v)")->execute([':aid' => $adminId, ':k' => $k, ':v' => $v]);
}

// 6. Contact
$pdo->prepare("INSERT INTO contact (admin_id, telephone, email, adresse, horaires) VALUES (:aid, '01 42 36 78 90', 'contact@petitbistro.fr', '24 Rue de Rivoli, 75004 Paris', 'Lundi : Fermé\nMardi - Vendredi : 12h00 - 14h30 / 19h00 - 22h30\nSamedi : 12h00 - 15h00 / 19h00 - 23h00\nDimanche : 12h00 - 15h00')")->execute([':aid' => $adminId]);

// 7. Categories
$cats = [
    ['Entrées', 'Nos entrées fraîches et de saison', 1],
    ['Plats', 'Nos plats signatures préparés avec soin', 2],
    ['Desserts', 'Douceurs sucrées pour terminer en beauté', 3],
    ['Boissons', 'Vins, cocktails et boissons sans alcool', 4],
];
$catIds = [];
foreach ($cats as [$name, $desc, $order]) {
    $pdo->prepare("INSERT INTO categories (admin_id, name, description, display_order) VALUES (:aid, :n, :d, :o)")->execute([':aid' => $adminId, ':n' => $name, ':d' => $desc, ':o' => $order]);
    $catIds[] = (int)$pdo->lastInsertId();
}

// 8. Plats
$plats = [
    // Entrées
    [$catIds[0], 'Velouté de butternut', 'Velouté onctueux à la butternut rôtie, éclats de noisettes et crème fouettée', 9.50, 1],
    [$catIds[0], 'Salade de chèvre chaud', 'Mesclun, toast de chèvre gratiné, miel, noix et vinaigrette balsamique', 11.00, 2],
    [$catIds[0], 'Tartare de saumon', 'Saumon frais mariné citron-aneth, avocat et toast grillé', 13.50, 3],
    [$catIds[0], 'Œuf parfait', 'Œuf cuit 64°, espuma de parmesan, chips de lard et salade frisée', 10.50, 4],
    [$catIds[0], 'Burrata crémeuse', 'Burrata di Puglia, tomates confites, pesto frais et roquette', 12.00, 5],
    // Plats
    [$catIds[1], 'Filet de bœuf rossini', 'Filet de bœuf, escalope de foie gras poêlée, jus truffé et pommes dauphines', 32.00, 1],
    [$catIds[1], 'Pavé de saumon grillé', 'Saumon label rouge, risotto aux asperges vertes et beurre citronné', 24.50, 2],
    [$catIds[1], 'Magret de canard', 'Magret rosé, purée de patate douce, figues rôties et sauce au porto', 26.00, 3],
    [$catIds[1], 'Risotto aux cèpes', 'Risotto crémeux aux cèpes frais, parmesan 24 mois et huile de truffe', 21.00, 4],
    [$catIds[1], 'Burger Le Bistro', 'Bœuf Aubrac, cheddar affiné, bacon fumé, oignons confits, frites maison', 18.50, 5],
    [$catIds[1], 'Souris d\'agneau confite', 'Agneau confit 7h, polenta crémeuse, jus corsé aux herbes de Provence', 27.00, 6],
    // Desserts
    [$catIds[2], 'Fondant au chocolat', 'Cœur coulant au chocolat noir 70%, glace vanille de Madagascar', 10.50, 1],
    [$catIds[2], 'Tarte tatin', 'Pommes caramélisées, pâte feuilletée croustillante et crème fraîche', 9.50, 2],
    [$catIds[2], 'Crème brûlée', 'Crème vanille Bourbon, caramel craquant', 8.50, 3],
    [$catIds[2], 'Tiramisu maison', 'Mascarpone léger, café expresso et cacao amer', 9.00, 4],
    [$catIds[2], 'Assiette de fromages', 'Sélection de 5 fromages affinés, confiture de figues et pain aux noix', 12.00, 5],
    // Boissons
    [$catIds[3], 'Côtes du Rhône rouge', 'Domaine de la Janasse, 2021 — Fruité et épicé', 7.50, 1],
    [$catIds[3], 'Sancerre blanc', 'Domaine Vacheron, 2022 — Frais et minéral', 9.00, 2],
    [$catIds[3], 'Cocktail Spritz', 'Aperol, prosecco, eau gazeuse et tranche d\'orange', 10.00, 3],
    [$catIds[3], 'Limonade maison', 'Citron pressé, menthe fraîche et miel', 5.50, 4],
    [$catIds[3], 'Café gourmand', 'Expresso et trio de mignardises du chef', 8.00, 5],
];
foreach ($plats as [$catId, $name, $desc, $price, $order]) {
    $pdo->prepare("INSERT INTO plats (category_id, name, description, price, display_order, is_active) VALUES (:cid, :n, :d, :p, :o, 1)")->execute([':cid' => $catId, ':n' => $name, ':d' => $desc, ':p' => $price, ':o' => $order]);
}

// 9. Menus du jour
$pdo->prepare("INSERT INTO daily_menus (admin_id, title, description, price, items, display_order, is_active) VALUES (:aid, 'Menu Déjeuner', 'Entrée + Plat + Dessert', 24.90, :items, 1, 1)")->execute([':aid' => $adminId, ':items' => json_encode(['Velouté de butternut ou Salade de chèvre chaud', 'Pavé de saumon grillé ou Risotto aux cèpes', 'Fondant au chocolat ou Crème brûlée'])]);
$pdo->prepare("INSERT INTO daily_menus (admin_id, title, description, price, items, display_order, is_active) VALUES (:aid, 'Formule Express', 'Plat + Dessert', 18.50, :items, 2, 1)")->execute([':aid' => $adminId, ':items' => json_encode(['Burger Le Bistro ou Risotto aux cèpes', 'Tiramisu maison ou Tarte tatin'])]);

// 10. Banner
$pdo->prepare("INSERT INTO banners (admin_id, filename, text) VALUES (:aid, '', 'Bienvenue au Petit Bistro Parisien — Cuisine française authentique')")->execute([':aid' => $adminId]);

echo "Demo restaurant seeded successfully! (admin_id=$adminId, restaurant_id=$restoId)\n";
echo "URL: ?page=display&slug=demo-restaurant\n";

