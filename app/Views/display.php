<?php
if (!isset($admin)) $admin = null;
if (!isset($restaurant)) $restaurant = null;
if (!isset($banner)) $banner = null;
if (!isset($logo)) $logo = null;
if (!isset($contact)) $contact = null;
if (!isset($categories)) $categories = [];
if (!isset($dishesByCategory)) $dishesByCategory = [];
if (!isset($allergenesByDish)) $allergenesByDish = [];
if (!isset($cardImages)) $cardImages = [];
if (!isset($dailyMenus)) $dailyMenus = [];
if (!isset($options)) $options = [];
if (!isset($carteMode)) $carteMode = 'editable';
if (!isset($palette)) $palette = 'classic';
if (!isset($layout)) $layout = 'standard';
if (!isset($isPreview)) $isPreview = false;
if (!isset($bookingEnabled)) $bookingEnabled = false;
if (!isset($deliveryEnabled)) $deliveryEnabled = false;
$deliveryOrderingAvailable = $deliveryEnabled && !empty(array_filter(array_map('trim', explode("\n", $options['delivery_time_slots'] ?? ''))));
$bookingOrderingAvailable = $bookingEnabled && !empty(array_filter(array_map('trim', explode("\n", $options['booking_time_slots'] ?? ''))));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($admin->restaurant_name ?? 'Restaurant') ?> — MenuCraft</title>
    <meta name="description" content="Découvrez <?= htmlspecialchars($admin->restaurant_name ?? '') ?> — Consultez notre carte, horaires et réservez en ligne.">
    <meta property="og:title" content="<?= htmlspecialchars($admin->restaurant_name ?? '') ?>">
    <meta property="og:type" content="restaurant">
    <meta property="og:url" content="<?= APP_URL ?>?page=display&slug=<?= htmlspecialchars($restaurant->slug ?? '') ?>">
    <?php if ($banner): ?>
    <meta property="og:image" content="<?= APP_URL ?>/uploads/<?= htmlspecialchars($banner->filename) ?>">
    <?php endif; ?>

    <!-- Schema.org -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Restaurant",
        "name": "<?= htmlspecialchars($admin->restaurant_name ?? '') ?>",
        "url": "<?= APP_URL ?>?page=display&slug=<?= htmlspecialchars($restaurant->slug ?? '') ?>"
        <?php if ($contact): ?>
        ,"telephone": "<?= htmlspecialchars($contact->telephone ?? '') ?>"
        ,"address": "<?= htmlspecialchars($contact->adresse ?? '') ?>"
        <?php endif; ?>
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800<?php if (($palette === 'custom') && !empty($options['custom_font']) && !in_array($options['custom_font'], ['Inter', 'Playfair Display'])): ?>&family=<?= urlencode($options['custom_font']) ?>:wght@400;500;600;700;800<?php endif; ?>&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/display/base.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/display/templates.css">
    <script>if(localStorage.getItem('displayDarkMode')==='true')document.documentElement.classList.add('dark-mode');</script>
</head>
<body class="display-page template-<?= htmlspecialchars($palette) ?> layout-<?= htmlspecialchars($layout) ?><?= ($isPreview ?? false) ? ' has-preview' : '' ?>"<?php if ($palette === 'custom'): ?> style="--custom-primary:<?= htmlspecialchars($options['custom_primary'] ?? '#b45309') ?>;--custom-bg:<?= htmlspecialchars($options['custom_bg'] ?? '#ffffff') ?>;--font-family:'<?= htmlspecialchars($options['custom_font'] ?? 'Inter') ?>', system-ui, sans-serif;--font-display:'<?= htmlspecialchars($options['custom_font'] ?? 'Inter') ?>', system-ui, sans-serif;"<?php endif; ?>>

<?php $todayClosed = in_array(date('Y-m-d'), $closureDates ?? []); ?>

<?php if ($isPreview ?? false): ?>
<div class="preview-banner">
    <i class="fas fa-eye"></i> Mode prévisualisation — Seul vous pouvez voir cette page
</div>
<?php endif; ?>

<!-- Header -->
<header class="display-header">
    <div class="header-inner">
        <div class="restaurant-brand">
            <?php if ($logo): ?>
            <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($logo->filename) ?>" alt="Logo" class="restaurant-logo">
            <?php endif; ?>
            <span class="restaurant-name"><?= htmlspecialchars($admin->restaurant_name ?? '') ?></span>
        </div>

        <ul class="display-nav" id="displayNav">
            <li><a href="#carte">Carte</a></li>
            <?php if (!empty($dailyMenus)): ?>
            <li><a href="#menus">Menus</a></li>
            <?php endif; ?>
            <li><a href="#services">Services</a></li>
            <?php if ($bookingEnabled ?? false): ?>
            <li><a href="#reservation">Réservation</a></li>
            <?php endif; ?>
            <?php if ($deliveryEnabled ?? false): ?>
            <li><a href="#delivery">Livraison</a></li>
            <?php endif; ?>
            <li><a href="#contact">Contact</a></li>
        </ul>

        <div class="header-actions">
            <?php if ($deliveryOrderingAvailable): ?>
            <a href="#delivery" class="cart-header-btn" id="cartHeaderBtn" style="display:none;" onclick="document.getElementById('displayNav').classList.remove('open')">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge" id="cartHeaderCount">0</span>
            </a>
            <?php endif; ?>
            <button class="dark-mode-toggle" onclick="toggleDisplayDarkMode()" title="Mode sombre">
                <i class="fas fa-moon" id="displayDarkIcon"></i>
            </button>
            <button class="display-mobile-toggle" onclick="document.getElementById('displayNav').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<?php if ($todayClosed): ?>
<div class="closure-bar" id="closureBar">
    <div class="closure-bar-inner">
        <i class="fas fa-door-closed"></i>
        <span>Le restaurant est fermé aujourd'hui.</span>
    </div>
    <button class="closure-bar-close" onclick="document.getElementById('closureBar').style.display='none'" title="Fermer">
        <i class="fas fa-times"></i>
    </button>
