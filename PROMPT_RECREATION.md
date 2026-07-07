# PROMPT — Recréation complète de MenuCraft

> **Objectif** : Donner ce prompt à une IA pour qu'elle recrée l'application MenuCraft de zéro, proprement.

---

## CONTEXTE GLOBAL

Crée une application web **MenuCraft** : une plateforme SaaS permettant aux restaurateurs de créer et gérer un site vitrine pour leur restaurant. L'application est développée en **PHP 8.x procédural avec classes** (architecture MVC simplifiée), **MySQL** via PDO, **Apache** avec `.htaccess`, **CSS custom** sans framework, **Vanilla JS**.

---

## STACK TECHNIQUE

| Composant | Technologie |
|-----------|------------|
| Backend | PHP 8.x (procédural avec classes, pas de framework) |
| BDD | MySQL 8.x via PDO (requêtes préparées exclusivement) |
| Serveur | Apache (WAMP/LAMP) avec `.htaccess` pour URL rewriting |
| Paiement | Stripe API via cURL natif (pas de SDK) |
| Emails | `mail()` natif PHP (MailHog en dev) |
| CSS | CSS custom avec variables CSS (pas de Tailwind, pas de Bootstrap) |
| JS | Vanilla JS + SweetAlert2 + Chart.js + SortableJS |
| Icônes | Font Awesome 6.5 |
| Fonts | Google Fonts (Inter pour le corps, Playfair Display pour certains templates) |
| Tests | PHPUnit 10.5 + k6 (load testing) |

---

## ARCHITECTURE

### Pattern MVC simplifié

```
MenuCraft/
├── public/                          # DOCUMENT ROOT Apache
│   ├── index.php                    # Point d'entrée unique + routeur
│   ├── .htaccess                    # Réécriture URL vers index.php
│   ├── robots.txt
│   ├── assets/
│   │   ├── css/
│   │   │   ├── shared/_variables.css    # Variables CSS globales
│   │   │   ├── landing.css              # Styles page landing
│   │   │   ├── admin.css                # Import master admin (importe tous les sous-fichiers)
│   │   │   └── display/                 # Styles vitrine restaurant
│   │   │       ├── _display-variables.css
│   │   │       ├── base.css, header.css, banner.css, carte.css...
│   │   │       ├── template-modern.css, template-elegant.css...  (palettes)
│   │   │       └── layout-bistro.css, layout-ocean.css           (layouts)
│   │   ├── js/
│   │   │   ├── landing/
│   │   │   ├── admin/
│   │   │   ├── display/
│   │   │   ├── effects/
│   │   │   └── sections/               # JS par page admin
│   │   ├── uploads/                     # Logos, bannières, images plats
│   │   ├── favicon.svg
│   │   └── logo.png
│
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php           # Classe parent (PDO, CSRF, flash, render, headers sécurité)
│   │   ├── AdminController.php          # Auth, inscription, dashboard
│   │   ├── CardController.php           # Gestion carte (catégories, plats, images, menus du jour)
│   │   ├── ContactController.php        # Contact du restaurant
│   │   ├── DisplayController.php        # Rendu site vitrine public
│   │   ├── FeedbackController.php       # Formulaire feedback beta
│   │   ├── FloorPlanController.php      # Plan de salle drag & drop
│   │   ├── LegalController.php          # Pages légales (CGU, RGPD, Cookies, Mentions)
│   │   ├── LogoBannerController.php     # Upload logo/bannière
│   │   ├── ReservationController.php    # Réservations en ligne (premium)
│   │   ├── ServicesController.php       # Services, paiements, réseaux sociaux
│   │   ├── SettingsController.php       # Paramètres (profil, mdp, options, template, premium)
│   │   ├── SitemapController.php        # Génération sitemap.xml
│   │   ├── StatsController.php          # Statistiques avancées (premium)
│   │   ├── StripeController.php         # Paiement Stripe (checkout, webhook, succès)
│   │   ├── ClientManagementController.php  # Gestion clients (SUPER_ADMIN only)
│   │   └── NotificationStreamController.php # SSE notifications temps réel
│   │
│   ├── Models/
│   │   ├── Admin.php                    # Utilisateurs (CRUD, auth, fill, findById, findByUsername)
│   │   ├── Allergene.php                # 14 allergènes réglementaires
│   │   ├── BillingCycle.php             # Calcul prorata facturation
│   │   ├── CardImage.php                # Images carte (mode images)
│   │   ├── Category.php                 # Catégories de plats
│   │   ├── ClientSubscription.php       # Abonnements clients
│   │   ├── Contact.php                  # Infos contact restaurant
│   │   ├── DailyMenu.php               # Menus du jour / formules
│   │   ├── DemoToken.php                # Tokens démo + clonage complet
│   │   ├── Dish.php                     # Plats (CRUD)
│   │   ├── Floor.php                    # Étages plan de salle
│   │   ├── GoogleReviews.php            # Avis Google Places (API + cache)
│   │   ├── OptionModel.php              # Options clé/valeur par admin
│   │   ├── PremiumFeature.php           # Features premium (activation/expiration)
│   │   ├── Reservation.php              # Réservations clients
│   │   ├── Restaurant.php               # Restaurant (slug, timestamps)
│   │   ├── RestaurantElement.php        # Éléments déco plan de salle
│   │   ├── RestaurantTable.php          # Tables plan de salle
│   │   └── SiteVisit.php               # Tracking visites (anonymisé)
│   │
│   ├── Helpers/
│   │   ├── Mailer.php                   # Envoi emails HTML (mail() + logs)
│   │   ├── RateLimiter.php              # Anti-brute-force (fichiers JSON/IP)
│   │   ├── Validator.php                # Validation formulaires (rules)
│   │   ├── FormHelper.php               # Helpers formulaires
│   │   └── old.php                      # Helper "old input" pour re-remplir les formulaires
│   │
│   ├── Services/
│   │   └── NotificationService.php      # Notifications email groupées
│   │
│   └── Views/
│       ├── landing.php                  # Page landing commerciale
│       ├── display.php                  # Layout vitrine restaurant
│       ├── display/                     # Composants vitrine (head, header, banner, carte, etc.)
│       ├── admin/                       # Pages back-office
│       ├── partials/                    # Header/footer admin réutilisables
│       └── errors/                      # 404, demo-expired
│
├── cron/
│   ├── auto_complete_reservations.php   # Marquage auto réservations terminées (chaque 15 min)
│   ├── send_reminders.php              # Rappels mensuels mise à jour carte
│   └── logs/                           # Logs CRON
│
├── database/
│   └── schema.sql                      # Schéma complet de la BDD
│
├── storage/
│   ├── .htaccess                       # Deny all
│   └── rate_limits/                    # Fichiers JSON du rate limiter
│
├── tests/
│   ├── bootstrap.php                   # Setup BDD test + schéma
│   ├── Unit/Models/                    # Tests unitaires modèles
│   ├── Functional/                     # Tests routes HTTP
│   ├── Security/                       # Tests SQL injection + XSS
│   └── load/k6-load-test.js           # Tests de charge
│
├── config.php                          # Configuration (gitignored)
├── config.example.php                  # Template de config
├── .htaccess                           # Protection racine + rewrite
├── .gitignore
├── composer.json
├── phpunit.xml
└── README.md
```

