# MenuCraft Ecosystem — Spécifications Techniques & Architecturales

Document d'architecture synthétique pour les 3 extensions de la plateforme **MenuCraft** (SaaS Restaurateurs).

---

## 1. Vision & Synergie Globale

Les trois nouveaux modules (**MenuCraft POS**, **MenuCraft Pay**, **MenuCraft Order**) s'intègrent nativement à la stack existante :
- **Backend** : PHP 8.x (procédural structuré avec classes, sans framework)
- **Base de données** : MySQL 8.x via PDO (prepared statements uniquement)
- **Frontend** : CSS Custom + Vanilla JS (WebSockets / Server-Sent Events pour le temps réel)
- **Paiements** : Stripe API via cURL + Intégration CONECS (Titres-Restaurant)

---

## 2. Synthèse des Modules

| Module | Fonction Principale | Cible Utilisateur |
| :--- | :--- | :--- |
| **MenuCraft (Base)** | Site vitrine, carte en ligne, réservations, back-office admin | Visiteurs web & Restaurateur |
| **MenuCraft POS** | Prise de commande serveurs, encaissement, Z de caisse, facturation | Serveurs & Gérant |
| **MenuCraft Pay** | Consultation d'addition et paiement mobile à table par QR Code | Client final en table |
| **MenuCraft Order** | Commande autonome à table via smartphone, panier de groupe | Client final en table |

---

## 3. Spécifications Détaillées des Modules

### A. MenuCraft POS — Caisse & Service en Salle

Application d'encaissement et de gestion de salle synchronisée en temps réel entre les terminaux mobiles du personnel et le poste central.

```
[Smartphone Serveur] ──(SSE/WebSockets)──> [Serveur Central / BDD] <──(SSE)── [Poste Caisse Central]
                                                     │
                                                     ├──> [Imprimante Cuisine (ESC/POS)]
                                                     └──> [Génération PDF Factures / Z de Caisse]
```

#### 1. Prise de Commande & Plan de Salle
- **Plan de salle interactif** : Synchronisation avec le module `floor-plan` (statuts : *Libre, Occupée, Addition demandée, Impayée*).
- **Saisie tactile rapide** : Sélection des plats par catégories, gestion des cuissons, accompagnements, suppléments et évictions d'allergènes.
- **Gestion des réclames/suites** : Saisie de remarques et envoi des ordres de préparation (*Envoyer Entrées*, *Envoyer Plats*).
- **Impression distante / KDS** : Envoi automatique des tickets vers l'imprimante thermique de cuisine/bar ou affichage sur écran KDS.

#### 2. Encaissement & Conformité Fiscale (NF525)
- **Divisions d'additions** : Paiement égalitaire ($N$ personnes) ou paiement à la pièce (sélection d'articles consommés).
- **Multi-règlements** : Carte bancaire, Espèces (calcul automatique du rendu), Titres-Restaurant (papier & cartes CONECS), Chèques vacances.
- **Facturation légale PDF** : Notes proforma, factures nominatives PDF avec TVA ventilée (5,5 %, 10 %, 20 %) et numérotation séquentielle inaltérable.

#### 3. Rapports de Ventes & Comptabilité
- **Clôtures de caisse** : Clôture journalière (Z de Caisse) et rapport de situation (X de Caisse).
- **Analytique détaillée** : Exports par plage personnalisée (Jour, Semaine, Mois, Année) du CA HT/TTC, ventilation TVA, répartition modes de paiement, top des ventes.
- **Formats d'export** : PDF, CSV, XLSX.

---

### B. MenuCraft Pay — Paiement QR Code à Table

Solution web *frictionless* (sans application à télécharger) permettant au client de consulter son addition et de payer directement depuis son smartphone.

```
[Client scanne QR Table 12] ──> [Interface Web Pay] ──> [API Stripe / CONECS]
                                                              │
[Alerte POS / Plan de salle] <────── (Webhook Stripe) ────────┘
```