</div>
<?php endif; ?>

<!-- Scroll navigation arrows -->
<div class="scroll-arrows">
    <button class="scroll-arrow scroll-arrow-up" id="scrollUpBtn" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Haut de page">
        <i class="fas fa-chevron-up"></i>
    </button>
    <button class="scroll-arrow scroll-arrow-down" id="scrollDownBtn" onclick="window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'})" title="Bas de page">
        <i class="fas fa-chevron-down"></i>
    </button>
</div>

<!-- Banner -->
<?php if ($banner): ?>
<section class="display-banner">
    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($banner->filename) ?>" alt="Bannière" class="banner-image">
    <div class="banner-overlay"></div>
    <div class="banner-content">
        <h1><?= htmlspecialchars($admin->restaurant_name ?? '') ?></h1>
        <?php if (!empty($banner->text)): ?>
        <p><?= htmlspecialchars($banner->text) ?></p>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>
<section class="display-banner no-banner">
    <div class="banner-content">
        <h1><?= htmlspecialchars($admin->restaurant_name ?? '') ?></h1>
    </div>
</section>
<?php endif; ?>

<!-- Carte -->
<section class="display-section" id="carte">
    <div class="display-container">
        <div class="display-section-title">
            <h2>Notre Carte</h2>
            <div class="title-decoration"></div>
        </div>

        <?php if ($carteMode === 'editable'): ?>
            <?php if (empty($categories)): ?>
                <p style="text-align:center;color:var(--color-text-muted);">La carte sera bientôt disponible.</p>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <?php $dishes = $dishesByCategory[$cat->id] ?? []; ?>
                    <?php $activeDishes = array_filter($dishes, fn($d) => $d->is_active); ?>
                    <?php if (!empty($activeDishes)): ?>
                    <div class="carte-category">
                        <h3><?= htmlspecialchars($cat->name) ?></h3>
                        <?php if (!empty(trim($cat->description ?? ''))): ?>
                        <p class="category-description"><?= htmlspecialchars($cat->description) ?></p>
                        <?php endif; ?>
                        <div class="dish-grid">
                            <?php foreach ($activeDishes as $dish): ?>
                            <div class="dish-card">
                                <?php if ($dish->image): ?>
                                <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($dish->image) ?>" alt="<?= htmlspecialchars($dish->name) ?>" class="dish-image" onclick="openLightbox(this.src)">
                                <?php endif; ?>
                                <div class="dish-details">
                                    <div class="dish-header">
                                        <span class="dish-name"><?= htmlspecialchars($dish->name) ?></span>
                                        <div class="dish-header-right">
                                            <span class="dish-price"><?= number_format($dish->price, 2, ',', ' ') ?> €</span>
                                            <?php if ($deliveryOrderingAvailable): ?>
                                            <button type="button" class="delivery-add-btn" onclick="addToCart(<?= $dish->id ?>, '<?= htmlspecialchars(addslashes($dish->name), ENT_QUOTES) ?>', <?= $dish->price ?>, this)" title="Ajouter au panier">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($dish->description): ?>
                                    <p class="dish-desc"><?= htmlspecialchars($dish->description) ?></p>
                                    <?php endif; ?>
                                    <?php $da = $allergenesByDish[$dish->id] ?? []; ?>
                                    <?php if (!empty($da)): ?>
                                    <div class="dish-allergenes">
                                        <?php foreach ($da as $al): ?>
                                        <span class="allergene-tag"><i class="fas <?= htmlspecialchars($al->icone) ?>"></i> <?= htmlspecialchars($al->nom) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- Mode images -->
            <?php if (!empty($cardImages)): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(300px,100%),1fr));gap:var(--spacing-md);">
                <?php foreach ($cardImages as $img): ?>
                <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($img->filename) ?>" alt="Carte" style="width:100%;max-width:100%;border-radius:var(--radius-md);cursor:pointer;" onclick="openLightbox(this.src)">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Menus du jour -->
<?php if (!empty($dailyMenus)): ?>
<section class="display-section" id="menus">
    <div class="display-container">
        <div class="display-section-title">
            <h2>Menus & Formules</h2>
            <div class="title-decoration"></div>
        </div>
        <div class="daily-menu-grid">
            <?php foreach ($dailyMenus as $menu): ?>
            <?php
            $menuItemsData = json_decode($menu->items ?? '[]', true) ?: [];
            $hasChoices = false;
            foreach ($menuItemsData as $mi) {
                $ch = $mi['choices'] ?? (!empty($mi['value']) ? [$mi['value']] : []);
                if (count($ch) > 1) { $hasChoices = true; break; }
            }
            ?>
            <div class="daily-menu-card">
                <div class="menu-header">
                    <h4><?= htmlspecialchars($menu->title) ?></h4>
                    <?php if ($menu->price): ?>
                    <span class="menu-price"><?= number_format($menu->price, 2, ',', ' ') ?> €</span>
                    <?php endif; ?>
                </div>
                <div class="menu-body">
                    <?php if ($menu->description): ?>
                    <p style="font-size:0.85rem;color:var(--color-text-muted);margin-bottom:12px;font-style:italic;"><?= htmlspecialchars($menu->description) ?></p>
                    <?php endif; ?>
                    <?php foreach ($menuItemsData as $item):
                        $choices = $item['choices'] ?? (!empty($item['value']) ? [$item['value']] : []);
                    ?>
                    <div class="menu-item">
                        <span class="item-label"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                        <?php if (count($choices) > 1): ?>
                            <span class="item-choices-label">Au choix :</span>
                            <ul class="item-choices-list">
                                <?php foreach ($choices as $choice): ?>
                                <li><?= htmlspecialchars($choice) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif (count($choices) === 1): ?>
                            <span class="item-value"><?= htmlspecialchars($choices[0]) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($deliveryOrderingAvailable && $menu->price): ?>
                    <div style="text-align:center;margin-top:12px;padding-top:12px;border-top:1px solid var(--color-border-light);">
                        <?php if ($hasChoices): ?>
                        <button type="button" class="delivery-add-btn menu-add-btn" onclick="openMenuChoiceModal(<?= $menu->id ?>, <?= htmlspecialchars(json_encode($menu->title), ENT_QUOTES) ?>, <?= (float)$menu->price ?>, <?= htmlspecialchars(json_encode($menuItemsData), ENT_QUOTES) ?>)" title="Commander ce menu">
                            <i class="fas fa-plus"></i> <span>Ajouter au panier</span>
                        </button>
                        <?php else: ?>
                        <button type="button" class="delivery-add-btn menu-add-btn" onclick="addMenuToCartDirect(<?= $menu->id ?>, <?= htmlspecialchars(json_encode($menu->title), ENT_QUOTES) ?>, <?= (float)$menu->price ?>, <?= htmlspecialchars(json_encode($menuItemsData), ENT_QUOTES) ?>, this)" title="Commander ce menu">
                            <i class="fas fa-plus"></i> <span>Ajouter au panier</span>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Services -->
