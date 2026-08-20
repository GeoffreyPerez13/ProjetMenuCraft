# Admin (Client) — Fiche de route

> Rôle : `ADMIN` — Gestion du site vitrine et du restaurant sur MenuCraft.
> Chaque Admin gère **son propre restaurant** : carte, réservations, apparence, etc.

---

## Accès rapide (sidebar)

| Page | Route | Description |
|------|-------|-------------|
| Tableau de bord | `?page=dashboard` | Vue d'ensemble du restaurant |
| Carte | `?page=edit-card` | Gestion des catégories, plats et menus du jour |
| Contact | `?page=edit-contact` | Informations de contact du restaurant |
| Logo & Bannière | `?page=edit-logo-banner` | Upload du logo et de la bannière |
| Services | `?page=edit-services` | Activation/désactivation des services proposés |
| Template | `?page=edit-template` | Personnalisation de l'apparence du site |
| Réservations | `?page=reservations` | Gestion des réservations en ligne *(premium)* |
| Statistiques | `?page=stats` | Visites et données analytiques *(premium)* |
| Plan de salle | `?page=floor-plan` | Éditeur visuel du plan de salle *(premium)* |
| Paramètres | `?page=settings` | Profil, mot de passe, options, abonnement |
| Feedback | `?page=feedback` | Envoyer un retour à l'équipe MenuCraft |

---

## 1. Tableau de bord (`dashboard`)

Page d'accueil après connexion. Résumé de l'état du restaurant :

- **Statut du site** — En ligne / Hors ligne
- **Restaurant** — Nom et lien vers le site public
- **Abonnement** — Type de plan et statut
- **Réservations en attente** — Nombre de réservations à traiter (si premium)

---

## 2. Carte du restaurant (`edit-card`)

Gestion complète du menu affiché sur le site public.

### Catégories
- **Créer** une catégorie (nom, description optionnelle)
- **Modifier** le nom et la description
- **Supprimer** une catégorie (et ses plats associés)
- **Réordonner** par glisser-déposer (`?page=reorder-categories`)
- **Actions par lot** (`?page=batch-categories`)

### Plats
- **Ajouter** un plat à une catégorie (nom, description, prix)
- **Modifier** les informations d'un plat
- **Supprimer** un plat
- **Réordonner** les plats dans une catégorie (`?page=reorder-dishes`)
- **Actions par lot** (`?page=batch-dishes`)
- **Image** — Upload et suppression d'image pour chaque plat (`?page=upload-card-image`, `?page=delete-card-image`)

### Menus du jour
- **Créer** un menu du jour (titre, contenu, prix)
- **Modifier** / **Supprimer** un menu du jour
- **Activer/Désactiver** l'affichage (`?page=toggle-daily-menu`)
- **Réordonner** les menus du jour (`?page=reorder-daily-menus`)

### Prévisualisation
- `?page=view-card` — Aperçu de la carte telle qu'elle apparaît sur le site public

---

## 3. Contact (`edit-contact`)

Gestion des informations de contact affichées sur le site public :

- **Adresse** du restaurant
- **Téléphone**
- **Email**
- **Horaires d'ouverture**
- **Liens réseaux sociaux** (Facebook, Instagram, etc.)
- **Coordonnées GPS** (pour la carte interactive)

---

## 4. Logo & Bannière (`edit-logo-banner`)

Gestion des éléments visuels principaux :

| Action | Route | Description |
|--------|-------|-------------|
| Upload logo | `?page=upload-logo` | Image de marque du restaurant |
| Supprimer logo | `?page=delete-logo` | Retirer le logo actuel |
| Upload bannière | `?page=upload-banner` | Image d'en-tête du site |
| Supprimer bannière | `?page=delete-banner` | Retirer la bannière actuelle |
| Texte bannière | `?page=save-banner-text` | Texte superposé sur la bannière |

---

## 5. Services (`edit-services`)

Activation/désactivation des services proposés par le restaurant :

- Exemples : Wi-Fi, terrasse, parking, climatisation, livraison, etc.
- Chaque service est un toggle on/off
- Les services activés sont affichés sur le site public

---

## 6. Template (`edit-template`)

Personnalisation de l'apparence du site vitrine :

- **Palette de couleurs** — Choix parmi plusieurs thèmes prédéfinis (classic, modern, warm, etc.)
- **Layout** — Disposition de la page (standard, etc.)
- Prévisualisation en direct

---

