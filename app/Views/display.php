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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/display/base.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/display/templates.css">
    <script>if(localStorage.getItem('displayDarkMode')==='true')document.documentElement.classList.add('dark-mode');</script>
</head>
<body class="display-page template-<?= htmlspecialchars($palette) ?> layout-<?= htmlspecialchars($layout) ?>">

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
            <li><a href="#contact">Contact</a></li>
        </ul>

        <div class="header-actions">
            <button class="dark-mode-toggle" onclick="toggleDisplayDarkMode()" title="Mode sombre">
                <i class="fas fa-moon" id="displayDarkIcon"></i>
            </button>
            <button class="display-mobile-toggle" onclick="document.getElementById('displayNav').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

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
                        <?php if ($cat->description): ?>
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
                                        <span class="dish-price"><?= number_format($dish->price, 2, ',', ' ') ?> €</span>
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
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:var(--spacing-md);">
                <?php foreach ($cardImages as $img): ?>
                <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($img->filename) ?>" alt="Carte" style="width:100%;border-radius:var(--radius-md);cursor:pointer;" onclick="openLightbox(this.src)">
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
                    <?php
                    $items = json_decode($menu->items ?? '[]', true) ?: [];
                    foreach ($items as $item):
                    ?>
                    <div class="menu-item">
                        <span class="item-label"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                        <span class="item-value"><?= htmlspecialchars($item['value'] ?? '') ?></span>
                    </div>
                    <?php endforeach; ?>
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
                    <input type="time" id="bookTime" required>
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
            <button type="button" onclick="submitBooking()" class="btn btn-primary btn-block btn-lg" style="background:var(--color-primary);color:#fff;border:none;padding:14px;border-radius:var(--radius-sm);font-size:1rem;font-weight:700;cursor:pointer;width:100%;">
                <i class="fas fa-calendar-check"></i> Réserver
            </button>
            <div id="bookingMessage" style="margin-top:12px;text-align:center;display:none;"></div>
        </div>
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

<!-- Lightbox -->
<div class="lightbox-overlay" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <img src="" alt="Image agrandie" id="lightboxImg">
</div>

<script>
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
if (!localStorage.getItem('display_cookie_consent')) {
    document.getElementById('cookieBanner').classList.add('show');
}
function acceptCookies() {
    localStorage.setItem('display_cookie_consent', 'accepted');
    document.getElementById('cookieBanner').classList.remove('show');
}
function refuseCookies() {
    localStorage.setItem('display_cookie_consent', 'refused');
    document.getElementById('cookieBanner').classList.remove('show');
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

    const data = new FormData();
    data.append('admin_id', '<?= $admin->id ?>');
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
</script>
</body>
</html>