---

## BASE DE DONNÉES (24 tables)

### Table `restaurants`
```sql
id INT PK AUTO_INCREMENT
name VARCHAR(255) NOT NULL
slug VARCHAR(255) UNIQUE NOT NULL
created_at DATETIME DEFAULT NOW()
updated_at DATETIME ON UPDATE NOW()
```

### Table `admins`
```sql
id INT PK AUTO_INCREMENT
username VARCHAR(100) UNIQUE NOT NULL
email VARCHAR(255) NOT NULL
password VARCHAR(255) NOT NULL          -- bcrypt
role ENUM('ADMIN', 'SUPER_ADMIN') DEFAULT 'ADMIN'
restaurant_name VARCHAR(255)
restaurant_id INT FK → restaurants.id ON DELETE SET NULL
carte_mode ENUM('editable', 'images') DEFAULT 'editable'
reset_token VARCHAR(255)
reset_token_expiry DATETIME
email_verified TINYINT(1) DEFAULT 0
verification_token VARCHAR(255)
created_at DATETIME DEFAULT NOW()
updated_at DATETIME ON UPDATE NOW()
```

### Table `categories`
```sql
id INT PK AUTO_INCREMENT
admin_id INT FK → admins.id CASCADE
name VARCHAR(255) NOT NULL
description TEXT
image VARCHAR(500)
display_order INT DEFAULT 0
created_at, updated_at DATETIME
```

### Table `plats`
```sql
id INT PK AUTO_INCREMENT
category_id INT FK → categories.id CASCADE
name VARCHAR(255) NOT NULL
description TEXT
price DECIMAL(8,2) NOT NULL
image VARCHAR(500)
display_order INT DEFAULT 0
is_active TINYINT(1) DEFAULT 1
created_at DATETIME
```

### Table `allergenes` (14 entrées pré-remplies)
```sql
id INT PK, nom VARCHAR(100), icone VARCHAR(100)
-- Gluten, Crustacés, Œufs, Poissons, Arachides, Soja, Lait, Fruits à coque, Céleri, Moutarde, Sésame, Sulfites, Lupin, Mollusques
```

### Table `plat_allergenes` (pivot)
```sql
plat_id INT FK → plats.id CASCADE
allergene_id INT FK → allergenes.id CASCADE
```

### Table `card_images` (mode images)
```sql
id INT PK, admin_id INT FK, filename VARCHAR(500), display_order INT, uploaded_at DATETIME
```

### Table `daily_menus` (menus du jour / formules)
```sql
id INT PK, admin_id INT FK
title VARCHAR(255), description TEXT, price DECIMAL(8,2)
items JSON                              -- [{label: "Entrée", value: "Salade César"}, ...]
display_order INT, is_active TINYINT(1) DEFAULT 1
created_at, updated_at DATETIME
```

### Table `contact`
```sql
id INT PK, admin_id INT UNIQUE FK
telephone VARCHAR(50), email VARCHAR(255), adresse TEXT, horaires TEXT
updated_at DATETIME
```

### Table `logos`
```sql
id INT PK, admin_id INT UNIQUE FK, filename VARCHAR(500), uploaded_at DATETIME
```

### Table `banners`
```sql
id INT PK, admin_id INT UNIQUE FK, filename VARCHAR(500), text TEXT, uploaded_at DATETIME
```

### Table `admin_options` (clé/valeur)
```sql
id INT PK, admin_id INT FK
option_name VARCHAR(100) NOT NULL
option_value TEXT
UNIQUE KEY (admin_id, option_name)
```

**Options principales :**
- `site_online` (0/1) — site en ligne ou maintenance
- `site_palette` — classic, modern, elegant, nature, rose, bistro, ocean
- `site_layout` — standard, bistro, ocean
- `email_notifications` (0/1) — recevoir notifs email
- `mail_reminder` (0/1) — rappel mensuel carte
- `google_place_id`, `google_api_key`, `google_reviews_enabled`
- `booking_enabled`, `booking_min_party`, `booking_max_party`, `booking_advance_days`, `booking_max_per_slot`, `booking_time_slots` (JSON), `booking_closed_days` (JSON), `booking_message`, `booking_auto_complete`
- `closure_dates` (JSON array de dates de fermeture exceptionnelle)
- `service_sur_place`, `service_a_emporter`, `service_livraison_ubereats`, `service_livraison_etablissement`, `service_wifi`, `service_climatisation`, `service_pmr`, `service_animaux`
- `payment_visa`, `payment_mastercard`, `payment_cb`, `payment_especes`, `payment_cheques`, `payment_tickets_restaurant`
- `social_instagram`, `social_facebook`, `social_x`, `social_tiktok`, `social_snapchat`
- `hide_dark_mode`, `hide_tour` (masquage boutons UI)

