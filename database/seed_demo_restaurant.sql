-- MenuCraft — Seed : Restaurant de démonstration complet
-- Exécuter dans phpMyAdmin ou via CLI : mysql -u root menucraft < seed_demo_restaurant.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. Restaurant
-- ============================================
INSERT INTO `restaurants` (`name`, `slug`) VALUES ('Le Petit Bistro Parisien', 'demo-restaurant');
SET @resto_id = LAST_INSERT_ID();

-- ============================================
-- 2. Admin lié au restaurant
-- ============================================
INSERT INTO `admins` (`username`, `email`, `password`, `role`, `restaurant_name`, `restaurant_id`, `carte_mode`, `email_verified`)
VALUES ('demo-admin', 'demo@menucraft.local', '$2y$12$qikpqKNAn6DKZa/M62qRsObgCtsNYugH2dFEyN9MsDncqzZJZthnu', 'ADMIN', 'Le Petit Bistro Parisien', @resto_id, 'editable', 1);
SET @admin_id = LAST_INSERT_ID();

-- ============================================
-- 3. Abonnement actif (premium pour la démo)
-- ============================================
INSERT INTO `client_subscriptions` (`admin_id`, `plan_type`, `status`, `price_per_month`, `started_at`)
VALUES (@admin_id, 'premium', 'active', 0, NOW());

-- ============================================
-- 4. Features premium activées
-- ============================================
INSERT INTO `premium_features` (`admin_id`, `feature_name`, `is_active`, `activated_at`) VALUES
(@admin_id, 'google_reviews', 1, NOW()),
(@admin_id, 'advanced_analytics', 1, NOW()),
(@admin_id, 'online_booking', 1, NOW()),
(@admin_id, 'delivery_integration', 1, NOW());

-- ============================================
-- 5. Options du site (en ligne + template)
-- ============================================
INSERT INTO `admin_options` (`admin_id`, `option_name`, `option_value`) VALUES
(@admin_id, 'site_online', '1'),
(@admin_id, 'site_palette', 'classic'),
(@admin_id, 'site_layout', 'standard'),
(@admin_id, 'email_notifications', '1');

-- ============================================
-- 6. Contact
-- ============================================
INSERT INTO `contact` (`admin_id`, `telephone`, `email`, `adresse`, `horaires`)
VALUES (@admin_id, '01 42 36 78 90', 'contact@petitbistro.fr', '24 Rue de Rivoli, 75004 Paris',
'Lundi : Fermé\nMardi - Vendredi : 12h00 - 14h30 / 19h00 - 22h30\nSamedi : 12h00 - 15h00 / 19h00 - 23h00\nDimanche : 12h00 - 15h00');

-- ============================================
-- 7. Catégories
-- ============================================
INSERT INTO `categories` (`admin_id`, `name`, `description`, `display_order`) VALUES
(@admin_id, 'Entrées', 'Nos entrées fraîches et de saison', 1),
(@admin_id, 'Plats', 'Nos plats signatures préparés avec soin', 2),
(@admin_id, 'Desserts', 'Douceurs sucrées pour terminer en beauté', 3),
(@admin_id, 'Boissons', 'Vins, cocktails et boissons sans alcool', 4);

SET @cat_entrees = LAST_INSERT_ID();
SET @cat_plats = @cat_entrees + 1;
SET @cat_desserts = @cat_entrees + 2;
SET @cat_boissons = @cat_entrees + 3;

-- ============================================
-- 8. Plats — Entrées
-- ============================================
INSERT INTO `plats` (`category_id`, `name`, `description`, `price`, `display_order`, `is_active`) VALUES
(@cat_entrees, 'Velouté de butternut', 'Velouté onctueux à la butternut rôtie, éclats de noisettes et crème fouettée', 9.50, 1, 1),
(@cat_entrees, 'Salade de chèvre chaud', 'Mesclun, toast de chèvre gratiné, miel, noix et vinaigrette balsamique', 11.00, 2, 1),
(@cat_entrees, 'Tartare de saumon', 'Saumon frais mariné citron-aneth, avocat et toast grillé', 13.50, 3, 1),
(@cat_entrees, 'Œuf parfait', 'Œuf cuit 64°, espuma de parmesan, chips de lard et salade frisée', 10.50, 4, 1),
(@cat_entrees, 'Burrata crémeuse', 'Burrata di Puglia, tomates confites, pesto frais et roquette', 12.00, 5, 1);

