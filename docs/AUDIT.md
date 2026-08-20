# Audit complet — MenuCraft

> Date : 20 août 2026
> Scope : Architecture, sécurité, qualité du code, performance, UX, robustesse

---

## Notation globale

| Domaine | Note | Verdict |
|---------|------|---------|
| Architecture & Structure | ⭐⭐⭐⭐ | Solide |
| Sécurité | ⭐⭐⭐ | Correcte, quelques failles à corriger |
| Qualité du code | ⭐⭐⭐⭐ | Propre et cohérent |
| Performance | ⭐⭐⭐ | Acceptable, optimisations possibles |
| UX / UI | ⭐⭐⭐⭐ | Moderne et responsive |
| Robustesse / Gestion d'erreurs | ⭐⭐⭐ | Partielle, à renforcer |

---

## 1. Architecture & Structure

### ✅ Points positifs

- **MVC clair** — `Controllers/`, `Models/`, `Views/`, `Helpers/`, `Services/` bien séparés
- **BaseController** solide — CSRF, flash, render, auth, JSON, redirect, security headers centralisés
- **Autoload propre** via `spl_autoload_register` sur 4 répertoires
- **Routeur centralisé** dans `public/index.php` — point d'entrée unique
- **Config séparée** — `config.php` gitignored, `config.example.php` fourni
- **Schéma SQL** complet et bien organisé avec clés étrangères et CASCADE
- **Crons** pour tâches planifiées (abonnements, nettoyage démo, rappels)
- **18 controllers, 19 models** — bonne granularité, pas de "God class"

### ⚠️ Points à améliorer

- **Pas d'autoloader PSR-4 / Composer** — L'autoload custom fonctionne mais ne gère pas les namespaces. Si le projet grandit, envisager Composer + PSR-4.
- **Routeur = switch géant** (537 lignes dans `index.php`) — Fonctionnel mais difficile à maintenir. Envisager un mini-routeur (`$routes['page'] = [Controller, 'method']`) pour simplifier.
- **Logique métier dans `index.php`** — Les pages `reset-password`, `reset-password-admin`, `edit-template`, `feedback-dashboard` contiennent de la logique directement dans le routeur au lieu d'être dans un contrôleur. Elles devraient être migrées dans leurs contrôleurs respectifs.
- **Pas de middleware** — Le pattern `requireAuth()` / `requireSuperAdmin()` est appelé manuellement dans chaque méthode. Un système de middleware (même simple) éviterait les oublis.
- **Absence de tests** — Aucun test unitaire ni fonctionnel. Critique pour un projet en production.

### 💡 Suggestion

```
Priorité : Moyenne
Migrer les 4 routes avec logique inline vers des méthodes de contrôleur.
Cela réduirait index.php de ~150 lignes et améliorerait la maintenabilité.
```

---

## 2. Sécurité

### ✅ Points positifs

- **CSRF** — Token sur tous les formulaires POST, vérification via `hash_equals()`
- **Prepared statements** — Utilisés partout, aucune concaténation SQL détectée
- **Bcrypt** — `password_hash(PASSWORD_BCRYPT)` pour tous les mots de passe
- **Brute-force protection** — Double protection IP (5 tentatives/15 min) + compte (10/30 min)
- **Security headers** — `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`, HSTS conditionnel
- **Session sécurisée** — `cookie_httponly`, `use_strict_mode`, `cookie_samesite=Lax`
- **Validation mot de passe** — Longueur, majuscule, chiffre, caractère spécial
- **Rate limiting** sur les réservations publiques
- **Mode démo** avec blocage des modifications

### 🔴 Failles critiques

#### 2.1 — IDOR : Pas de vérification de propriété sur les ressources

**Fichier** : `CardController.php`

```php
// deleteCategory() — n'importe quel admin authentifié peut supprimer
// la catégorie d'un autre admin
$id = (int)($_POST['category_id'] ?? 0);
(new Category($this->pdo))->delete($id);
```

**Même problème sur** : `deleteDish()`, `saveDish()` (modification), `deleteImage()`, `saveDailyMenu()`, `deleteDailyMenu()`, `toggleDailyMenu()`, `reorderCategories()`, `reorderDishes()`.