### Table `invitations`
```sql
id INT PK, email VARCHAR(255), restaurant_name VARCHAR(255)
token VARCHAR(255) UNIQUE, expiry DATETIME, used TINYINT(1) DEFAULT 0
created_at DATETIME
```

### Table `demo_tokens`
```sql
id INT PK, token VARCHAR(255) UNIQUE
admin_id INT FK → admins.id CASCADE       -- pointe vers le CLONE
clone_admin_id INT, clone_restaurant_id INT
label VARCHAR(255), expires_at DATETIME NOT NULL
created_by INT FK → admins.id SET NULL
created_at DATETIME
```

### Table `client_subscriptions`
```sql
id INT PK, admin_id INT UNIQUE FK
plan_type VARCHAR(50) DEFAULT 'basique'
status ENUM('active', 'inactive', 'cancelled', 'expired') DEFAULT 'inactive'
price_per_month DECIMAL(8,2)
features_enabled JSON
started_at DATETIME, expires_at DATETIME
billing_cycle_day INT DEFAULT 15
next_billing_date DATE
stripe_session_id VARCHAR(255)
created_by INT, created_at, updated_at DATETIME
```

### Table `premium_features`
```sql
id INT PK, admin_id INT FK
feature_name VARCHAR(100)                -- google_reviews, advanced_analytics, online_booking, delivery_integration
is_active TINYINT(1) DEFAULT 0
activated_at DATETIME, expires_at DATETIME, cancelled_at DATETIME
UNIQUE KEY (admin_id, feature_name)
```

### Table `reservations`
```sql
id INT PK, admin_id INT FK
customer_name VARCHAR(255), customer_phone VARCHAR(50), customer_email VARCHAR(255)
reservation_date DATE, reservation_time TIME
party_size INT DEFAULT 2
special_requests TEXT
status ENUM('pending', 'confirmed', 'rejected', 'completed', 'cancelled', 'no_show') DEFAULT 'pending'
created_at, updated_at DATETIME
INDEX (admin_id, status), INDEX (admin_id, reservation_date)
```

### Table `site_visits`
```sql
id INT PK, admin_id INT FK
visitor_hash VARCHAR(64)                 -- SHA256(IP + User-Agent) pour anonymisation
user_agent VARCHAR(512), referrer VARCHAR(1024)
device_type VARCHAR(20), browser VARCHAR(50), page_path VARCHAR(255)
visited_at DATETIME
INDEX (admin_id, visited_at), INDEX (admin_id, visitor_hash)
```

### Table `google_reviews_cache`
```sql
id INT PK, place_id VARCHAR(255) UNIQUE, data LONGTEXT (JSON), cached_at DATETIME
```

### Table `floors`
```sql
id INT PK, admin_id INT FK, name VARCHAR(100) DEFAULT 'Salle principale', display_order INT
```

### Table `restaurant_tables`
```sql
id INT PK, floor_id INT FK → floors.id CASCADE
table_number VARCHAR(20), seats INT DEFAULT 4
x FLOAT, y FLOAT, width FLOAT, height FLOAT
shape ENUM('square', 'round') DEFAULT 'square', rotation FLOAT
```

### Table `restaurant_elements`
```sql
id INT PK, floor_id INT FK → floors.id CASCADE
element_type VARCHAR(50)                 -- bar, cuisine, entrée, WC, escalier...
x FLOAT, y FLOAT, width FLOAT, height FLOAT, rotation FLOAT
```

### Table `feedbacks`
```sql
id INT PK, admin_id INT FK
name VARCHAR(255), email VARCHAR(255), rating INT
ease_of_use VARCHAR(50), favorite_feature TEXT, improvements TEXT, comments TEXT
created_at DATETIME
```

### Table `password_resets`
```sql
id INT PK, email VARCHAR(255), token VARCHAR(255), expires_at DATETIME, used TINYINT(1)
INDEX (token), INDEX (email)
```

---

## CONFIGURATION (`config.php`)

```php
<?php
$pdo = new PDO("mysql:host=localhost;dbname=menucraft;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

define('SITE_URL', 'http://localhost/MenuCraft/public');
define('BASE_PATH', __DIR__);

// Stripe
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// Mode Beta (true = toutes les features premium gratuites)
define('BETA_MODE', true);
define('BETA_EXPIRES', '2026-09-30');
```

---

## ROUTEUR (`public/index.php`)

Point d'entrée unique. Dispatch selon `$_GET['page']` :

### Routes publiques (pas d'auth)
| Route | Action |
|-------|--------|
| `landing` (ou vide) | Affiche `landing.php` |
| `login` | `AdminController->login()` |
| `auto-register` | `AdminController->autoRegister()` |
| `register` | `AdminController->register()` (via invitation) |
| `verify-email` | Vérification token email |
| `reset-password` | Demande reset MDP |
| `reset-password-admin` | Formulaire nouveau MDP |
| `display` | `DisplayController->show()` (slug en param) |
| `demo` | Accès démo via token |
| `public-booking` | `ReservationController->publicBooking()` |
| `legal` | `LegalController->show()` (section=cgu/privacy/cookies/legal) |
| `sitemap.xml` | `SitemapController->generate()` |
| `stripe-webhook` | `StripeController->handleWebhook()` |