-- ============================================
-- 9. Plats — Plats principaux
-- ============================================
INSERT INTO `plats` (`category_id`, `name`, `description`, `price`, `display_order`, `is_active`) VALUES
(@cat_plats, 'Filet de bœuf rossini', 'Filet de bœuf, escalope de foie gras poêlée, jus truffé et pommes dauphines', 32.00, 1, 1),
(@cat_plats, 'Pavé de saumon grillé', 'Saumon label rouge, risotto aux asperges vertes et beurre citronné', 24.50, 2, 1),
(@cat_plats, 'Magret de canard', 'Magret rosé, purée de patate douce, figues rôties et sauce au porto', 26.00, 3, 1),
(@cat_plats, 'Risotto aux cèpes', 'Risotto crémeux aux cèpes frais, parmesan 24 mois et huile de truffe', 21.00, 4, 1),
(@cat_plats, 'Burger Le Bistro', 'Bœuf Aubrac, cheddar affiné, bacon fumé, oignons confits, frites maison', 18.50, 5, 1),
(@cat_plats, 'Souris d''agneau confite', 'Agneau confit 7h, polenta crémeuse, jus corsé aux herbes de Provence', 27.00, 6, 1);

-- ============================================
-- 10. Plats — Desserts
-- ============================================
INSERT INTO `plats` (`category_id`, `name`, `description`, `price`, `display_order`, `is_active`) VALUES
(@cat_desserts, 'Fondant au chocolat', 'Cœur coulant au chocolat noir 70%, glace vanille de Madagascar', 10.50, 1, 1),
(@cat_desserts, 'Tarte tatin', 'Pommes caramélisées, pâte feuilletée croustillante et crème fraîche', 9.50, 2, 1),
(@cat_desserts, 'Crème brûlée', 'Crème vanille Bourbon, caramel craquant', 8.50, 3, 1),
(@cat_desserts, 'Tiramisu maison', 'Mascarpone léger, café expresso et cacao amer', 9.00, 4, 1),
(@cat_desserts, 'Assiette de fromages', 'Sélection de 5 fromages affinés, confiture de figues et pain aux noix', 12.00, 5, 1);

-- ============================================
-- 11. Plats — Boissons
-- ============================================
INSERT INTO `plats` (`category_id`, `name`, `description`, `price`, `display_order`, `is_active`) VALUES
(@cat_boissons, 'Côtes du Rhône rouge', 'Domaine de la Janasse, 2021 — Fruité et épicé', 7.50, 1, 1),
(@cat_boissons, 'Sancerre blanc', 'Domaine Vacheron, 2022 — Frais et minéral', 9.00, 2, 1),
(@cat_boissons, 'Cocktail Spritz', 'Aperol, prosecco, eau gazeuse et tranche d''orange', 10.00, 3, 1),
(@cat_boissons, 'Limonade maison', 'Citron pressé, menthe fraîche et miel', 5.50, 4, 1),
(@cat_boissons, 'Café gourmand', 'Expresso et trio de mignardises du chef', 8.00, 5, 1);

-- ============================================
-- 12. Menus du jour
-- ============================================
INSERT INTO `daily_menus` (`admin_id`, `title`, `description`, `price`, `items`, `display_order`, `is_active`) VALUES
(@admin_id, 'Menu Déjeuner', 'Entrée + Plat + Dessert', 24.90,
 '["Velouté de butternut ou Salade de chèvre chaud", "Pavé de saumon grillé ou Risotto aux cèpes", "Fondant au chocolat ou Crème brûlée"]', 1, 1),
(@admin_id, 'Formule Express', 'Plat + Dessert', 18.50,
 '["Burger Le Bistro ou Risotto aux cèpes", "Tiramisu maison ou Tarte tatin"]', 2, 1);

-- ============================================
-- 13. Bannière
-- ============================================
INSERT INTO `banners` (`admin_id`, `filename`, `text`)
VALUES (@admin_id, '', 'Bienvenue au Petit Bistro Parisien — Cuisine française authentique');

SET FOREIGN_KEY_CHECKS = 1;

-- Terminé ! Le restaurant démo est accessible via ?page=display&slug=demo-restaurant