**Correction** : Vérifier que la ressource appartient à `$adminId` avant toute modification/suppression.

```php
// Exemple de fix
$category = $categoryModel->findById($id);
if (!$category || $category->admin_id !== $adminId) {
    $this->flash('error', 'Catégorie introuvable.');
    $this->redirect('edit-card');
    return;
}
```

#### 2.2 — IDOR : FloorPlan save/rename sans vérification de propriété

**Fichier** : `FloorPlanController.php`

```php
// save() — n'importe quel admin peut modifier le plan de n'importe quel floor
$floorId = (int)($data['floor_id'] ?? 0);
(new RestaurantTable($this->pdo))->save($floorId, $tables);
```

```php
// renameFloor() — pas de vérification que le floor appartient à l'admin
(new Floor($this->pdo))->rename($id, $name);
```

#### 2.3 — Stripe webhook non vérifié

**Fichier** : `StripeController.php`

```php
public function handleWebhook(): void
{
    $payload = file_get_contents('php://input');
    $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    http_response_code(200);
    echo json_encode(['received' => true]);
    // La signature n'est jamais vérifiée !
    // N'importe qui peut appeler cet endpoint
}
```

**Correction** : Vérifier la signature avec `STRIPE_WEBHOOK_SECRET`, ou si pas encore implémenté, au moins renvoyer 400 pour empêcher les abus.

#### 2.4 — handleSuccess() sans vérification du paiement

**Fichier** : `StripeController.php`

```php
public function handleSuccess(): void
{
    $sessionId = $_GET['session_id'] ?? '';
    // Active l'abonnement sans vérifier auprès de Stripe
    // que le paiement a réellement été effectué !
    $subModel->activate($adminId, [...]);
}
```

**Risque** : Un utilisateur peut forger l'URL `?page=stripe-success&session_id=fake` et obtenir un abonnement premium gratuit.

**Correction** : Appeler l'API Stripe pour récupérer la session et vérifier `payment_status === 'paid'`.

### 🟡 Failles modérées

#### 2.5 — Upload : validation basée sur `$file['type']` (spoofable)

**Fichiers** : `CardController::handleUpload()`, `LogoBannerController::handleUpload()`

```php
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) return null;
```

Le MIME type envoyé par le client est falsifiable. Ajouter une vérification côté serveur :

```php
$finfo = new finfo(FILEINFO_MIME_TYPE);
$realType = $finfo->file($file['tmp_name']);
if (!in_array($realType, $allowedTypes)) return null;
```

#### 2.6 — Extension de fichier non validée

```php
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = $subfolder . '/' . uniqid() . '_' . time() . '.' . $ext;
```

L'extension vient du nom de fichier client. Un attaquant pourrait uploader `image.php` avec un MIME falsifié. Utiliser une map MIME→extension :

```php
$extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', ...];
$ext = $extMap[$realType] ?? null;
```

#### 2.7 — Code dupliqué : `handleUpload()` dans 2 controllers

Le code d'upload est dupliqué entre `CardController` et `LogoBannerController`. Il devrait être centralisé dans `BaseController` ou un `UploadHelper`.

#### 2.8 — CSRF manquant sur les endpoints JSON

Les endpoints `reorderCategories()`, `reorderDishes()`, `reorderDailyMenus()`, `toggleDailyMenu()`, et tous les endpoints `FloorPlanController` (save, create, rename, delete) ne vérifient pas le CSRF token. Ils lisent depuis `php://input` (JSON) et n'appellent pas `verifyCsrfToken()`.

**Correction** : Envoyer le CSRF token dans un header `X-CSRF-Token` et le vérifier côté serveur pour les requêtes AJAX.

#### 2.9 — Session non régénérée après suspension/impersonation

Lors de l'impersonation, `session_regenerate_id()` n'est pas appelé, ce qui peut permettre du session fixation.

### 🟢 Points mineurs