## 7. Réservations (`reservations`) *(Premium)*

Gestion des réservations en ligne des clients du restaurant.

### Liste des réservations
- Affichage avec date, heure, nombre de couverts, nom du client, statut
- Filtres et tri disponibles

### Actions
| Action | Route | Description |
|--------|-------|-------------|
| Changer statut | `?page=reservation-update-status` | Confirmer, refuser ou marquer comme terminée |
| Compteur en attente | `?page=reservation-pending-count` | API JSON — nombre de réservations pending |
| Liste en attente | `?page=reservation-pending-list` | API JSON — détails des réservations pending |

### Notifications
- Badge dans la sidebar indiquant le nombre de réservations en attente
- Notifications en temps réel via `?page=notification-stream` (Server-Sent Events)

### Réservation publique
- `?page=public-booking` — Formulaire accessible aux visiteurs du site pour réserver une table

---

## 8. Statistiques (`stats`) *(Premium)*

Données analytiques sur la fréquentation du site :

- **Visites** — Nombre de visites du site public
- **Données API** — `?page=stats-data` retourne les données au format JSON pour les graphiques
- Graphiques interactifs (visites par jour, semaine, mois)

---

## 9. Plan de salle (`floor-plan`) *(Premium)*

Éditeur visuel pour configurer l'agencement du restaurant :

| Action | Route | Description |
|--------|-------|-------------|
| Voir/éditer | `?page=floor-plan` | Interface de l'éditeur |
| Sauvegarder | `?page=floor-plan-save` | Enregistrer la disposition des tables |
| Créer une salle | `?page=floor-plan-create-room` | Ajouter un nouvel espace |
| Renommer | `?page=floor-plan-rename-room` | Modifier le nom d'une salle |
| Supprimer | `?page=floor-plan-delete-room` | Retirer une salle |

---

## 10. Paramètres (`settings`)

### Sections disponibles

| Section | Description |
|---------|-------------|
| **Profil** | Nom d'utilisateur, email, nom du restaurant |
| **Mot de passe** | Changement de mot de passe (avec validation de complexité) |
| **Options** | Site en ligne/hors ligne, palette, layout, notifications email |
| **Premium** | Gestion de l'abonnement et des features premium *(non dispo pour Super Admin)* |

### Routes associées
- `?page=update-profile` — Mise à jour du profil
- `?page=update-password` — Changement de mot de passe
- `?page=update-options` — Mise à jour des options du site
- `?page=update-template` — Changement de template

---

## 11. Feedback (`feedback`)

Formulaire permettant d'envoyer un retour à l'équipe MenuCraft :

- `?page=feedback` — Affichage du formulaire
- `?page=submit-feedback` — Envoi du message
- Le message est enregistré en base et visible par le Super Admin dans le dashboard Feedbacks

---

## 12. Abonnement & Paiement

### Stripe
| Route | Description |
|-------|-------------|
| `?page=stripe-checkout` | Lancement du paiement Stripe |
| `?page=stripe-success` | Page de succès après paiement |
| `?page=stripe-cancel` | Annulation d'abonnement |
| `?page=stripe-reactivate` | Réactivation d'un abonnement annulé |

### Webhook
- `?page=stripe-webhook` — Endpoint pour recevoir les événements Stripe (paiement, annulation, etc.)

---

## 13. Site public (`display`)

Le site vitrine du restaurant accessible aux visiteurs :

- `?page=display` — Affichage du site public (carte, contact, services, réservation)
- Utilise le slug du restaurant pour l'URL publique
- Thème et layout personnalisés selon les paramètres de l'admin

---

## Pages publiques complémentaires

| Route | Description |
|-------|-------------|
| `?page=landing` | Page d'accueil de MenuCraft |
| `?page=legal` | Mentions légales |
| `?page=sitemap.xml` | Sitemap XML pour le SEO |
| `?page=reset-password` | Demande de réinitialisation de mot de passe |
| `?page=verify-email` | Vérification de l'adresse email |

---

## Sécurité

- **Authentification** — Login avec protection brute-force (blocage IP + verrouillage compte)
- **CSRF** — Token sur tous les formulaires POST
- **Vérification email** — Obligatoire avant la première connexion
- **Mot de passe** — Validation de complexité (longueur, caractères spéciaux, etc.)
- **Mode démo** — Accès lecture seule via lien temporaire, modifications bloquées
- **Annonces** — Bandeau global visible si le Super Admin publie une annonce