<section class="display-section" id="services">
    <div class="display-container">
        <div class="display-section-title">
            <h2>Nos Services</h2>
            <div class="title-decoration"></div>
        </div>

        <?php
        $serviceItems = [
            'service_sur_place' => ['Sur place', 'fa-chair'],
            'service_a_emporter' => ['À emporter', 'fa-bag-shopping'],
            'service_livraison_ubereats' => ['Livraison', 'fa-motorcycle'],
            'service_wifi' => ['WiFi gratuit', 'fa-wifi'],
            'service_climatisation' => ['Climatisation', 'fa-snowflake'],
            'service_pmr' => ['Accès PMR', 'fa-wheelchair'],
            'service_animaux' => ['Animaux acceptés', 'fa-paw'],
        ];
        $activeServices = [];
        foreach ($serviceItems as $key => [$label, $icon]) {
            if (($options[$key] ?? '0') === '1') $activeServices[] = [$label, $icon];
        }
        ?>
        <?php if (!empty($activeServices)): ?>
        <div class="services-grid">
            <?php foreach ($activeServices as [$label, $icon]): ?>
            <div class="service-item">
                <i class="fas <?= $icon ?>"></i>
                <span><?= $label ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Moyens de paiement -->
        <?php
        $paymentItems = [
            'payment_visa' => 'Visa', 'payment_mastercard' => 'Mastercard',
            'payment_cb' => 'CB', 'payment_especes' => 'Espèces',
            'payment_cheques' => 'Chèques', 'payment_tickets_restaurant' => 'Tickets resto',
        ];
        $activePayments = [];
        foreach ($paymentItems as $key => $label) {
            if (($options[$key] ?? '0') === '1') $activePayments[] = $label;
        }
        ?>
        <?php if (!empty($activePayments)): ?>
        <div style="text-align:center;margin-top:var(--spacing-xl);">
            <h3 style="font-size:1rem;color:var(--color-text-light);margin-bottom:var(--spacing-md);">Moyens de paiement acceptés</h3>
            <div class="payment-methods">
                <?php foreach ($activePayments as $label): ?>
                <span class="payment-badge"><i class="fas fa-credit-card"></i> <?= $label ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Réseaux sociaux -->
        <?php
        $socialItems = [
            'social_instagram' => 'fa-instagram', 'social_facebook' => 'fa-facebook',
            'social_x' => 'fa-x-twitter', 'social_tiktok' => 'fa-tiktok', 'social_snapchat' => 'fa-snapchat',
        ];
        $activeSocials = [];
        foreach ($socialItems as $key => $icon) {
            if (!empty($options[$key])) $activeSocials[] = [$options[$key], $icon];
        }
        ?>
        <?php if (!empty($activeSocials)): ?>
        <div class="social-links">
            <?php foreach ($activeSocials as [$url, $icon]): ?>
            <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="social-link" rel="noopener">
                <i class="fab <?= $icon ?>"></i>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Réservation -->
<?php if ($bookingEnabled ?? false): ?>
<section class="display-section" id="reservation">
    <div class="display-container">
        <div class="display-section-title">
            <h2>Réserver une table</h2>
            <div class="title-decoration"></div>
        </div>

        <?php if (!empty($options['booking_message'])): ?>
        <p style="text-align:center;color:var(--color-text-light);margin-bottom:var(--spacing-xl);font-style:italic;">
            <?= htmlspecialchars($options['booking_message']) ?>
        </p>
        <?php endif; ?>

        <?php if ($bookingOrderingAvailable): ?>
        <?php
        $timeSlots = array_filter(array_map('trim', explode("\n", $options['booking_time_slots'] ?? '')));
        ?>
        <div class="reservation-form" id="bookingForm">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom complet *</label>
                    <input type="text" id="bookName" required placeholder="Votre nom">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" id="bookPhone" placeholder="06 12 34 56 78">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="bookEmail" placeholder="votre@email.com">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" id="bookDate" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Heure *</label>
                    <select id="bookTime" required>
                        <option value="">Choisir un créneau</option>
                        <?php foreach ($timeSlots as $slot):
                            if (preg_match('/^\d{1,2}:\d{2}$/', $slot)): ?>
                        <option value="<?= htmlspecialchars($slot) ?>"><?= htmlspecialchars($slot) ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre de personnes *</label>
                <select id="bookSize">
                    <?php for ($i = (int)($options['booking_min_party'] ?? 1); $i <= (int)($options['booking_max_party'] ?? 20); $i++): ?>
                    <option value="<?= $i ?>" <?= $i === 2 ? 'selected' : '' ?>><?= $i ?> personne<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Demandes spéciales</label>
                <textarea id="bookRequests" rows="3" placeholder="Allergies, occasion spéciale..."></textarea>
            </div>
            <button type="button" onclick="submitBooking()" class="display-btn display-btn-primary display-btn-block display-btn-lg">
                <i class="fas fa-calendar-check"></i> Réserver
            </button>
            <div id="bookingMessage" style="margin-top:12px;text-align:center;display:none;"></div>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:var(--spacing-xl);color:var(--color-text-muted);">
            <i class="fas fa-clock" style="font-size:2rem;margin-bottom:var(--spacing-sm);display:block;opacity:0.5;"></i>
            <p style="font-size:0.9rem;">Les réservations en ligne ne sont pas disponibles pour le moment.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Livraison -->