- **`$_SESSION['csrf_token']` unique** — Un seul token pour toute la session. Des tokens par formulaire seraient plus sûrs mais le niveau actuel est acceptable.
- **Pas de Content-Security-Policy** — Header CSP absent. Ajouter au moins `default-src 'self'` pour mitiger les XSS.
- **Pas de `session.cookie_secure`** — En production HTTPS, forcer `ini_set('session.cookie_secure', 1)`.

---

## 3. Qualité du code

### ✅ Points positifs

- **Nommage cohérent** — camelCase pour les méthodes, snake_case pour la DB, français pour les messages utilisateur
- **Controllers fins** — La plupart font < 100 lignes, bonne séparation des responsabilités
- **Models propres** — Chaque model gère sa table avec des méthodes claires
- **Pas de logique dans les vues** — Les vues sont principalement du HTML + echo de variables
- **Flash messages** bien implémentés (success, error, warning, info)
- **Mode démo** bien intégré avec `blockIfDemo()`
- **Validator** réutilisable avec règles déclaratives

### ⚠️ Points à améliorer

#### 3.1 — `getCurrentAdmin()` appelé à chaque requête × N fois

**Fichier** : `BaseController.php`

```php
protected function getCurrentAdmin(): ?object
{
    // Requête DB à chaque appel !
    $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
    ...
}
```

Cette méthode est appelée dans `render()`, `requireRole()`, `isSuperAdmin()`, et souvent aussi dans les controllers. Cela peut résulter en 3-5 requêtes identiques par page.

**Fix** : Mettre en cache dans une propriété :

```php
private ?object $currentAdminCache = null;

protected function getCurrentAdmin(): ?object
{
    if ($this->currentAdminCache !== null) return $this->currentAdminCache;
    // ... query ...
    $this->currentAdminCache = $stmt->fetch() ?: null;
    return $this->currentAdminCache;
}
```

#### 3.2 — `Admin::updateProfile()` vulnérable à l'injection de colonnes

```php
public function updateProfile(int $id, array $data): bool
{
    foreach ($data as $key => $value) {
        $sets[] = "`$key` = :$key";  // $key vient du caller mais non filtré
    }
}
```

Si un jour `$_POST` est passé directement, un attaquant pourrait modifier `role`, `suspended`, etc. Ajouter une whitelist :

```php
$allowed = ['username', 'email', 'restaurant_name'];
$data = array_intersect_key($data, array_flip($allowed));
```

#### 3.3 — Requêtes SQL directes dans les controllers

Plusieurs controllers font des requêtes SQL au lieu d'utiliser les models :

- `DisplayController` : `SELECT * FROM logos ...`, `SELECT * FROM banners ...`
- `SuperAdminController::globalDashboard()` : 7 requêtes SQL brutes
- `index.php` : requêtes inline pour `feedback-dashboard`, `reset-password-admin`

Ces requêtes devraient être dans les models correspondants.

#### 3.4 — Mailer utilise `mail()` natif

`mail()` est peu fiable, sans gestion DKIM/SPF, et les emails risquent de finir en spam. En production, utiliser PHPMailer ou un service transactionnel (Mailgun, Resend, etc.).

#### 3.5 — Pas de typage strict

Aucun `declare(strict_types=1)` dans les fichiers PHP. Ajouter cela améliorerait la robustesse.

---

## 4. Performance

### ✅ Points positifs

- **PDO avec `FETCH_OBJ`** et `EMULATE_PREPARES = false` — Bon choix
- **Index DB** sur les colonnes critiques (`idx_admin_status`, `idx_admin_date`, `idx_ip`, `idx_username`)
- **Pagination** dans le journal des connexions (50/page)
- **`session_write_close()`** dans le SSE stream — Bonne pratique

### ⚠️ Points à améliorer

#### 4.1 — N+1 queries dans CardController et DisplayController

```php
foreach ($categories as $cat) {
    $dishes = $dishModel->getByCategory($cat->id);  // 1 requête par catégorie
    foreach ($dishes as $dish) {
        $allergenesByDish[$dish->id] = $dishModel->getAllergenes($dish->id);  // 1 par plat !
    }
}
```

Pour un restaurant avec 8 catégories et 40 plats = 1 + 8 + 40 = **49 requêtes**.

