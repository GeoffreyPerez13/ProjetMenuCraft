# 🚀 Déploiement MenuCraft sur o2switch

## Prérequis
- Compte o2switch actif (accès cPanel)
- Nom de domaine configuré (ou utiliser le domaine par défaut o2switch)
- Client FTP (FileZilla) ou utiliser le Gestionnaire de fichiers cPanel

---

## Étape 1 — Créer la base de données

1. Connectez-vous à **cPanel** (https://VOTRE-SERVEUR.o2switch.net:2083)
2. Allez dans **Bases de données MySQL**
3. Créez une nouvelle base : `menucraft`
   → Le nom complet sera : `votreuser_menucraft`
4. Créez un utilisateur MySQL avec un **mot de passe fort**
   → Le nom complet sera : `votreuser_menucraft`
5. **Associez** l'utilisateur à la base avec **TOUS LES PRIVILÈGES**
6. Allez dans **phpMyAdmin** (depuis cPanel)
7. Sélectionnez votre base `votreuser_menucraft`
8. Onglet **Importer** → chargez le fichier `database/schema.sql`
9. Cliquez **Exécuter** — toutes les tables seront créées

---

## Étape 2 — Uploader les fichiers

### Option A : Via le Gestionnaire de fichiers cPanel (simple)

1. Dans cPanel > **Gestionnaire de fichiers**
2. Naviguez vers `public_html/`
3. **Supprimez** les fichiers par défaut (index.html, cgi-bin, etc.)
4. Compressez tout le projet en `.zip` sur votre PC
5. Uploadez le `.zip` dans `public_html/`
6. **Extraire** le zip → les fichiers doivent être directement dans `public_html/` :

```
public_html/
├── .htaccess            ← redirige vers public/
├── config.php           ← à créer (voir étape 3)
├── app/
├── database/
├── cron/
├── storage/
└── public/
    ├── .htaccess        ← routing + sécurité
    ├── index.php        ← point d'entrée
    ├── assets/
    └── uploads/
```

### Option B : Via FTP (FileZilla)

1. Dans cPanel > **Comptes FTP** : notez vos identifiants
   - Hôte : `ftp.VOTRE-DOMAINE.com` (ou le serveur o2switch)
   - Port : `21` (FTP) ou `990` (FTPS)
   - Utilisateur : votre user cPanel
   - Mot de passe : votre mot de passe cPanel
2. Connectez FileZilla et naviguez vers `public_html/`
3. Uploadez **tout le contenu** du projet (pas le dossier parent, son contenu)

### Option C : Via Git (avancé)

1. En SSH (cPanel > Terminal) :
```bash
cd ~
git clone https://github.com/GeoffreyPerez13/ProjetMenuCraft.git
# Copier les fichiers dans public_html
cp -r ProjetMenuCraft/* public_html/
cp ProjetMenuCraft/.htaccess public_html/
```

---

## Étape 3 — Configurer config.php

1. Copiez `config.production.example.php` → `config.php` sur le serveur
2. Éditez `config.php` (via le Gestionnaire de fichiers > clic droit > Modifier) :

```php
<?php
$host = 'localhost';
$dbname = 'votreuser_menucraft';     // ← Nom complet de la base
$user   = 'votreuser_menucraft';     // ← Nom complet de l'utilisateur
$pass   = 'VOTRE_MOT_DE_PASSE';     // ← Mot de passe défini à l'étape 1

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

define('SITE_URL', 'https://votre-domaine.com/');
define('BASE_PATH', __DIR__);

define('STRIPE_SECRET_KEY', 'sk_live_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

define('BETA_MODE', true);
define('BETA_EXPIRES', '2026-09-30');
```

> ⚠️ **SITE_URL** doit correspondre EXACTEMENT à votre domaine avec le `/` final.
> Exemples : `https://menucraft.fr/` ou `https://www.mondomaine.com/`

---

## Étape 4 — Activer le SSL (HTTPS)

1. cPanel > **SSL/TLS** > **Let's Encrypt** (ou AutoSSL)
2. Sélectionnez votre domaine et cliquez **Installer**
3. Attendez quelques minutes que le certificat soit actif
4. Éditez `public/.htaccess` sur le serveur, **décommentez** les 2 lignes HTTPS :

```apache
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Étape 5 — Permissions des dossiers

Via le Gestionnaire de fichiers cPanel (clic droit > Permissions) :

| Dossier | Permission | Pourquoi |
|---------|-----------|----------|
| `storage/` | 755 | Cache et rate limits |
| `storage/cache/` | 755 | Cache des pages display |
| `storage/rate_limits/` | 755 | Anti-flood |
| `public/uploads/` | 755 | Upload logos, bannières, images |
| `cron/logs/` | 755 | Logs des emails et crons |

---

## Étape 6 — Mettre à jour l'adresse email d'envoi

Éditez `app/Helpers/Mailer.php` sur le serveur, ligne 7 :

```php
private string $from = 'no-reply@votre-domaine.com';
```

> Sur o2switch, `mail()` fonctionne nativement. L'email d'envoi doit correspondre à votre domaine pour éviter le spam.

Optionnel : créez l'adresse email `no-reply@votre-domaine.com` dans cPanel > **Comptes de messagerie**.

---

## Étape 7 — Configurer les tâches CRON (optionnel)

cPanel > **Tâches Cron** :

| Fréquence | Commande |
|-----------|----------|
| Toutes les heures | `php /home/VOTREUSER/public_html/cron/clean_demos.php` |
| Tous les jours à 8h | `php /home/VOTREUSER/public_html/cron/send_reminders.php` |
| Tous les jours à 2h | `php /home/VOTREUSER/public_html/cron/check_subscriptions.php` |

> Remplacez `VOTREUSER` par votre nom d'utilisateur cPanel.

---

## Étape 8 — Créer le premier compte admin

1. Connectez-vous à **phpMyAdmin** dans cPanel
2. Dans la table `admins`, insérez un compte SUPER_ADMIN :

```sql
INSERT INTO restaurants (name, slug) VALUES ('Mon Restaurant', 'mon-restaurant');

INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, email_verified)
VALUES (
    'admin',
    'votre@email.com',
    '$2y$10$YOUR_BCRYPT_HASH',
    'SUPER_ADMIN',
    'Mon Restaurant',
    1,
    1
);
```

**Pour générer le hash du mot de passe** : accédez à `https://votre-domaine.com/` et utilisez la fonction d'invitation pour créer un compte normalement. OU utilisez un générateur bcrypt en ligne.