<?php if ($deliveryEnabled ?? false): ?>
<section class="display-section" id="delivery">
    <div class="display-container">
        <div class="display-section-title">
            <h2><i class="fas fa-motorcycle"></i> Commander en livraison</h2>
            <div class="title-decoration"></div>
        </div>

        <?php if (!empty($options['delivery_message'])): ?>
        <p style="text-align:center;color:var(--color-text-light);margin-bottom:var(--spacing-lg);font-style:italic;">
            <?= htmlspecialchars($options['delivery_message']) ?>
        </p>
        <?php endif; ?>

        <?php
        $platforms = [];
        if (!empty($options['delivery_platform_ubereats'])) $platforms[] = ['url' => $options['delivery_platform_ubereats'], 'name' => 'Uber Eats', 'icon' => 'https://cdn.simpleicons.org/ubereats/06C167', 'color' => '#06C167'];
        if (!empty($options['delivery_platform_deliveroo'])) $platforms[] = ['url' => $options['delivery_platform_deliveroo'], 'name' => 'Deliveroo', 'icon' => 'https://cdn.simpleicons.org/deliveroo/00CCBC', 'color' => '#00CCBC'];
        if (!empty($options['delivery_platform_justeat'])) $platforms[] = ['url' => $options['delivery_platform_justeat'], 'name' => 'Just Eat', 'icon' => 'https://cdn.simpleicons.org/justeat/F36D00', 'color' => '#F36D00'];
        if (!empty($options['delivery_platform_other'])) $platforms[] = ['url' => $options['delivery_platform_other'], 'name' => $options['delivery_platform_other_name'] ?? 'Autre', 'icon' => '', 'color' => 'var(--color-primary)'];
        ?>
        <?php if (!empty($platforms)): ?>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-bottom:var(--spacing-xl);">
            <?php foreach ($platforms as $pf): ?>
            <a href="<?= htmlspecialchars($pf['url']) ?>" target="_blank" rel="noopener" class="delivery-platform-link" style="--platform-color:<?= htmlspecialchars($pf['color']) ?>;">
                <?php if ($pf['icon']): ?>
                <img src="<?= htmlspecialchars($pf['icon']) ?>" alt="" style="width:20px;height:20px;">
                <?php else: ?>
                <i class="fas fa-external-link-alt"></i>
                <?php endif; ?>
                <span>Commander sur <?= htmlspecialchars($pf['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-bottom:var(--spacing-xl);font-size:0.85rem;color:var(--color-text-muted);">
            <?php if (!empty($options['delivery_estimated_time'])): ?>
            <span><i class="fas fa-clock"></i> <?= htmlspecialchars($options['delivery_estimated_time']) ?></span>
            <?php endif; ?>
            <?php if (!empty($options['delivery_fee']) && (float)$options['delivery_fee'] > 0): ?>
            <span><i class="fas fa-euro-sign"></i> Livraison : <?= number_format((float)$options['delivery_fee'], 2, ',', '') ?> €</span>
            <?php else: ?>
            <span><i class="fas fa-gift"></i> Livraison gratuite</span>
            <?php endif; ?>
            <?php if (!empty($options['delivery_min_order']) && (float)$options['delivery_min_order'] > 0): ?>
            <span><i class="fas fa-shopping-basket"></i> Min. <?= number_format((float)$options['delivery_min_order'], 2, ',', '') ?> €</span>
            <?php endif; ?>
            <?php if (!empty($options['delivery_radius_km'])): ?>
            <span><i class="fas fa-map-marker-alt"></i> Rayon : <?= htmlspecialchars($options['delivery_radius_km']) ?> km</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($options['delivery_zones'])): ?>
        <p style="text-align:center;font-size:0.82rem;color:var(--color-text-muted);margin-bottom:var(--spacing-lg);">
            <i class="fas fa-map-marked-alt"></i> <strong>Zones desservies :</strong> <?= htmlspecialchars($options['delivery_zones']) ?>
        </p>
        <?php endif; ?>

        <?php if (!empty($options['delivery_hours'])): ?>
        <p style="text-align:center;font-size:0.82rem;color:var(--color-text-muted);margin-bottom:var(--spacing-lg);">
            <i class="fas fa-clock"></i> <?= nl2br(htmlspecialchars($options['delivery_hours'])) ?>
        </p>
        <?php endif; ?>

        <?php
        $deliverySlots = array_filter(array_map('trim', explode("\n", $options['delivery_time_slots'] ?? '')));
        ?>

        <?php if ($deliveryOrderingAvailable): ?>
        <!-- Panier -->
        <div id="deliveryCart" style="display:none;background:var(--color-bg-alt);border:1px solid var(--color-border);border-radius:var(--radius-md);padding:var(--spacing-lg);margin-bottom:var(--spacing-lg);">
            <h3 style="margin-bottom:var(--spacing-md);font-size:1rem;"><i class="fas fa-shopping-cart"></i> Votre panier</h3>
            <div id="cartItems"></div>
            <div style="border-top:1px solid var(--color-border);padding-top:var(--spacing-sm);margin-top:var(--spacing-sm);display:flex;justify-content:space-between;font-weight:700;">
                <span>Total</span>
                <span id="cartTotal">0,00 €</span>
            </div>
        </div>

        <!-- Formulaire commande -->
        <div id="deliveryFormContainer" style="display:none;">
            <div class="reservation-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom complet *</label>
                        <input type="text" id="delName" required placeholder="Votre nom">
                    </div>
                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="tel" id="delPhone" required placeholder="06 12 34 56 78">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="delEmail" placeholder="votre@email.com">
                </div>
                <div class="form-group">
                    <label>Adresse de livraison *</label>
                    <textarea id="delAddress" rows="2" required placeholder="Numéro, rue, code postal, ville"></textarea>
                </div>
                <div class="form-group">
                    <label>Créneau de livraison souhaité *</label>
                    <select id="delTimeSlot" required>
                        <option value="">— Choisir un créneau —</option>
                        <?php foreach ($deliverySlots as $slot): ?>
                        <option value="<?= htmlspecialchars($slot) ?>"><?= htmlspecialchars($slot) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Instructions de livraison</label>
                    <input type="text" id="delNotes" placeholder="Interphone, étage, code...">
                </div>
                <button type="button" onclick="submitDeliveryOrder()" class="display-btn display-btn-primary display-btn-block display-btn-lg">
                    <i class="fas fa-paper-plane"></i> Envoyer la commande
                </button>
            </div>
        </div>

        <div id="deliveryMessage" style="margin-top:12px;text-align:center;display:none;"></div>

        <!-- Info: ajouter des plats -->
        <p id="deliveryHint" style="text-align:center;color:var(--color-text-muted);font-size:0.85rem;">
            <i class="fas fa-info-circle"></i> Ajoutez des plats depuis la carte ci-dessus pour commander en livraison.
        </p>
        <?php else: ?>
        <div style="text-align:center;padding:var(--spacing-xl);color:var(--color-text-muted);">
            <i class="fas fa-clock" style="font-size:2rem;margin-bottom:var(--spacing-sm);display:block;opacity:0.5;"></i>
            <p style="font-size:0.9rem;">La commande en ligne n'est pas disponible pour le moment.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Footer / Contact -->
<footer class="display-footer" id="contact">
    <div class="footer-content">
        <div class="footer-info">
            <h3><?= htmlspecialchars($admin->restaurant_name ?? '') ?></h3>
            <?php if ($contact): ?>
                <?php if ($contact->telephone): ?>
                <p><i class="fas fa-phone"></i> <?= htmlspecialchars($contact->telephone) ?></p>
                <?php endif; ?>
                <?php if ($contact->email): ?>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($contact->email) ?></p>
                <?php endif; ?>
                <?php if ($contact->adresse): ?>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($contact->adresse) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($contact && $contact->horaires): ?>
        <div class="footer-hours">
            <h4><i class="fas fa-clock" style="color:var(--color-primary-light);margin-right:6px;"></i> Horaires</h4>
            <?php foreach (explode("\n", $contact->horaires) as $line): ?>
            <p><?= htmlspecialchars(trim($line)) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($contact && $contact->adresse): ?>
        <div class="footer-map">
            <iframe src="https://maps.google.com/maps?q=<?= urlencode($contact->adresse) ?>&output=embed" allowfullscreen loading="lazy"></iframe>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer-bottom-display">
        <span>© <?= date('Y') ?> <?= htmlspecialchars($admin->restaurant_name ?? '') ?> — Propulsé par <a href="<?= APP_URL ?>" style="color:var(--color-primary-light);">MenuCraft</a></span>
        <div class="footer-legal-links">
            <a href="<?= APP_URL ?>?page=legal&section=cgu">CGU</a>
            <a href="<?= APP_URL ?>?page=legal&section=privacy">Confidentialité</a>
            <a href="<?= APP_URL ?>?page=legal&section=cookies">Cookies</a>
        </div>
    </div>
</footer>

<!-- Cookie Banner -->
<div class="cookie-banner" id="cookieBanner">
    <div class="cookie-banner-inner">
        <p>🍪 Ce site utilise des cookies pour améliorer votre expérience. En continuant, vous acceptez notre <a href="<?= APP_URL ?>?page=legal&section=cookies" style="color:var(--color-primary-light);">politique de cookies</a>.</p>
        <div class="cookie-actions">
            <button class="cookie-accept" onclick="acceptCookies()">Accepter</button>
            <button class="cookie-customize" onclick="toggleCookiePrefs()">Personnaliser</button>
            <button class="cookie-refuse" onclick="refuseCookies()">Refuser</button>
        </div>
    </div>
    <div class="cookie-prefs" id="cookiePrefs">
        <div class="cookie-pref-item">
            <div>
                <strong>Cookies essentiels</strong>
                <p>Nécessaires au fonctionnement du site.</p>
            </div>
            <label class="cookie-toggle">
                <input type="checkbox" checked disabled>
                <span class="cookie-toggle-slider"></span>
            </label>
        </div>
        <div class="cookie-pref-item">
            <div>
                <strong>Cookies analytiques</strong>
                <p>Nous aident à comprendre comment vous utilisez le site.</p>
            </div>
            <label class="cookie-toggle">
                <input type="checkbox" id="cookieAnalytics">
                <span class="cookie-toggle-slider"></span>
            </label>
        </div>
        <div class="cookie-pref-item">
            <div>
                <strong>Cookies marketing</strong>
                <p>Utilisés pour vous proposer du contenu pertinent.</p>
            </div>
            <label class="cookie-toggle">
                <input type="checkbox" id="cookieMarketing">
                <span class="cookie-toggle-slider"></span>
            </label>
        </div>
        <div style="display:flex;justify-content:flex-end;padding-top:12px;">
            <button class="cookie-save-prefs" onclick="saveCustomCookies()"><i class="fas fa-check"></i> Enregistrer mes choix</button>
        </div>
    </div>
</div>

<!-- Cookie Settings Shortcut -->
<button class="cookie-reopen-btn" id="cookieReopenBtn" onclick="reopenCookieBanner()" title="Paramètres des cookies">
    <i class="fas fa-cookie-bite"></i>
</button>

<!-- Lightbox -->
<div class="lightbox-overlay" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <img src="" alt="Image agrandie" id="lightboxImg">
</div>

<script>
// Scroll arrows visibility
(function() {
    const upBtn = document.getElementById('scrollUpBtn');
    const downBtn = document.getElementById('scrollDownBtn');
    function updateArrows() {
        const scrollY = window.scrollY;
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        upBtn.classList.toggle('visible', scrollY > 200);
        downBtn.classList.toggle('visible', scrollY < maxScroll - 200);
    }
    updateArrows();
    window.addEventListener('scroll', updateArrows, {passive: true});
})();

// Closure dates - block booking
<?php if ($bookingEnabled ?? false): ?>
const closureDates = <?= json_encode($closureDates ?? []) ?>;
<?php endif; ?>

// Dark mode
function toggleDisplayDarkMode() {
    document.documentElement.classList.toggle('dark-mode');
    const isDark = document.documentElement.classList.contains('dark-mode');
    localStorage.setItem('displayDarkMode', isDark);
    const icon = document.getElementById('displayDarkIcon');
    if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}
if (document.documentElement.classList.contains('dark-mode')) {
    const icon = document.getElementById('displayDarkIcon');
    if (icon) icon.className = 'fas fa-sun';
}

// Smooth scroll
document.querySelectorAll('.display-nav a[href^="#"]').forEach(a => {
    a.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('displayNav').classList.remove('open');
        }
    });
});