**Fix** : Charger tous les plats de l'admin en une requête avec JOIN, puis regrouper en PHP.

#### 4.2 — `loadPendingReservations()` dans le constructeur de BaseController

```php
public function __construct(PDO $pdo)
{
    $this->pdo = $pdo;
    $this->setSecurityHeaders();
    $this->loadPendingReservations();  // Requête DB à chaque requête
}
```

Cela exécute une requête DB même pour les pages publiques, le sitemap, le webhook Stripe, etc. Ce chargement devrait être lazy (seulement quand le header admin est affiché).

#### 4.3 — `getCurrentAdmin()` + `activeAnnouncements` dans `render()`

Chaque appel à `render()` fait :
1. `getCurrentAdmin()` → 1 requête
2. `SELECT * FROM announcements` → 1 requête
3. `OptionModel->get('hide_tour_button')` → 1 requête

Soit 3 requêtes overhead par page, même pour les pages publiques.

#### 4.4 — SSE Stream : 1 requête DB toutes les 3 secondes

`NotificationStreamController` garde une connexion ouverte et interroge la DB toutes les 3s. Avec N admins connectés = N connexions persistantes + N requêtes/3s. Envisager un mécanisme de polling côté client avec intervalle plus long (10-15s).

#### 4.5 — `login_attempts` sans purge automatique

La table `login_attempts` grossit indéfiniment. `LoginAttempt::cleanup()` existe mais n'est appelé nulle part en production. Ajouter au cron.

#### 4.6 — `password_resets` sans purge

Même problème : les tokens expirés ne sont jamais nettoyés.

---

## 5. UX / UI

### ✅ Points positifs

- **Design system cohérent** — Variables CSS, composants réutilisables (cards, badges, buttons, tables)
- **Dark mode** supporté
- **Responsive** — Breakpoints à 768px, 500px, 400px
- **Super admin** — Mobile cards pour `manage-clients`, filter bar stylisée, type selector radio
- **Flash messages** animés avec icônes par type
- **Empty states** avec icônes et messages d'aide
- **Sidebar** avec overlay mobile et scroll

### ⚠️ Points à améliorer

- **Pas de skeleton/loading states** — Les pages s'affichent d'un coup après le chargement PHP. Pas critique en MPA mais pourrait être amélioré pour les requêtes AJAX.
- **Pas de breadcrumbs** — La navigation dépend uniquement de la sidebar. Des breadcrumbs aideraient l'orientation.
- **Confirmation de suppression** — Utilise `confirm()` natif du navigateur. Des modales custom (comme `sa-modal`) seraient plus cohérentes.
- **Pas de recherche globale** — Avec beaucoup de plats/catégories, une recherche serait utile sur `edit-card`.
- **Pas de toasts** pour les actions AJAX — Les requêtes JSON (reorder, toggle) n'ont pas de feedback visuel.

---

## 6. Robustesse & Gestion d'erreurs

### ✅ Points positifs

- **`display_errors = 0`** en production
- **`log_errors = 1`** — Les erreurs sont loggées
- **try/catch** sur les requêtes critiques (announcements, pending reservations)
- **Fallbacks** dans les vues (`$variable ?? default`)

### ⚠️ Points à améliorer

#### 6.1 — Pas de gestionnaire d'erreurs global

Aucun `set_exception_handler()` ni `set_error_handler()`. Une erreur fatale affiche une page blanche. Ajouter un handler qui :
- Logge l'erreur complète
- Affiche une page d'erreur propre à l'utilisateur

#### 6.2 — Pas de transaction DB pour les opérations critiques

`SuperAdminController::deleteClient()` supprime des données dans 9 tables + le restaurant + l'admin. Si une suppression échoue, les données sont dans un état incohérent.

```php
// Devrait être dans une transaction
$this->pdo->beginTransaction();
try {
    // ... deletions ...
    $this->pdo->commit();
} catch (Exception $e) {
    $this->pdo->rollBack();
}
```

Même chose pour `AdminController::register()` (création restaurant + admin + subscription + options).

#### 6.3 — Suppression en cascade manuelle dans deleteClient()

