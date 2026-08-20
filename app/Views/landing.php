<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MenuCraft — Créez le site vitrine de votre restaurant</title>
    <meta name="description" content="MenuCraft : la plateforme SaaS qui permet aux restaurateurs de créer un site vitrine professionnel avec carte en ligne, réservations et plus.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/landing.css">
    <script>
        // Dark mode — chargé tôt pour éviter le flash
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>

<!-- Navigation -->
<nav class="landing-nav" id="mainNav">
    <div class="nav-container">
        <a href="<?= APP_URL ?>" class="nav-logo">
            <i class="fas fa-utensils"></i> MenuCraft
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#features">Fonctionnalités</a></li>
            <li><a href="#pricing">Tarifs</a></li>
            <li><a href="#demo">Démo</a></li>
            <li><a href="#faq">FAQ</a></li>
        </ul>
        <div class="nav-actions">
            <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Mode sombre">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </button>
            <a href="<?= APP_URL ?>?page=login" class="nav-btn nav-btn-ghost">Se connecter</a>
            <?php if (defined('BETA_MODE') && BETA_MODE): ?>
                <a href="mailto:contact.menucraft@gmail.com?subject=Inscription MenuCraft" class="nav-btn nav-btn-primary">Créer mon site</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>?page=auto-register" class="nav-btn nav-btn-primary">Créer mon site</a>
            <?php endif; ?>
            <button class="mobile-nav-toggle" onclick="toggleMobileNav()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-container">
        <h1>Votre restaurant mérite<br>un site <span class="highlight">à la hauteur</span> de votre cuisine</h1>
        <p class="hero-subtitle">Créez un site vitrine professionnel pour votre restaurant en quelques minutes. Carte en ligne, réservations, avis Google et bien plus.</p>
        <?php if (defined('BETA_MODE') && BETA_MODE): ?>
            <p class="hero-price">🎉 <span class="price-current">100% Gratuit pendant la Beta</span></p>
        <?php else: ?>
            <p class="hero-price">À partir de <span class="price-strike">14,99€</span> <span class="price-current">11,99€/mois</span></p>
        <?php endif; ?>
        <div class="hero-cta">
            <?php if (defined('BETA_MODE') && BETA_MODE): ?>
                <a href="mailto:contact.menucraft@gmail.com?subject=Inscription MenuCraft" class="btn-hero btn-hero-primary">
                    <i class="fas fa-rocket"></i> Créer mon site
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>?page=auto-register" class="btn-hero btn-hero-primary">
                    <i class="fas fa-rocket"></i> Créer mon site
                </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>?page=display&slug=demo-restaurant" target="_blank" class="btn-hero btn-hero-secondary">
                <i class="fas fa-play-circle"></i> Voir la démo
            </a>
        </div>
    </div>
</section>

<!-- Social Proof -->
<section class="social-proof">
    <div class="social-proof-inner">
        <span>🍕 La Dolce Vita</span>
        <span>🍣 Sushi Zen</span>
        <span>🥐 Le Petit Bistro</span>
        <span>🍔 Burger Palace</span>
        <span>🌿 Green Garden</span>
        <span>🦐 L'Océan Bleu</span>
    </div>
</section>

