# Super Admin — Fiche de route

> Rôle : `SUPER_ADMIN` — Gestion globale de la plateforme MenuCraft.
> Le Super Admin dispose de **toutes les fonctionnalités Admin** (ci-dessous) plus les fonctionnalités d'administration de la plateforme.

---

## Accès rapide (sidebar → section "Administration")

| Page | Route | Description |
|------|-------|-------------|
| Vue globale | `?page=super-dashboard` | Dashboard d'administration avec statistiques plateforme |
| Clients | `?page=manage-clients` | Gestion complète des comptes clients |
| Invitations | `?page=send-invitation` | Envoi d'invitations par email |
| Journal connexions | `?page=login-journal` | Historique de toutes les tentatives de connexion |
| Annonces | `?page=announcements` | Système d'annonces globales |
| Feedbacks | `?page=feedback-dashboard` | Lecture des retours envoyés par les clients |

---

## 1. Vue globale (`super-dashboard`)

Dashboard centralisé avec statistiques en temps réel :

- **Clients inscrits** — Nombre total de comptes `ADMIN`
- **Abonnements actifs** — Comptes avec souscription `active`
- **Sites en ligne** — Nombre de sites publiés (`site_online = 1`)
- **Nouveaux clients (7 jours)** — Inscriptions de la dernière semaine
- **Réservations aujourd'hui** — Total des réservations du jour
- **Échecs login (24h)** — Tentatives de connexion échouées récentes (alerte si > 10)

### Activité récente
Tableau des 15 derniers clients triés par dernière connexion, avec :
- Nom du restaurant, utilisateur
- Statut abonnement (active/inactive)
- Dernière connexion
- Statut du compte (actif/suspendu)

### Accès rapides
Liens directs vers : Clients, Journal connexions, Annonces, Invitations, Feedbacks.

---

## 2. Gestion des clients (`manage-clients`)

Tableau complet de tous les comptes clients avec actions :

### Informations affichées
- Restaurant, utilisateur, email
- Statut abonnement (active/inactive + type de plan)
- Statut du compte (actif/suspendu)
- Date d'inscription

### Actions disponibles

| Action | Bouton | Description |
|--------|--------|-------------|
| **Impersonation** | <i class="fas fa-sign-in-alt"></i> | Se connecter en tant que ce client (voir section 4) |
| **Activer abonnement** | <i class="fas fa-check"></i> | Passer l'abonnement en `active` + activer features premium |
| **Désactiver abonnement** | <i class="fas fa-pause"></i> | Désactiver l'abonnement du client |
| **Suspendre** | <i class="fas fa-ban"></i> | Suspendre le compte avec raison optionnelle (email envoyé au client) |
| **Réactiver** | <i class="fas fa-unlock"></i> | Lever la suspension du compte |
| **Supprimer** | <i class="fas fa-trash"></i> | Supprimer définitivement le compte + toutes les données associées |

### Suspension
- Ouvre une modale pour saisir une raison (optionnelle)
- Le client reçoit un email de notification
- Le client ne peut plus se connecter tant que le compte est suspendu
- Un badge "Suspendu" apparaît dans la liste

### Suppression
- **Irréversible** — Demande de confirmation avant exécution
- Supprime en cascade : catégories, plats, contact, menus du jour, réservations, options, features premium, abonnement, visites, feedbacks, restaurant, puis le compte admin

---

## 3. Journal des connexions (`login-journal`)

Historique complet des tentatives de connexion sur la plateforme.

### Filtres disponibles
- **Adresse IP** — Recherche partielle
- **Nom d'utilisateur** — Recherche partielle
- **Statut** — Toutes / Réussies / Échouées

### Informations par entrée
- Date et heure exactes
- Adresse IP (format `code`)
- Nom d'utilisateur tenté
- Restaurant associé (si trouvé)
- Statut : Réussie (vert) / Échouée (rouge)

### Pagination
- 50 entrées par page
- Navigation avec filtres conservés entre les pages

### Sécurité liée
Le système de brute-force bloque automatiquement :
- Une IP après trop de tentatives (blocage temporaire)
- Un compte après trop d'échecs consécutifs (verrouillage temporaire)

---

## 4. Impersonation (se connecter en tant que client)

Permet de naviguer dans l'application avec le compte d'un client pour diagnostiquer des problèmes ou vérifier la configuration.

### Fonctionnement
1. Cliquer sur le bouton d'impersonation depuis `manage-clients`
2. La session bascule sur le compte client (dashboard, carte, réservations, etc.)
3. Un **bandeau violet** apparaît en haut de page avec le message :
   > *Connecté en tant que [username]. Revenir à votre compte*
4. Cliquer sur "Revenir" ou aller sur `?page=stop-impersonate` pour restaurer la session Super Admin

### Détails techniques
- L'ID Super Admin est sauvegardé dans `$_SESSION['impersonating_from']`
- L'impersonation ne fonctionne que sur les comptes de rôle `ADMIN`
- Après arrêt, retour automatique sur `manage-clients`

---

## 5. Annonces globales (`announcements`)

Système de communication pour afficher des messages à **tous les administrateurs** via un bandeau en haut de chaque page.

### Créer une annonce
- **Message** — Texte libre (obligatoire)
- **Type** — Sélection visuelle parmi :
  - **Information** (bleu) — Mises à jour, informations générales
  - **Avertissement** (orange) — Maintenance planifiée, changements à venir
  - **Urgent** (rouge) — Incidents, actions requises

### Gérer les annonces existantes
| Action | Description |
|--------|-------------|
| **Activer/Désactiver** | Basculer la visibilité sans supprimer |
| **Supprimer** | Retirer définitivement l'annonce |

### Affichage
- Les annonces actives apparaissent dans le **header de toutes les pages admin**
- Chaque annonce a un bandeau coloré selon son type
- Triées par date de création (plus récentes en premier)

---

## 6. Invitations (`send-invitation`)

Envoi d'invitations par email pour créer un nouveau compte client.

- **Email** — Adresse du futur client
- **Nom du restaurant** — Pré-rempli pour l'inscription
- Génère un lien unique valable **7 jours**
- Le client reçoit un email avec un bouton pour créer son compte
- L'invitation est marquée comme utilisée après inscription

---

## 7. Feedbacks (`feedback-dashboard`)

Lecture centralisée de tous les retours envoyés par les clients.

- Affiche : message, nom du restaurant, date
- Trié par date décroissante (plus récents en premier)
- Accessible uniquement au Super Admin

---

## 8. Liens de démonstration

Depuis le **tableau de bord** (`dashboard`), le Super Admin peut voir les tokens de démonstration actifs.

- Les liens de démo permettent à un visiteur de tester l'interface admin en mode lecture seule
- Les tokens expirés sont nettoyés automatiquement
- Les sessions de démo ont des restrictions (pas de modification de données)

---

## Sécurité

- **CSRF** — Toutes les actions POST sont protégées par un token CSRF
- **Vérification de rôle** — Chaque action Super Admin passe par `requireSuperAdmin()`
- **Protection brute-force** — Blocage IP + verrouillage de compte automatique
- **Suspension** — Blocage immédiat à la prochaine tentative de connexion
- **Dernière connexion** — `last_login_at` mis à jour à chaque login réussi