### Routes authentifiées (ADMIN + SUPER_ADMIN)
| Route | Action |
|-------|--------|
| `dashboard` | `AdminController->dashboard()` |
| `logout` | `AdminController->logout()` |
| `edit-card` | `CardController->show()` |
| `save-category`, `delete-category` | CRUD catégorie |
| `save-dish`, `delete-dish` | CRUD plat |
| `reorder-categories`, `reorder-dishes` | Réordonnancement drag&drop |
| `upload-card-image`, `delete-card-image` | Mode images |
| `save-daily-menu`, `delete-daily-menu`, `toggle-daily-menu`, `reorder-daily-menus` | Menus du jour |
| `view-card` | Prévisualisation |
| `edit-contact` | `ContactController->edit()` |
| `edit-logo-banner` | `LogoBannerController->show()` |
| `upload-logo`, `delete-logo`, `upload-banner`, `delete-banner`, `save-banner-text` | Gestion médias |
| `edit-services`, `save-services` | Services/paiements/réseaux |
| `edit-template` | Choix palette + layout |
| `settings` | `SettingsController->show()` |
| `update-profile`, `update-password`, `update-options`, `update-template` | Sauvegardes paramètres |
| `floor-plan`, `floor-plan-save` | Plan de salle |
| `feedback`, `submit-feedback` | Feedback beta |
| `notification-stream` | SSE temps réel |

### Routes ADMIN uniquement (abonnement requis)
| Route | Action |
|-------|--------|
| `stripe-checkout` | `StripeController->createCheckout()` |
| `stripe-success` | `StripeController->handleSuccess()` |
| `stripe-cancel`, `stripe-reactivate` | Gestion abonnement |

### Routes premium (feature activée requise)
| Route | Action |
|-------|--------|
| `stats`, `stats-data` | Statistiques avancées (feature `advanced_analytics`) |
| `reservations`, `reservation-update-status` | Réservations (feature `online_booking`) |

### Routes SUPER_ADMIN uniquement
| Route | Action |
|-------|--------|
| `send-invitation` | Envoi d'invitation |
| `manage-clients` | Gestion des clients |
| `activate-subscription`, `deactivate-subscription` | Activer/désactiver abo client |
| `feedback-dashboard` | Dashboard feedbacks |

---

## SÉCURITÉ

### BaseController (classe parent de tous les controllers)
```php
class BaseController {
    protected PDO $pdo;

    public function __construct(PDO $pdo) { ... }

    // CSRF
    protected function getCsrfToken(): string      // Génère et stocke en session
    protected function verifyCsrfToken(): void     // Vérifie $_POST['csrf_token']

    // Flash messages
    protected function flash(string $type, string $message): void
    protected function getFlash(): ?array

    // Rendu
    protected function render(string $view, array $data = []): void

    // Headers de sécurité (appelé automatiquement)
    // X-Content-Type-Options: nosniff
    // X-Frame-Options: DENY
    // X-XSS-Protection: 1; mode=block
    // Referrer-Policy: strict-origin-when-cross-origin
    // Strict-Transport-Security si HTTPS

    // Mode démo
    protected function blockIfDemo(): void         // Redirige si $_SESSION['demo_mode']

    // Nombre de réservations en attente (pour badge notification)
    protected int $pendingReservationsCount;
}
```

### Authentification
- Mots de passe : `password_hash()` bcrypt + `password_verify()`
- Session : `session_regenerate_id(true)` après login
- Variables session : `admin_logged`, `admin_id`, `admin_name`, `username`
- Vérification email obligatoire avant connexion

### Rate Limiting (fichier JSON par IP)
- Login : **5 tentatives / 15 minutes**
- Réservation publique : **10 / heure**
- Feedback : **3 / mois**
- Stockage : `storage/rate_limits/{action}_{ip_hash}.json`

### Protection XSS
- `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` systématique dans les vues

### Protection SQL Injection
- PDO avec requêtes préparées exclusivement (paramètres bindés `:param`)

---

## RÔLES ET PERMISSIONS

### PUBLIC (non connecté)
- Page landing, login, inscription, reset MDP
- Site vitrine restaurant (via slug)
- Accès démo (via token)
- Réservation publique
- Pages légales, sitemap

### ADMIN (restaurateur connecté)
- Dashboard
- Gestion carte (catégories, plats, images, menus du jour)
- Gestion contact, logo, bannière
- Services, paiements, réseaux sociaux
- Choix template (palette + layout)
- Paramètres (profil, MDP, options générales)
- Plan de salle
- Feedback beta
- Paiement Stripe
- Features premium (si payées ou BETA_MODE)

### SUPER_ADMIN (gestionnaire plateforme)
Tout ce que ADMIN peut + :
- Envoi d'invitations
- Gestion des clients (activer/désactiver abonnements)
- Dashboard feedbacks
- Génération de liens démo
- Accès prorata Stripe

**Restrictions SUPER_ADMIN** : pas d'accès aux sections premium/subscriptions/google-reviews/stats/online-booking/delivery (redirigé vers profil).

---

## PAGE LANDING (`?page=landing`)

Page commerciale publique avec les sections suivantes :

### 1. Navigation
- Logo "MenuCraft" + icône fa-utensils
- Liens : Fonctionnalités, Tarifs, Démo, FAQ
- Boutons : "Se connecter" → login, "Créer mon site" → auto-register (ou mailto en beta)
- Dark mode toggle

### 2. Hero Section
- Titre : "Votre restaurant mérite un site à la hauteur de votre cuisine"
- Sous-titre avec prix barré (selon BETA_MODE)
- 2 CTA : "Créer mon site" + "Voir la démo"
- Mockup visuel du produit

### 3. Social Proof
- Noms fictifs de restaurants utilisant la plateforme