Ou plus simplement : envoyez-vous une invitation depuis le flux normal une fois le premier SUPER_ADMIN créé.

---

## Étape 9 — Vérifications post-déploiement

- [ ] Le site s'affiche à `https://votre-domaine.com/`
- [ ] La page de connexion fonctionne (`?page=login`)
- [ ] Le dashboard admin s'affiche après connexion
- [ ] L'upload d'images fonctionne (logo, bannière, plats)
- [ ] Les emails sont envoyés (test depuis invitation ou réservation)
- [ ] Le plan de salle fonctionne (sauvegarder une table)
- [ ] La page restaurant publique s'affiche (`/slug-restaurant`)
- [ ] Le mode dark/light fonctionne
- [ ] Le SSL est actif (cadenas dans la barre d'adresse)

---

## Dépannage

### Page blanche ou erreur 500
→ Vérifiez le fichier `config.php` (identifiants BDD)
→ Vérifiez la version PHP dans cPanel > **Sélectionner une version de PHP** (PHP 8.1+ requis)
→ Activez les extensions : `pdo_mysql`, `mbstring`, `json`, `fileinfo`

### Erreur "No input file specified"
→ Le `.htaccess` racine ne fonctionne pas. Vérifiez que `mod_rewrite` est activé.

### Les emails n'arrivent pas
→ Vérifiez que l'adresse `from` dans `Mailer.php` correspond à votre domaine
→ Configurez un enregistrement SPF dans la zone DNS (cPanel > Zone Editor) :
```
v=spf1 include:o2switch.net ~all
```

### Les images ne s'affichent pas
→ Vérifiez les permissions de `public/uploads/` (755)
→ Vérifiez que `SITE_URL` dans `config.php` est correct

### Erreur CSRF "Token de sécurité invalide"
→ Vérifiez que `session.save_path` est accessible (cPanel > PHP > Options)

---

## Mise à jour du site

Pour mettre à jour après des modifications :

1. **Via FTP** : re-uploadez les fichiers modifiés
2. **Via Git SSH** :
```bash
cd ~/public_html
git pull origin main
```

> ⚠️ Ne jamais écraser `config.php` en production !