// Lightbox
function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}

// Cookies
const cookieReopenBtn = document.getElementById('cookieReopenBtn');
function showReopenBtn() {
    if (cookieReopenBtn) cookieReopenBtn.classList.add('visible');
}
function hideReopenBtn() {
    if (cookieReopenBtn) cookieReopenBtn.classList.remove('visible');
}
if (!localStorage.getItem('display_cookie_consent')) {
    document.getElementById('cookieBanner').classList.add('show');
} else {
    showReopenBtn();
}
function acceptCookies() {
    localStorage.setItem('display_cookie_consent', 'accepted');
    document.getElementById('cookieBanner').classList.remove('show');
    showReopenBtn();
}
function refuseCookies() {
    localStorage.setItem('display_cookie_consent', 'refused');
    document.getElementById('cookieBanner').classList.remove('show');
    showReopenBtn();
}
function toggleCookiePrefs() {
    const prefs = document.getElementById('cookiePrefs');
    prefs.style.display = prefs.style.display === 'block' ? 'none' : 'block';
}
function saveCustomCookies() {
    const analytics = document.getElementById('cookieAnalytics')?.checked ? '1' : '0';
    const marketing = document.getElementById('cookieMarketing')?.checked ? '1' : '0';
    localStorage.setItem('display_cookie_consent', 'custom');
    localStorage.setItem('cookie_analytics', analytics);
    localStorage.setItem('cookie_marketing', marketing);
    document.getElementById('cookieBanner').classList.remove('show');
    showReopenBtn();
}
function reopenCookieBanner() {
    hideReopenBtn();
    document.getElementById('cookieBanner').classList.add('show');
}