### 4. Fonctionnalités (9 cards en grille)
- Carte en ligne (Gratuit)
- Templates personnalisables — 7 palettes, 3 layouts (Gratuit)
- Horaires & Contact (Gratuit)
- 100% Responsive (Gratuit)
- SEO optimisé (Gratuit)
- RGPD & Légal (Gratuit)
- Avis Google (Premium)
- Statistiques avancées (Premium)
- Réservations en ligne (Premium)

### 5. Comment ça marche (3 étapes)
1. Créer votre compte
2. Personnaliser votre site
3. Publier et recevoir des clients

### 6. Tarifs (conditionnel)

**En mode BETA (`BETA_MODE === true`)** :
- Bloc unique "100% Gratuit pendant la Beta"
- Badge "Beta gratuite — Toutes les fonctionnalités incluses"
- Pas de prix affiché

**En mode normal** :
- Abonnement Basique : **11,99€/mois** (9,99€ annuel) — toggle mensuel/annuel
- Pack Full : **29,99€/mois** (22,99€ annuel) — avec durées 1/3/12 mois
- Options à la carte : Avis Google 3,99€, Stats 3,99€, Réservations 10,99€, Livraison 3,99€

### 7. Démo
- CTA "Voir la démo en direct"
- Formulaire demande de démo privée (mailto)

### 8. FAQ (accordéon)
- Questions fréquentes sur le service, tarifs, fonctionnalités

### 9. CTA final
- Dernier appel à l'inscription

### 10. Footer
- Liens légaux (CGU, RGPD, Cookies, Mentions)
- Email contact : contact.menucraft@gmail.com
- Copyright

### Comportement Beta
Quand `BETA_MODE === true` :
- Bouton "Créer mon site" → `mailto:contact.menucraft@gmail.com`
- Section tarifs → bloc "Beta Gratuite"
- Tous les prix masqués

---

## SYSTÈME D'AUTHENTIFICATION

### Inscription libre (`?page=auto-register`)
1. Formulaire : username, email, nom du restaurant, mot de passe + confirmation
2. Validation : username ≥ 3 chars, email valide, password robuste (min 8, 1 majuscule, 1 chiffre, 1 spécial)
3. Création compte via `Admin::createAccountDirect()`
4. Création d'un restaurant avec slug (slugify du nom)
5. Envoi email de vérification avec token unique
6. Création abonnement basique inactif dans `client_subscriptions`
7. En BETA : création abonnement premium gratuit + 4 features activées
8. Redirection login avec message succès

### Inscription par invitation (`?page=register`)
1. SUPER_ADMIN envoie une invitation (email + nom restaurant)
2. Invité reçoit email avec lien contenant token
3. Formulaire : username + mot de passe (email et restaurant pré-remplis)
4. Validation token (non expiré, non utilisé)
5. Création compte via `Admin::createAccount()`

### Vérification email (`?page=verify-email`)
- Token validé → `email_verified = 1`
- Suppression du `verification_token`
- Email de confirmation envoyé

### Connexion (`?page=login`)
1. Rate limiting : 5 tentatives / 15 min par IP
2. `password_verify()` avec hash stocké
3. Vérification `email_verified == 1`
4. `session_regenerate_id(true)`
5. Session : `admin_logged=true`, `admin_id`, `admin_name`, `username`
6. Redirection dashboard

### Reset mot de passe
1. **Demande** : email → génération token 1h → envoi email
2. **Formulaire** : token validé → nouveau password → update BDD

### Déconnexion
- Mode normal : `session_destroy()` → redirection login
- Mode démo : → `demo-logout` (nettoyage clone)

---

## FONCTIONNALITÉS ADMIN

### Dashboard (`?page=dashboard`)
- Nom du restaurant, slug, dernière mise à jour
- Lien vers le site vitrine public
- Accès rapides : Carte, Contact, Logo, Services, Paramètres
- Badge réservations en attente (si feature premium activée)
- Statut abonnement
- Bouton "Mettre en ligne / hors ligne"

### Gestion de la carte (`?page=edit-card`)

**Deux modes (stocké dans `admins.carte_mode`)** :

#### Mode "editable"
- **Catégories** : CRUD, drag & drop (SortableJS) pour réordonner, image optionnelle
- **Plats** par catégorie : nom, description, prix, image, allergènes (14 checkbox), actif/inactif, drag & drop
- **Allergènes** : 14 réglementaires avec icônes Font Awesome

#### Mode "images"
- Upload de photos de la carte physique
- Ajout, suppression, réordonnancement

#### Menus du jour / Formules
- CRUD : titre, description, prix, items (tableau JSON : [{label, value}])
- Activation/désactivation individuelle
- Réordonnancement
- Affichés sur la vitrine quand actifs

### Contact (`?page=edit-contact`)
- Téléphone, email, adresse, horaires d'ouverture
- Validation serveur, support AJAX + fallback formulaire classique
- Création automatique si inexistant

### Logo et bannière (`?page=edit-logo-banner`)
- **Logo** : upload (jpg, png, webp, jpeg), suppression, preview
- **Bannière** : upload image, suppression, texte personnalisé
- Stockage dans `public/assets/uploads/` (ou `public/assets/logos/` et `public/assets/banners/`)

### Services, paiements, réseaux (`?page=edit-services`)
- **Services** : sur place, à emporter, livraison (Uber Eats / propre), WiFi, clim, PMR, animaux
- **Paiements** : Visa, Mastercard, CB, espèces, chèques, tickets restaurant
- **Réseaux** : Instagram, Facebook, X, TikTok, Snapchat (URLs)
- Tout stocké comme options dans `admin_options`

### Template (`?page=edit-template`)

