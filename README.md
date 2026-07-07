# MenuCraft

Plateforme SaaS permettant aux restaurateurs de créer et gérer un site vitrine professionnel.

## Stack technique

- **Backend** : PHP 8.x (procédural avec classes, pas de framework)
- **Base de données** : MySQL 8.x via PDO (prepared statements uniquement)
- **Serveur** : Apache avec `.htaccess` pour l'URL rewriting
- **CSS** : Custom (pas de Tailwind, Bootstrap, etc.)
- **JS** : Vanilla JS + libs CDN (SweetAlert2, Chart.js, SortableJS)
- **Paiement** : Stripe API via cURL (pas de SDK)
- **Email** : `mail()` natif PHP

## Installation

### Prérequis

- PHP 8.x
- MySQL 8.x
- Apache avec `mod_rewrite`
- WAMP / LAMP / MAMP

### Étapes

1. **Cloner le projet** dans le dossier `www/` de votre serveur :
   ```
   git clone <repo> ProjetMenuCraft
   ```

2. **Créer la base de données** :
   ```sql
   CREATE DATABASE menucraft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Importer le schéma** :
   ```
   mysql -u root menucraft < database/schema.sql
   ```

4. **Configurer l'application** :
   ```
   cp config.example.php config.php
   ```
   Adapter les valeurs dans `config.php` (BDD, Stripe, etc.)

5. **Vérifier les permissions** :
   - `public/uploads/` : écriture (775)
   - `storage/` : écriture (775)
   - `cron/logs/` : écriture (775)

6. **Accéder à l'application** :
   ```
   http://localhost/ProjetMenuCraft/public/
   ```

## Structure du projet

```
ProjetMenuCraft/
├── app/
│   ├── Controllers/       # Contrôleurs MVC
│   ├── Models/            # Modèles de données
│   ├── Helpers/           # Classes utilitaires
│   ├── Services/          # Services métier
│   └── Views/             # Vues PHP
│       ├── admin/         # Back-office
│       ├── display/       # Site vitrine (pas utilisé directement)
│       ├── errors/        # Pages d'erreur
│       └── partials/      # Header/Footer admin
├── cron/                  # Tâches planifiées
├── database/              # Schema SQL
├── public/                # Document root
│   ├── assets/css/        # Feuilles de style
│   ├── uploads/           # Fichiers uploadés
│   ├── index.php          # Routeur principal
│   └── robots.txt         # SEO
├── storage/               # Stockage interne (rate limits)
├── config.php             # Configuration (ignoré par Git)
├── config.example.php     # Template de configuration
└── .htaccess              # Redirection vers public/
```

## Rôles

| Rôle | Accès |
|------|-------|
| **PUBLIC** | Landing page, site vitrine, réservation |
| **ADMIN** | Back-office complet pour son restaurant |
| **SUPER_ADMIN** | Gestion des clients, invitations, feedbacks, démos |

## CRON Jobs

| Script | Fréquence | Description |
|--------|-----------|-------------|
| `cron/clean_demos.php` | Toutes les heures | Nettoie les démos expirées |
| `cron/check_subscriptions.php` | Tous les jours à 2h | Expire les abonnements, auto-complète les réservations |
| `cron/send_reminders.php` | 1er du mois à 10h | Rappels de mise à jour de la carte |

## Mode Beta

Quand `BETA_MODE` est activé dans `config.php` :
- Toutes les fonctionnalités premium sont gratuites
- Les inscriptions se font sur invitation (mailto)
- Valable jusqu'à la date `BETA_EXPIRES`

## Sécurité

- CSRF tokens sur tous les formulaires
- Rate limiting sur login et réservations
- Mots de passe hashés avec `password_hash()` (bcrypt)
- Headers de sécurité (X-Frame-Options, CSP, etc.)
- Prepared statements PDO exclusivement
- `htmlspecialchars()` sur toutes les sorties

## Licence

Propriétaire — Tous droits réservés.