// Booking
<?php if ($bookingEnabled ?? false): ?>
function submitBooking() {
    const name = document.getElementById('bookName').value.trim();
    const date = document.getElementById('bookDate').value;
    const time = document.getElementById('bookTime').value;
    const msg = document.getElementById('bookingMessage');

    if (!name || !date || !time) {
        msg.style.display = 'block';
        msg.style.color = 'var(--color-error)';
        msg.innerHTML = 'Veuillez remplir les champs obligatoires.';
        return;
    }

    if (closureDates.includes(date)) {
        msg.style.display = 'block';
        msg.style.color = 'var(--color-error)';
        msg.innerHTML = '<i class="fas fa-door-closed"></i> Le restaurant est fermé à cette date. Veuillez choisir une autre date.';
        return;
    }

    const data = new FormData();
    data.append('admin_id', '<?= $admin->id ?? 0 ?>');
    data.append('customer_name', name);
    data.append('customer_phone', document.getElementById('bookPhone').value);
    data.append('customer_email', document.getElementById('bookEmail').value);
    data.append('reservation_date', date);
    data.append('reservation_time', time);
    data.append('party_size', document.getElementById('bookSize').value);
    data.append('special_requests', document.getElementById('bookRequests').value);

    fetch('<?= APP_URL ?>?page=public-booking', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            msg.style.display = 'block';
            if (res.success) {
                msg.style.color = 'var(--color-success)';
                msg.innerHTML = '<i class="fas fa-check-circle"></i> ' + res.message;
                document.getElementById('bookName').value = '';
                document.getElementById('bookDate').value = '';
                document.getElementById('bookTime').value = '';
            } else {
                msg.style.color = 'var(--color-error)';
                msg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (res.error || 'Erreur');
            }
        })
        .catch(() => {
            msg.style.display = 'block';
            msg.style.color = 'var(--color-error)';
            msg.innerHTML = 'Erreur de connexion.';
        });
}
<?php endif; ?>

// Delivery cart
<?php if ($deliveryEnabled ?? false): ?>
const deliveryCart = [];
let menuCartIdCounter = 0;
const DELIVERY_FEE = <?= (float)($options['delivery_fee'] ?? 0) ?>;
const DELIVERY_MIN = <?= (float)($options['delivery_min_order'] ?? 0) ?>;