#### 7 Palettes de couleurs
Chaque palette = un fichier CSS qui override les variables `--color-*` sur `body.template-{name}` :

| Palette | Description | Couleur primaire |
|---------|-------------|-----------------|
| `classic` | Tons chauds (défaut) | #b45309 (ambre) |
| `modern` | Minimaliste bleu | #2563eb |
| `elegant` | Sombre + dorés, serif | #d4a853 (fond noir #12121f) |
| `nature` | Vert naturel | vert |
| `rose` | Pastel féminin | rose |
| `bistro` | Rouge traditionnel | rouge |
| `ocean` | Bleu marin | bleu turquoise |

#### 3 Layouts
| Layout | Description |
|--------|------------|
| `standard` | Vertical classique |
| `bistro` | Accent ambiance/photos |
| `ocean` | Éléments visuels aquatiques |

- Body reçoit : `<body class="template-{palette} layout-{layout}">`
- Preview live : `?preview_palette=X&preview_layout=Y`
- Palette et layout stockés séparément dans `admin_options`

### Paramètres (`?page=settings`)

Sections accessibles via `?section=` :
- `profile` : username, email, nom restaurant
- `password` : changement MDP
- `general` : site en ligne, notifications email, rappel mensuel
- `closure-dates` : dates de fermeture exceptionnelle (JSON)
- `premium` : gestion options premium + abonnement Stripe
- `google-reviews` : Place ID + API Key
- `stats` : lien vers statistiques
- `online-booking` : configuration réservations
- `delivery` : configuration livraison (placeholder actuellement)
- `subscriptions` : détails abonnement

### Plan de salle (`?page=floor-plan`)
- Éditeur visuel drag & drop
- Multi-étages (CRUD)
- Tables : numéro, places, position (x, y), dimensions, forme (ronde/carrée), rotation
- Éléments décoratifs : bar, cuisine, entrée, WC... position, dimensions
- Étage par défaut créé si aucun n'existe
- Nécessite abonnement actif

### Feedback beta (`?page=feedback`)
- Formulaire : nom, email, note 1-5, facilité d'utilisation, fonctionnalité préférée, améliorations, commentaires
- Limite : 3 soumissions / mois / admin

---

## FONCTIONNALITÉS SUPER_ADMIN

### Dashboard enrichi
- Liste des **tokens de démo** actifs (liens + dates expiration)
- Bouton "Générer un lien de démo"
- Statut restaurant démo (`demo-menucraft`)

### Envoi d'invitations (`?page=send-invitation`)
- Formulaire : email, nom du restaurant
- Génération token + envoi email

### Gestion des clients (`?page=manage-clients`)
- Liste tous les clients avec : nom, email, restaurant, date inscription, statut abo, features premium
- Actions : activer/désactiver abonnement premium manuellement

### Dashboard feedbacks (`?page=feedback-dashboard`)
- Tous les feedbacks soumis (notes, commentaires, stats)

### Génération de démos
- Clone complet du restaurant `demo-menucraft`
- Token validité : 3 jours
- Nettoyage automatique à expiration

---

## SITE VITRINE RESTAURANT (`?page=display&slug=X`)

### Flux de rendu (DisplayController)
1. Récupérer restaurant par slug
2. Vérifier existence restaurant + admin
3. Vérifier abonnement actif (sauf SUPER_ADMIN / démo)
4. Charger toutes les données (logo, bannière, carte, menus, options, contact, avis, réservations, palette, layout)
5. Gérer maintenance (`site_online`)
6. Mode preview pour admin propriétaire connecté
7. Tracker la visite (hors preview)
8. Rendu avec toutes les données

### Composants de la vitrine
1. **Head** : Meta tags, Open Graph, Schema.org Restaurant, CSS conditionnel (palette + layout)
2. **Header** : Logo, nom restaurant, navigation (Carte, Menus, Services, Réservation, Contact)
3. **Bannière** : Image + texte personnalisé
4. **Carte** : Affichage catégories/plats (mode editable) OU images (mode images) + allergènes
5. **Menus du jour** : Cards avec titre, description, prix, items (si actifs)
6. **Services** : Icônes services + paiements + liens réseaux sociaux
7. **Avis Google** : Note moyenne + 5 derniers avis (si premium activé)
8. **Réservation** : Formulaire public (si premium booking activé)
9. **Footer** : Contact, horaires, Google Maps embed, liens légaux
10. **Cookies** : Bannière RGPD
11. **Lightbox** : Zoom images

### Logique visibilité
```
Pas d'abonnement actif ET pas SUPER_ADMIN → hors ligne
site_online = 0 → maintenance (sauf admin propriétaire / SUPER_ADMIN)
SUPER_ADMIN → toujours visible
Admin propriétaire connecté → mode preview
Mode démo → toujours visible
```

### Dark mode vitrine
- Supporté via `html.dark-mode` qui override les variables CSS
- Toggle accessible dans le header

---

## SYSTÈME DE PAIEMENT STRIPE

### Intégration
- API Stripe via **cURL natif** (pas de SDK PHP)
- Mode test : clés `sk_test_...` / `pk_test_...`
- Carte test : `4242 4242 4242 4242`

### Types de checkout

| Type | Metadata | Description |
|------|----------|-------------|
| `basique` | Abonnement de base 11,99€/mois |
| `basique_premium` | Basique + options sélectionnées |
| `premium` | Options à la carte seulement |
| `pack_full` | Tout inclus 29,99€/mois |

### Tarification

| Offre | Mensuel | Annuel |
|-------|---------|--------|
| Basique | 11,99€ | 9,99€/mois |
| Avis Google | 3,99€ | 2,99€/mois |
| Statistiques | 3,99€ | 2,99€/mois |
| Réservations | 10,99€ | 8,99€/mois |
| Livraison | 3,99€ | 2,99€/mois |
| **Pack Full** | **29,99€** | **22,99€/mois** |