<!-- Fonctionnalités -->
<section class="section" id="features">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge"><i class="fas fa-star"></i> Fonctionnalités</div>
            <h2>Tout ce dont votre restaurant a besoin</h2>
            <p>Des outils professionnels pour mettre en valeur votre établissement et attirer de nouveaux clients.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-badge free">Gratuit</span>
                <div class="feature-icon"><i class="fas fa-utensils"></i></div>
                <h3>Carte en ligne</h3>
                <p>Présentez votre carte avec catégories, descriptions, prix et photos de vos plats. Gestion des allergènes incluse.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge free">Gratuit</span>
                <div class="feature-icon"><i class="fas fa-palette"></i></div>
                <h3>Templates personnalisables</h3>
                <p>7 palettes de couleurs et 3 layouts différents pour un site qui correspond à votre identité.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge free">Gratuit</span>
                <div class="feature-icon"><i class="fas fa-clock"></i></div>
                <h3>Horaires & Contact</h3>
                <p>Affichez vos horaires d'ouverture, coordonnées et intégrez Google Maps automatiquement.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge free">Gratuit</span>
                <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                <h3>100% Responsive</h3>
                <p>Votre site s'adapte parfaitement à tous les écrans : mobile, tablette et ordinateur.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge free">Gratuit</span>
                <div class="feature-icon"><i class="fas fa-search"></i></div>
                <h3>SEO optimisé</h3>
                <p>Données structurées Schema.org, meta tags, sitemap XML pour un bon référencement naturel.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge free">Gratuit</span>
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>RGPD & Légal</h3>
                <p>CGU, politique de confidentialité, gestion des cookies. Tout est inclus et conforme.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge premium">Premium</span>
                <div class="feature-icon"><i class="fab fa-google"></i></div>
                <h3>Avis Google</h3>
                <p>Affichez automatiquement vos avis Google sur votre site pour rassurer vos futurs clients.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge premium">Premium</span>
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Statistiques avancées</h3>
                <p>Suivez les visites, les appareils utilisés, les heures de pointe et plus encore.</p>
            </div>
            <div class="feature-card">
                <span class="feature-badge premium">Premium</span>
                <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Réservations en ligne</h3>
                <p>Recevez des réservations directement sur votre site avec confirmation automatique par email.</p>
            </div>
        </div>
    </div>
</section>

<!-- Comment ça marche -->
<section class="section" style="background: var(--color-bg-alt);">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge"><i class="fas fa-magic"></i> Simple</div>
            <h2>Comment ça marche ?</h2>
            <p>Trois étapes suffisent pour mettre votre restaurant en ligne.</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Créez votre compte</h3>
                <p>Inscrivez-vous en quelques secondes avec le nom de votre restaurant.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Personnalisez votre site</h3>
                <p>Ajoutez votre carte, logo, horaires et choisissez votre template.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Publiez et recevez des clients</h3>
                <p>Mettez votre site en ligne et commencez à recevoir des visites et réservations.</p>
            </div>
        </div>
    </div>
</section>

<!-- Tarifs -->
<section class="section pricing-section" id="pricing">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge"><i class="fas fa-tag"></i> Tarifs</div>
            <h2>Un prix juste pour votre visibilité</h2>
        </div>

        <?php if (defined('BETA_MODE') && BETA_MODE): ?>
            <div class="beta-pricing">
                <div class="beta-badge"><i class="fas fa-gift"></i> Beta gratuite</div>
                <h3>100% Gratuit</h3>
                <p>Profitez de toutes les fonctionnalités pendant la période de beta, sans aucun engagement.</p>
                <ul class="beta-features">
                    <li><i class="fas fa-check-circle"></i> Site vitrine complet</li>
                    <li><i class="fas fa-check-circle"></i> Carte en ligne illimitée</li>
                    <li><i class="fas fa-check-circle"></i> 7 templates personnalisables</li>
                    <li><i class="fas fa-check-circle"></i> Avis Google intégrés</li>
                    <li><i class="fas fa-check-circle"></i> Statistiques avancées</li>
                    <li><i class="fas fa-check-circle"></i> Réservations en ligne</li>
                    <li><i class="fas fa-check-circle"></i> Support prioritaire</li>
                </ul>
                <a href="mailto:contact.menucraft@gmail.com?subject=Inscription Beta MenuCraft" class="btn-hero btn-hero-primary">
                    <i class="fas fa-envelope"></i> Demander un accès
                </a>
                <p style="margin-top: 16px; font-size: 0.8rem; color: var(--color-text-muted);">
                    Beta valable jusqu'au <?= defined('BETA_EXPIRES') ? date('d/m/Y', strtotime(BETA_EXPIRES)) : '30/09/2026' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="features-grid" style="grid-template-columns: repeat(2, 1fr); max-width: 800px; margin: 0 auto;">
                <div class="feature-card" style="border: 2px solid var(--color-border); text-align: center; padding: 40px 30px;">
                    <h3 style="font-size: 1.3rem; margin-bottom: 8px;">Basique</h3>
                    <p style="font-size: 2.5rem; font-weight: 800; color: var(--color-primary); margin-bottom: 4px;">11,99€<span style="font-size: 0.9rem; font-weight: 400; color: var(--color-text-muted);">/mois</span></p>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 24px;">ou 9,99€/mois en annuel</p>
                    <ul class="beta-features" style="margin-bottom: 24px;">
                        <li><i class="fas fa-check-circle"></i> Site vitrine complet</li>
                        <li><i class="fas fa-check-circle"></i> Carte en ligne</li>
                        <li><i class="fas fa-check-circle"></i> 7 templates</li>
                        <li><i class="fas fa-check-circle"></i> SEO optimisé</li>
                    </ul>
                    <a href="<?= APP_URL ?>?page=auto-register" class="btn-hero btn-hero-primary" style="width: 100%; justify-content: center;">Commencer</a>
                </div>
                <div class="feature-card" style="border: 2px solid var(--color-primary); text-align: center; padding: 40px 30px; position: relative;">
                    <span class="feature-badge premium" style="top: 12px; right: 12px;">Populaire</span>
                    <h3 style="font-size: 1.3rem; margin-bottom: 8px;">Pack Full</h3>
                    <p style="font-size: 2.5rem; font-weight: 800; color: var(--color-primary); margin-bottom: 4px;">29,99€<span style="font-size: 0.9rem; font-weight: 400; color: var(--color-text-muted);">/mois</span></p>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 24px;">ou 22,99€/mois en annuel</p>
                    <ul class="beta-features" style="margin-bottom: 24px;">
                        <li><i class="fas fa-check-circle"></i> Tout du Basique</li>
                        <li><i class="fas fa-check-circle"></i> Avis Google</li>
                        <li><i class="fas fa-check-circle"></i> Statistiques avancées</li>
                        <li><i class="fas fa-check-circle"></i> Réservations en ligne</li>
                    </ul>
                    <a href="<?= APP_URL ?>?page=auto-register" class="btn-hero btn-hero-primary" style="width: 100%; justify-content: center;">Commencer</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Démo -->