```php
$tables = ['categories', 'contact', 'daily_menus', ...];
foreach ($tables as $table) {
    try {
        $this->pdo->prepare("DELETE FROM `$table` WHERE admin_id = :id")->execute([':id' => $clientId]);
    } catch (PDOException $e) {}
}
```

Les clés étrangères `ON DELETE CASCADE` dans le schema devraient gérer cela automatiquement. Supprimer l'admin devrait cascader. Le code actuel est une double sécurité mais masque les erreurs avec `catch (PDOException $e) {}`.

#### 6.4 — `RateLimiter` basé sur fichiers

Le rate limiter utilise des fichiers JSON par IP. Problèmes :
- Race conditions possibles (lectures/écritures concurrentes)
- Pas de nettoyage des vieux fichiers
- Ne fonctionne pas en cluster/multi-serveur

Pour le moment c'est acceptable vu l'échelle, mais envisager de migrer vers une solution DB ou Redis.

---

## 7. Résumé des actions recommandées

### 🔴 Priorité haute (sécurité)

| # | Action | Fichier(s) | Effort |
|---|--------|------------|--------|
| 1 | Vérifier propriété des ressources (IDOR) | `CardController`, `FloorPlanController` | 2h |
| 2 | Vérifier le paiement Stripe dans `handleSuccess()` | `StripeController` | 1h |
| 3 | Valider uploads avec `finfo` + whitelist extensions | `CardController`, `LogoBannerController` | 30min |
| 4 | Ajouter CSRF sur endpoints JSON/AJAX | Tous les controllers avec `php://input` | 1h |
| 5 | Whitelist colonnes dans `Admin::updateProfile()` | `Admin.php` | 10min |

### 🟡 Priorité moyenne (qualité / performance)

| # | Action | Fichier(s) | Effort |
|---|--------|------------|--------|
| 6 | Cache `getCurrentAdmin()` en propriété | `BaseController` | 10min |
| 7 | Lazy-load `pendingReservations` (pas dans constructor) | `BaseController` | 20min |
| 8 | Résoudre N+1 queries (plats + allergènes) | `CardController`, `DisplayController` | 1h |
| 9 | Migrer logique inline de `index.php` vers controllers | `index.php` | 1h |
| 10 | Centraliser `handleUpload()` dans BaseController | `CardController`, `LogoBannerController` | 30min |
| 11 | Transactions DB pour opérations multi-tables | `SuperAdminController`, `AdminController` | 30min |
| 12 | Cron : purge `login_attempts` + `password_resets` | `cron/` | 20min |

### 🟢 Priorité basse (améliorations)

| # | Action | Effort |
|---|--------|--------|
| 13 | Ajouter `declare(strict_types=1)` partout | 30min |
| 14 | Ajouter un error handler global | 30min |
| 15 | Ajouter Content-Security-Policy header | 15min |
| 16 | Remplacer `mail()` par PHPMailer ou Resend | 2h |
| 17 | Réduire le polling SSE à 10-15s | 5min |
| 18 | Tests unitaires pour les models critiques | 4h+ |

---

## 8. Ce qui est bien fait (à garder)

Pour finir sur une note positive, voici ce qui est **solide et bien conçu** :

- ✅ Architecture MVC claire et maintenable
- ✅ Sécurité CSRF + prepared statements systématiques
- ✅ Brute-force protection double couche
- ✅ Security headers complets
- ✅ Mode démo avec isolation propre
- ✅ Système d'invitations par email
- ✅ Gestion d'abonnements et features premium
- ✅ SSE pour notifications temps réel
- ✅ Sitemap dynamique pour le SEO
- ✅ Design UI cohérent avec variables CSS et responsive
- ✅ Système de rôles (ADMIN / SUPER_ADMIN) bien implémenté
- ✅ Crons pour maintenance automatique
- ✅ Config gitignorée avec example fourni
- ✅ Impersonation avec session safety

Le projet est **bien structuré pour sa taille** et la plupart des bonnes pratiques web sont suivies. Les corrections prioritaires concernent principalement les vérifications de propriété (IDOR) et la sécurisation Stripe.