### Flux
1. Admin choisit son plan (`?page=settings&section=premium`)
2. POST → `?page=stripe-checkout` → session Stripe Checkout créée via cURL
3. Redirection Stripe
4. Retour `?page=stripe-success&session_id=...` → activation abonnement + features
5. Webhook `?page=stripe-webhook` pour traitement côté serveur (backup)

### Gestion
- `stripe-cancel` : annulation abonnement
- `stripe-reactivate` : réactivation
- SUPER_ADMIN : calcul prorata (facturation au 15 du mois)

---

## FONCTIONNALITÉS PREMIUM

### 4 features disponibles

| Clé | Nom | Prix |
|-----|-----|------|
| `google_reviews` | Avis Google | 3,99€/mois |
| `advanced_analytics` | Statistiques avancées | 3,99€/mois |
| `online_booking` | Réservations en ligne | 10,99€/mois |
| `delivery_integration` | Intégration livraison | 3,99€/mois |

### Logique d'accès (PremiumFeature::isEnabled)
```
BETA_MODE === true → toujours accès
SUPER_ADMIN → toujours accès
Feature is_active = 1 ET (expires_at > now OU expires_at IS NULL) → accès
Sinon → redirigé vers premium settings
```

### Avis Google (`google_reviews`)
- Google Places API (New) via cURL
- Cache en BDD (1h) pour réduire appels API
- Config : Place ID + API Key dans admin_options
- Affichage : note moyenne, nombre d'avis, 5 derniers avis avec auteur + texte

### Statistiques (`advanced_analytics`)
- Tracking anonymisé (hash SHA256 de IP+UA)
- Données : visites totales, uniques, par jour (Chart.js), par appareil, navigateur, référents, heures, jours
- Anti-spam : max 1 visite/visiteur/minute
- API JSON (`?page=stats-data`) pour graphiques dynamiques

### Réservations (`online_booking`)

**Côté public** :
- Formulaire : nom, tel, email, date, heure, nombre de personnes, demandes spéciales
- Rate limiting : 10 résa/heure/IP
- Email confirmation client + notification restaurant