function addToCart(dishId, dishName, price, btnEl) {
    const existing = deliveryCart.find(i => i.type === 'dish' && i.dish_id === dishId);
    if (existing) {
        existing.quantity++;
    } else {
        deliveryCart.push({ type: 'dish', dish_id: dishId, name: dishName, price: price, quantity: 1 });
    }
    if (btnEl) { btnEl.classList.add('added'); setTimeout(() => btnEl.classList.remove('added'), 600); }
    renderCart();
    updateCartHeader();
}

function addMenuToCartDirect(menuId, menuTitle, price, itemsData, btnEl) {
    const selectedChoices = {};
    itemsData.forEach(cat => {
        const choices = cat.choices || (cat.value ? [cat.value] : []);
        if (choices.length > 0) selectedChoices[cat.label] = choices[0];
    });
    deliveryCart.push({ type: 'menu', cart_id: ++menuCartIdCounter, menu_id: menuId, name: menuTitle, price: price, quantity: 1, selected_choices: selectedChoices });
    if (btnEl) { btnEl.classList.add('added'); setTimeout(() => btnEl.classList.remove('added'), 600); }
    renderCart();
    updateCartHeader();
}

function openMenuChoiceModal(menuId, menuTitle, price, itemsData) {
    let modal = document.getElementById('menuChoiceModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'menuChoiceModal';
        modal.className = 'menu-choice-overlay';
        document.body.appendChild(modal);
    }
    let html = `<div class="menu-choice-panel">
        <div class="menu-choice-header">
            <h3><i class="fas fa-utensils"></i> ${menuTitle}</h3>
            <span style="font-weight:700;color:var(--color-primary);">${price.toFixed(2).replace('.', ',')} €</span>
        </div>
        <div class="menu-choice-body">
            <p style="font-size:0.85rem;color:var(--color-text-muted);margin-bottom:12px;">Choisissez une option pour chaque catégorie :</p>`;
    itemsData.forEach((cat, ci) => {
        const choices = cat.choices || (cat.value ? [cat.value] : []);
        html += `<div class="menu-choice-category">
            <label class="menu-choice-cat-label">${cat.label}</label>`;
        if (choices.length > 1) {
            choices.forEach((ch, chi) => {
                html += `<label class="menu-choice-option">
                    <input type="radio" name="menuChoice_${ci}" value="${ch.replace(/"/g, '&quot;')}" ${chi === 0 ? 'checked' : ''}>
                    <span>${ch}</span>
                </label>`;
            });
        } else if (choices.length === 1) {
            html += `<div class="menu-choice-fixed"><i class="fas fa-check" style="color:var(--color-success);margin-right:6px;"></i>${choices[0]}</div>
                <input type="hidden" name="menuChoice_${ci}" value="${choices[0].replace(/"/g, '&quot;')}">`;
        }
        html += `</div>`;
    });
    html += `</div>
        <div class="menu-choice-footer">
            <button type="button" class="mc-btn-cancel" onclick="closeMenuChoiceModal()">Annuler</button>
            <button type="button" class="mc-btn-confirm" onclick="confirmMenuChoice(${menuId}, '${menuTitle.replace(/'/g, "\\'")}', ${price}, ${JSON.stringify(itemsData).replace(/'/g, "\\'")})">
                <i class="fas fa-cart-plus"></i> Ajouter au panier
            </button>
        </div>
    </div>`;
    modal.innerHTML = html;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeMenuChoiceModal() {
    const modal = document.getElementById('menuChoiceModal');
    if (modal) { modal.style.display = 'none'; document.body.style.overflow = ''; }
}

function confirmMenuChoice(menuId, menuTitle, price, itemsData) {
    const selectedChoices = {};
    itemsData.forEach((cat, ci) => {
        const radio = document.querySelector(`input[name="menuChoice_${ci}"]:checked`);
        const hidden = document.querySelector(`input[type="hidden"][name="menuChoice_${ci}"]`);
        if (radio) selectedChoices[cat.label] = radio.value;
        else if (hidden) selectedChoices[cat.label] = hidden.value;
    });
    deliveryCart.push({ type: 'menu', cart_id: ++menuCartIdCounter, menu_id: menuId, name: menuTitle, price: price, quantity: 1, selected_choices: selectedChoices });
    closeMenuChoiceModal();
    renderCart();
    updateCartHeader();
}

function updateCartHeader() {
    const btn = document.getElementById('cartHeaderBtn');
    const count = document.getElementById('cartHeaderCount');
    if (!btn) return;
    const totalItems = deliveryCart.reduce((s, i) => s + i.quantity, 0);
    if (totalItems > 0) {
        btn.style.display = 'inline-flex';
        count.textContent = totalItems;
        btn.classList.remove('cart-bounce');
        void btn.offsetWidth;
        btn.classList.add('cart-bounce');
    } else {
        btn.style.display = 'none';
    }
}

function removeFromCartByIndex(idx) {
    deliveryCart.splice(idx, 1);
    renderCart();
    updateCartHeader();
}

function updateQtyByIndex(idx, delta) {
    const item = deliveryCart[idx];
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) removeFromCartByIndex(idx);
    else { renderCart(); updateCartHeader(); }
}

// Legacy functions kept for dish buttons
function removeFromCart(dishId) {
    const idx = deliveryCart.findIndex(i => i.type === 'dish' && i.dish_id === dishId);
    if (idx > -1) removeFromCartByIndex(idx);
}
function updateQty(dishId, delta) {
    const idx = deliveryCart.findIndex(i => i.type === 'dish' && i.dish_id === dishId);
    if (idx > -1) updateQtyByIndex(idx, delta);
}