<section class="section" id="demo">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge"><i class="fas fa-play-circle"></i> Démo</div>
            <h2>Découvrez MenuCraft en action</h2>
            <p>Explorez un site de démonstration complet : carte, réservations, horaires… comme si vous étiez client.</p>
        </div>
        <div style="text-align: center;">
            <a href="<?= APP_URL ?>?page=display&slug=demo-restaurant" target="_blank" class="btn-hero btn-hero-primary">
                <i class="fas fa-external-link-alt"></i> Voir le restaurant démo
            </a>
            <p style="margin-top:12px;font-size:0.85rem;color:var(--color-text-muted);">Aucune inscription requise — naviguez librement.</p>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section" id="faq" style="background: var(--color-bg-alt);">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge"><i class="fas fa-question-circle"></i> FAQ</div>
            <h2>Questions fréquentes</h2>
        </div>
        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Dois-je avoir des compétences techniques ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Absolument pas ! MenuCraft est conçu pour être utilisé sans aucune compétence technique. Tout se fait via une interface intuitive.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Puis-je personnaliser l'apparence de mon site ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Oui ! Vous pouvez choisir parmi 7 palettes de couleurs et 3 layouts différents. Ajoutez votre logo, bannière et personnalisez chaque section.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Comment fonctionne la période de beta ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Pendant la beta, toutes les fonctionnalités sont gratuites et accessibles. L'inscription se fait sur invitation. Contactez-nous pour obtenir un accès.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Mon site est-il optimisé pour le référencement Google ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Oui, chaque site généré inclut des meta tags optimisés, des données structurées Schema.org, un sitemap XML et une architecture SEO-friendly.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Puis-je annuler mon abonnement à tout moment ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Oui, sans engagement. Vous pouvez annuler votre abonnement à tout moment depuis votre espace d'administration.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="cta-section">
    <div class="section-container">
        <h2>Prêt à donner de la visibilité à votre restaurant ?</h2>
        <p>Rejoignez les restaurateurs qui font confiance à MenuCraft.</p>
        <?php if (defined('BETA_MODE') && BETA_MODE): ?>
            <a href="mailto:contact.menucraft@gmail.com?subject=Inscription MenuCraft" class="btn-cta">
                <i class="fas fa-rocket"></i> Commencer gratuitement
            </a>
        <?php else: ?>
            <a href="<?= APP_URL ?>?page=auto-register" class="btn-cta">
                <i class="fas fa-rocket"></i> Créer mon site maintenant
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Cookie Banner -->
<div class="cookie-banner" id="cookieBanner">
    <div class="cookie-banner-inner">
        <p>🍪 Ce site utilise des cookies pour améliorer votre expérience. En continuant, vous acceptez notre <a href="<?= APP_URL ?>?page=legal&section=cookies">politique de cookies</a>.</p>
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