**Côté admin** :
- Dashboard avec KPIs (aujourd'hui, en attente, confirmées)
- Liste filtrable par date/statut
- Statuts : pending → confirmed / rejected / completed / cancelled / no_show
- Email notification client à chaque changement
- Paramètres : taille groupe min/max, jours à l'avance, auto-complétion

**Notifications temps réel (SSE)** :
- `NotificationStreamController` via Server-Sent Events
- Vérifie toutes les 3s les nouvelles réservations
- Heartbeat toutes les 15s
- `session_write_close()` immédiat pour ne pas bloquer

---

## SYSTÈME DE DÉMONSTRATION

### Fonctionnement
1. SUPER_ADMIN génère un lien depuis le dashboard
2. Clone **complet** du restaurant `demo-menucraft` :
   - Restaurant cloné (slug : `demo-menucraft-XXXXXXXX`)
   - Admin cloné (username : `demo_XXXXXXXX`)
   - Catégories, plats, images, options, contact, menus du jour clonés
3. Token unique généré (validité : **3 jours**)
4. Lien partageable : `?page=demo&token=XXXXX`

### Accès démo
1. Visiteur clique sur le lien
2. Token validé (existence + non expiré)
3. Session créée : `demo_mode=true`, `demo_token`, `demo_expires_at`, `demo_slug`, `admin_id` = clone
4. Redirection dashboard du clone

### Restrictions démo
Bloqué par `BaseController::blockIfDemo()` :
- Modification profil/MDP
- Paiement Stripe
- Suppression compte
- Envoi invitations

Autorisé (dans le clone isolé) :
- Éditer carte, contact, logo, services, template

### Nettoyage
- À la déconnexion (`demo-logout`) : suppression complète du clone
- `DemoToken::cleanExpired()` appelé régulièrement (lazy cleanup)
- Protection : refuse de supprimer le slug `demo-menucraft` (template original)

---

## CRON JOBS

### `cron/auto_complete_reservations.php`
- **Fréquence** : toutes les 15 minutes
- **Action** : marque les réservations confirmées comme "completed" si date/heure passée
- **Condition** : seulement pour admins avec `booking_auto_complete = 1`

### `cron/send_reminders.php`
- **Fréquence** : mensuelle
- **Action** : email rappel aux admins avec `mail_reminder = 1`
- **Contenu** : invitation à mettre à jour la carte
- **Logs** : `cron/logs/reminders.log`

---

## EMAILS

### Mailer Helper
- Envoi HTML via `mail()` natif PHP
- From : `no-reply@menucraft.com`
- Conversion HTML → plain text automatique
- Log de chaque email dans `cron/logs/mail.log`

### Emails envoyés
| Déclencheur | Destinataire | Contenu |
|-------------|-------------|---------|
| Inscription libre | Nouvel admin | Email vérification avec token |
| Email vérifié | Admin | Confirmation activation |
| Invitation SUPER_ADMIN | Invité | Lien inscription avec token |
| Reset MDP | Admin | Lien réinitialisation |
| Nouvelle réservation | Restaurant | Détails réservation |
| Confirmation résa | Client | Confirmation + détails |
| Refus résa | Client | Notification refus |
| Rappel mensuel (CRON) | Admins avec option | Mise à jour carte |

---

## SEO & PAGES LÉGALES

### SEO (dans `display/head.php`)
- Meta tags : title, description, keywords (par restaurant)
- Open Graph : og:title, og:description, og:type, og:image
- Schema.org : données structurées Restaurant (JSON-LD)
- Sitemap XML dynamique (`?page=sitemap.xml`)

### Pages légales (`?page=legal&section=`)
| Section | Contenu |
|---------|---------|
| `cgu` | Conditions Générales d'Utilisation |
| `privacy` | Politique de Confidentialité (RGPD) |
| `cookies` | Politique des Cookies |
| `legal` | Mentions Légales |

### Cookies RGPD
- Bannière consentement sur la vitrine (`display/cookies.js`)
- Bannière séparée dans le back-office (`admin/cookies.js`)
- Cookies séparés : `display_cookie_consent` (vitrine) vs `cookie_consent` (admin)

---

## CSS — CHARTE GRAPHIQUE

### Variables globales (`shared/_variables.css`)
```css
:root {
  --color-primary: #b45309;       /* Ambre/cuivré — restauration */
  --color-primary-dark: #92400e;
  --color-primary-light: #d97706;
  --color-text: #1c1917;
  --color-text-light: #57534e;
  --color-text-muted: #a8a29e;
  --color-bg: #ffffff;
  --color-bg-alt: #fafaf9;
  --color-bg-warm: #fef7ed;
  --color-border: #e7e5e4;
  --font-family: 'Inter', system-ui, sans-serif;
  --radius-sm: 6px; --radius-md: 12px; --radius-lg: 20px;
  --shadow-sm/md/lg/xl
  --spacing-xs(4)/sm(8)/md(16)/lg(24)/xl(40)/2xl(64)/3xl(96)
  --transition-fast(0.15s)/normal(0.3s)/slow(0.5s)
}
```

### Dark mode (admin + vitrine)
- Toggle avec `localStorage` persistence
- Classe `html.dark-mode` ou `body.dark-mode`
- Override de toutes les variables CSS vers tons sombres (stone palette)
- Script chargé tôt (pas de defer) pour éviter flash

### Breakpoints
- Desktop : > 768px
- Tablette/Mobile : 768px - 501px
- Petit mobile : ≤ 500px

---

## JAVASCRIPT — LIBRAIRIES EXTERNES

| Lib | Usage | CDN |
|-----|-------|-----|
| SweetAlert2 | Confirmations, alertes stylisées | CDN |
| Chart.js | Graphiques statistiques | CDN |
| SortableJS | Drag & drop catégories/plats/images | CDN |
| Font Awesome 6.5 | Icônes | CDN |

---

## TESTS

### PHPUnit 10.5
- Bootstrap : `tests/bootstrap.php` (crée base `menucraft_test` avec schéma complet)
- **Unit** : tests modèles (Admin, Restaurant, DemoToken)
- **Functional** : tests accès routes HTTP (codes retour, redirections)
- **Security** : tests SQL injection + XSS (injections dans les formulaires)

### k6
- Tests de charge HTTP

---

## POINTS IMPORTANTS À RESPECTER

1. **Pas de framework PHP** — tout est fait à la main (pas de Laravel, Symfony, etc.)
2. **Pas de SDK Stripe** — intégration cURL native
3. **Pas de framework CSS** — tout custom avec variables CSS
4. **Pas de framework JS** — Vanilla JS uniquement (+ libs CDN ci-dessus)
5. **PDO FETCH_OBJ** — les requêtes retournent des objets (`$row->id`, pas `$row['id']`)
6. **Le modèle Admin utilise `fill()` avec accès array** — exception : `$data['id']` (array access dans fill)
7. **Routage par `$_GET['page']`** — pas d'URL rewriting complexe
8. **`public/` est le document root** — les fichiers PHP de `app/` ne sont jamais accessibles directement
9. **Mode Beta** : quand activé, tout est gratuit et les inscriptions se font sur invitation (mailto)
10. **Slug restaurant** : généré à l'inscription à partir du nom du restaurant (slugify)
11. **Email de vérification obligatoire** pour pouvoir se connecter
12. **SUPER_ADMIN ne paie pas** — accès gratuit à tout
13. **Un admin = un restaurant** (relation 1:1)
14. **Les fichiers uploadés** sont stockés avec un nom unique (uniqid) pour éviter les collisions

---

## RÉSUMÉ DES RÔLES

| Fonctionnalité | Public | ADMIN | SUPER_ADMIN |
|---------------|--------|-------|-------------|
| Page landing | ✅ | ✅ | ✅ |
| Site vitrine | ✅ | ✅ | ✅ |
| Inscription/Login | ✅ | — | — |
| Dashboard | — | ✅ | ✅ |
| Gestion carte | — | ✅ | ✅ |
| Contact/Logo/Services | — | ✅ | ✅ |
| Template | — | ✅ | ✅ |
| Paramètres | — | ✅ | ✅ |
| Plan de salle | — | ✅ | ✅ |
| Feedback | — | ✅ | ✅ |
| Paiement Stripe | — | ✅ | — |
| Options premium | — | ✅ (payant) | — |
| Envoi invitations | — | — | ✅ |
| Gestion clients | — | — | ✅ |
| Dashboard feedbacks | — | — | ✅ |
| Génération démos | — | — | ✅ |

---

## INSTRUCTIONS POUR L'IA

1. Commence par créer la structure de dossiers complète
2. Crée le `database/schema.sql` avec toutes les tables
3. Crée le `config.example.php`
4. Implémente le `BaseController` avec toute la sécurité
5. Implémente le routeur `public/index.php`
6. Implémente les Models un par un
7. Implémente les Controllers un par un
8. Crée les vues (landing, admin, display)
9. Crée les assets CSS (variables, admin, display, templates)
10. Crée les assets JS (par section)
11. Ajoute les CRON jobs
12. Ajoute les tests
13. Configure les `.htaccess` de protection
14. Crée le `README.md`

**Priorité** : fonctionnel > beau. Assure-toi que chaque feature marche avant de passer à la suivante. Code propre, bien commenté, bien structuré.