function renderCart() {
    const cartEl = document.getElementById('deliveryCart');
    const itemsEl = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    const formEl = document.getElementById('deliveryFormContainer');
    const hintEl = document.getElementById('deliveryHint');

    if (deliveryCart.length === 0) {
        cartEl.style.display = 'none';
        formEl.style.display = 'none';
        hintEl.style.display = 'block';
        return;
    }

    hintEl.style.display = 'none';
    cartEl.style.display = 'block';
    formEl.style.display = 'block';

    let html = '';
    let subtotal = 0;
    deliveryCart.forEach((item, idx) => {
        const lineTotal = item.price * item.quantity;
        subtotal += lineTotal;
        let choicesHtml = '';
        if (item.type === 'menu' && item.selected_choices) {
            choicesHtml = '<div style="font-size:0.75rem;color:var(--color-text-muted);margin-top:2px;">' +
                Object.entries(item.selected_choices).map(([k,v]) => `${k}: ${v}`).join(' · ') + '</div>';
        }
        const badge = item.type === 'menu' ? '<span style="background:var(--color-primary);color:#fff;font-size:0.65rem;padding:1px 5px;border-radius:4px;margin-left:6px;vertical-align:middle;">MENU</span>' : '';
        html += `<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--color-border);">
            <div style="flex:1;min-width:0;">
                <span style="font-weight:500;font-size:0.88rem;">${item.name}${badge}</span>
                <span style="color:var(--color-text-muted);font-size:0.8rem;margin-left:6px;">${item.price.toFixed(2).replace('.', ',')} €</span>
                ${choicesHtml}
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <button onclick="updateQtyByIndex(${idx}, -1)" style="width:24px;height:24px;border:1px solid var(--color-border);border-radius:50%;background:transparent;cursor:pointer;font-size:0.8rem;">−</button>
                <span style="min-width:20px;text-align:center;font-weight:600;">${item.quantity}</span>
                <button onclick="updateQtyByIndex(${idx}, 1)" style="width:24px;height:24px;border:1px solid var(--color-border);border-radius:50%;background:transparent;cursor:pointer;font-size:0.8rem;">+</button>
                <button onclick="removeFromCartByIndex(${idx})" style="border:none;background:none;color:var(--color-error);cursor:pointer;font-size:0.85rem;padding:4px;" title="Retirer"><i class="fas fa-trash"></i></button>
            </div>
            <span style="min-width:60px;text-align:right;font-weight:600;font-size:0.88rem;">${lineTotal.toFixed(2).replace('.', ',')} €</span>
        </div>`;
    });

    if (DELIVERY_FEE > 0) {
        html += `<div style="display:flex;justify-content:space-between;padding:6px 0;font-size:0.82rem;color:var(--color-text-muted);">
            <span>Frais de livraison</span><span>${DELIVERY_FEE.toFixed(2).replace('.', ',')} €</span>
        </div>`;
    }

    itemsEl.innerHTML = html;
    totalEl.textContent = (subtotal + DELIVERY_FEE).toFixed(2).replace('.', ',') + ' €';
}

function submitDeliveryOrder() {
    const name = document.getElementById('delName').value.trim();
    const phone = document.getElementById('delPhone').value.trim();
    const address = document.getElementById('delAddress').value.trim();
    const timeSlot = document.getElementById('delTimeSlot').value.trim();
    const msg = document.getElementById('deliveryMessage');

    if (!name || !phone || !address || !timeSlot) {
        msg.style.display = 'block';
        msg.style.color = 'var(--color-error)';
        msg.innerHTML = 'Veuillez remplir les champs obligatoires (nom, téléphone, adresse, créneau).';
        return;
    }

    const subtotal = deliveryCart.reduce((s, i) => s + i.price * i.quantity, 0);
    if (DELIVERY_MIN > 0 && subtotal < DELIVERY_MIN) {
        msg.style.display = 'block';
        msg.style.color = 'var(--color-error)';
        msg.innerHTML = 'Montant minimum de commande : ' + DELIVERY_MIN.toFixed(2).replace('.', ',') + ' €.';
        return;
    }

    const dishItems = deliveryCart.filter(i => i.type === 'dish').map(i => ({ dish_id: i.dish_id, quantity: i.quantity }));
    const menuItems = deliveryCart.filter(i => i.type === 'menu').map(i => ({ menu_id: i.menu_id, quantity: i.quantity, selected_choices: i.selected_choices }));

    const data = new FormData();
    data.append('admin_id', '<?= $admin->id ?? 0 ?>');
    data.append('customer_name', name);
    data.append('customer_phone', phone);
    data.append('customer_email', document.getElementById('delEmail').value.trim());
    data.append('delivery_address', address);
    data.append('delivery_time_slot', timeSlot);
    data.append('delivery_notes', document.getElementById('delNotes').value.trim());
    data.append('items', JSON.stringify(dishItems));
    data.append('menu_items', JSON.stringify(menuItems));

    const btn = document.querySelector('#deliveryFormContainer button[onclick]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';

    fetch('<?= APP_URL ?>?page=delivery-public-order', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            msg.style.display = 'block';
            if (res.success) {
                msg.style.color = 'var(--color-success)';
                msg.innerHTML = '<i class="fas fa-check-circle"></i> ' + res.message;
                msg.style.padding = '16px';
                msg.style.background = 'var(--color-success-bg, #ecfdf5)';
                msg.style.borderRadius = '8px';
                msg.style.fontSize = '1rem';
                msg.style.fontWeight = '600';
                deliveryCart.length = 0;
                renderCart();
                updateCartHeader();
                document.getElementById('delName').value = '';
                document.getElementById('delPhone').value = '';
                document.getElementById('delEmail').value = '';
                document.getElementById('delAddress').value = '';
                const tsEl = document.getElementById('delTimeSlot');
                if (tsEl.tagName === 'SELECT') tsEl.selectedIndex = 0; else tsEl.value = '';
                document.getElementById('delNotes').value = '';
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                msg.style.color = 'var(--color-error)';
                msg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (res.error || 'Erreur');
            }
        })
        .catch(() => {
            msg.style.display = 'block';
            msg.style.color = 'var(--color-error)';
            msg.innerHTML = 'Erreur de connexion.';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer la commande';
        });
}
<?php endif; ?>
</script>
</body>
</html>