<!-- Footer -->
<footer class="landing-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3><i class="fas fa-utensils"></i> MenuCraft</h3>
                <p>La plateforme SaaS qui permet aux restaurateurs de créer et gérer leur site vitrine professionnel.</p>
                <p style="margin-top: 12px;"><i class="fas fa-envelope" style="margin-right: 6px;"></i> contact.menucraft@gmail.com</p>
            </div>
            <div class="footer-links">
                <h4>Produit</h4>
                <ul>
                    <li><a href="#features">Fonctionnalités</a></li>
                    <li><a href="#pricing">Tarifs</a></li>
                    <li><a href="#demo">Démo</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Légal</h4>
                <ul>
                    <li><a href="<?= APP_URL ?>?page=legal&section=cgu">CGU</a></li>
                    <li><a href="<?= APP_URL ?>?page=legal&section=privacy">Confidentialité</a></li>
                    <li><a href="<?= APP_URL ?>?page=legal&section=cookies">Cookies</a></li>
                    <li><a href="<?= APP_URL ?>?page=legal&section=legal">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> MenuCraft — Tous droits réservés</p>
        </div>
    </div>
</footer>

<!-- Scroll navigation arrows -->
<div class="scroll-arrows">
    <button class="scroll-arrow scroll-arrow-up" id="scrollUpBtn" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Haut de page">
        <i class="fas fa-chevron-up"></i>
    </button>
    <button class="scroll-arrow scroll-arrow-down" id="scrollDownBtn" onclick="window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'})" title="Bas de page">
        <i class="fas fa-chevron-down"></i>
    </button>
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

// Dark mode toggle
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark-mode');
    const isDark = document.documentElement.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    document.getElementById('darkModeIcon').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}

// Update icon on load
if (document.documentElement.classList.contains('dark-mode')) {
    document.getElementById('darkModeIcon').className = 'fas fa-sun';
}

// Nav scroll effect
window.addEventListener('scroll', () => {
    document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 40);
});

// FAQ accordion
function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const wasOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
    if (!wasOpen) item.classList.add('open');
}

// Mobile nav
function toggleMobileNav() {
    document.getElementById('navLinks').classList.toggle('open');
}

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('navLinks').classList.remove('open');
        }
    });
});

// Cookies
const cookieReopenBtn = document.getElementById('cookieReopenBtn');
function showReopenBtn() { if (cookieReopenBtn) cookieReopenBtn.classList.add('visible'); }
function hideReopenBtn() { if (cookieReopenBtn) cookieReopenBtn.classList.remove('visible'); }
if (!localStorage.getItem('landing_cookie_consent')) {
    document.getElementById('cookieBanner').classList.add('show');
} else {
    showReopenBtn();
}
function acceptCookies() {
    localStorage.setItem('landing_cookie_consent', 'accepted');
    document.getElementById('cookieBanner').classList.remove('show');
    showReopenBtn();
}
function refuseCookies() {
    localStorage.setItem('landing_cookie_consent', 'refused');
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
    localStorage.setItem('landing_cookie_consent', 'custom');
    localStorage.setItem('cookie_analytics', analytics);
    localStorage.setItem('cookie_marketing', marketing);
    document.getElementById('cookieBanner').classList.remove('show');
    showReopenBtn();
}
function reopenCookieBanner() {
    hideReopenBtn();
    document.getElementById('cookieBanner').classList.add('show');
}
</script>
</body>
</html>