#### 1. Parcours Client & Expérience de Paiement
- **Accès par QR Code** : Lien direct attribué à la table (ex: `?page=pay&table=12`).
- **Consultation de l'addition** : Affichage dynamique du ticket de caisse ouvert sur le POS.
- **Flexibilité du règlement** :
  - *Tout payer* : Règlement intégral du solde.
  - *Payer sa part* : Sélection explicite des produits consommés.
  - *Diviser l'addition* : Saisie d'un montant libre ou d'une fraction ($1/2$, $1/3$, $1/4$).
- **Pourboire (Tip) dynamique** : Suggestions configurables (5 %, 10 %, montant libre).
- **Reçu & Facture PDF** : Envoi automatique par email après saisie de l'adresse par le client.

#### 2. Intégrations des Passerelles de Paiement
- **Cartes bancaires & Wallets** : Stripe API, Apple Pay, Google Pay.
- **Titres-Restaurant Dématérialisés** : Swile, Edenred, Resto Flash via réseaux CONECS / Stripe. Gestion automatique du plafond journalier (25 €) avec complément CB automatique.

#### 3. Alertes & Rapprochement
- **Push Instantané POS** : Notification sonore/visuelle en caisse et mise à jour de l'état de la table (*Partiellement payée / Libérée*).
- **Reporting dédié** : Suivi des frais de transaction et commissions, consolidation financière.

---

### C. MenuCraft Order — Commande Directe à Table

Application web de commande autonome à table permettant d'accélérer le service et d'augmenter le panier moyen.

```
[Client à Table] ──> [Menu Digital interactif] ──> [Option: MenuCraft Pay (Pay-First)]
                                                        │
[Impression Cuisine / POS] <── (Envoi Commande) <───────┘
```

#### 1. Prise de Commande Autonome
- **Menu digital enrichi** : Photos HD, fiches allergènes, filtres (Végan, Fait maison, Halal, Sans gluten).
- **Synchro Stocks / Ruptures** : Mise en rupture automatique si le plat est épuisé sur le POS.
- **Panier de groupe collaboratif** : Affichage des ajouts faits par les convives de la même table.

#### 2. Modes d'Exécution & Sécurité
- **Configuration au choix du restaurateur** :
  - *Mode Pay-First (Commande + Paiement)* : Le client règle via MenuCraft Pay avant l'envoi en cuisine.
  - *Mode Pay-Later (Commande seule)* : La commande est envoyée en cuisine et s'ajoute à l'addition de la table sur le POS.
- **Anti-fraude** : Geofencing GPS optionnel et mode de validation préalable par le serveur.

---

## 4. Modèle de Données Consolidé (MySQL 8)

```sql
-- Structure du plan de salle et QR Codes
CREATE TABLE pos_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    room_id INT NOT NULL,
    table_number VARCHAR(10) NOT NULL,
    qr_token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('free', 'occupied', 'bill_requested', 'paid') DEFAULT 'free',
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Commandes / Additions
CREATE TABLE pos_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    table_id INT NOT NULL,
    status ENUM('pending', 'in_preparation', 'served', 'closed', 'cancelled') DEFAULT 'pending',
    total_ht DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_ttc DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES pos_tables(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Détails des articles dans les commandes
CREATE TABLE pos_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    notes TEXT NULL,
    options_json JSON NULL,
    FOREIGN KEY (order_id) REFERENCES pos_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historique des règlements
CREATE TABLE pos_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cb', 'cash', 'meal_ticket', 'holiday_check', 'stripe_qr') NOT NULL,
    stripe_payment_id VARCHAR(255) NULL,
    status ENUM('success', 'failed', 'refunded') DEFAULT 'success',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES pos_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clôtures de caisse comptables (Z/X)
CREATE TABLE pos_closures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    type ENUM('X', 'Z') NOT NULL,
    total_ht DECIMAL(10,2) NOT NULL,
    total_ttc DECIMAL(10,2) NOT NULL,
    tax_details_json JSON NOT NULL,
    closed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
