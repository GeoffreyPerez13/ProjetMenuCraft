<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Mentions Légales') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <style>
        .legal-page { max-width: 800px; margin: 40px auto; padding: 0 24px; }
        .legal-page h1 { font-size: 1.8rem; margin-bottom: 24px; color: var(--color-primary); }
        .legal-page h2 { font-size: 1.2rem; margin: 24px 0 12px; color: var(--color-text); }
        .legal-page p, .legal-page li { font-size: 0.9rem; color: var(--color-text-light); line-height: 1.8; margin-bottom: 12px; }
        .legal-page ul { padding-left: 24px; }
        .legal-nav { display: flex; gap: 8px; margin-bottom: 32px; flex-wrap: wrap; }
        .legal-nav a { padding: 8px 16px; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 500; border: 1px solid var(--color-border); color: var(--color-text-light); }
        .legal-nav a.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
    </style>
</head>
<body>
<div class="legal-page">
    <a href="<?= APP_URL ?>" style="display:inline-flex;align-items:center;gap:8px;color:var(--color-primary);font-size:0.85rem;margin-bottom:24px;">
        <i class="fas fa-arrow-left"></i> Retour à l'accueil
    </a>

    <div class="legal-nav">
        <a href="<?= APP_URL ?>?page=legal&section=cgu" class="<?= ($section ?? '') === 'cgu' ? 'active' : '' ?>">CGU</a>
        <a href="<?= APP_URL ?>?page=legal&section=privacy" class="<?= ($section ?? '') === 'privacy' ? 'active' : '' ?>">Confidentialité</a>
        <a href="<?= APP_URL ?>?page=legal&section=cookies" class="<?= ($section ?? '') === 'cookies' ? 'active' : '' ?>">Cookies</a>
        <a href="<?= APP_URL ?>?page=legal&section=legal" class="<?= ($section ?? '') === 'legal' ? 'active' : '' ?>">Mentions Légales</a>
    </div>

    <?php if (($section ?? '') === 'cgu'): ?>
    <h1>Conditions Générales d'Utilisation</h1>
    <p><em>Dernière mise à jour : <?= date('d/m/Y') ?></em></p>

    <h2>1. Objet</h2>
    <p>Les présentes CGU régissent l'utilisation de la plateforme MenuCraft, service en ligne de création de sites vitrines pour restaurateurs.</p>

    <h2>2. Acceptation</h2>
    <p>L'inscription et l'utilisation de MenuCraft impliquent l'acceptation pleine et entière des présentes conditions.</p>

    <h2>3. Services proposés</h2>
    <p>MenuCraft met à disposition des restaurateurs une interface permettant de créer et gérer un site vitrine avec : carte en ligne, horaires, contact, réservations, et autres fonctionnalités.</p>

    <h2>4. Responsabilité</h2>
    <p>Le contenu publié par les restaurateurs reste sous leur entière responsabilité. MenuCraft ne peut être tenu responsable des informations inexactes publiées par ses utilisateurs.</p>

    <h2>5. Propriété intellectuelle</h2>
    <p>Le code source, le design et les éléments graphiques de MenuCraft sont la propriété exclusive de l'éditeur. Les contenus uploadés par les restaurateurs restent leur propriété.</p>

    <h2>6. Résiliation</h2>
    <p>Tout utilisateur peut supprimer son compte à tout moment. MenuCraft se réserve le droit de suspendre ou résilier un compte en cas de non-respect des présentes CGU.</p>

    <?php elseif (($section ?? '') === 'privacy'): ?>
    <h1>Politique de Confidentialité</h1>
    <p><em>Dernière mise à jour : <?= date('d/m/Y') ?></em></p>

    <h2>1. Données collectées</h2>
    <p>Nous collectons les données suivantes :</p>
    <ul>
        <li>Nom d'utilisateur, email, nom du restaurant</li>
        <li>Contenu du site (carte, photos, horaires)</li>
        <li>Données de navigation anonymisées (statistiques)</li>
    </ul>

    <h2>2. Utilisation des données</h2>
    <p>Vos données sont utilisées pour :</p>
    <ul>
        <li>Fournir et maintenir le service</li>
        <li>Envoyer des notifications liées au service</li>
        <li>Améliorer l'expérience utilisateur</li>
    </ul>

    <h2>3. Conservation</h2>
    <p>Les données sont conservées tant que votre compte est actif. Elles sont supprimées dans un délai de 30 jours après la suppression du compte.</p>

    <h2>4. Droits</h2>
    <p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification, de suppression et de portabilité de vos données. Contactez-nous à contact.menucraft@gmail.com.</p>

    <?php elseif (($section ?? '') === 'cookies'): ?>
    <h1>Politique des Cookies</h1>
    <p><em>Dernière mise à jour : <?= date('d/m/Y') ?></em></p>

    <h2>1. Cookies utilisés</h2>
    <ul>
        <li><strong>Cookies essentiels :</strong> Session PHP (PHPSESSID) — nécessaire au fonctionnement.</li>
        <li><strong>Cookies de préférence :</strong> Mode sombre, consentement cookies.</li>
        <li><strong>Cookies analytiques :</strong> Statistiques de visites anonymisées (si premium activé).</li>
    </ul>

    <h2>2. Gestion des cookies</h2>
    <p>Vous pouvez gérer vos préférences de cookies via la bannière affichée lors de votre première visite, ou via les paramètres de votre navigateur.</p>

    <?php else: ?>
    <h1>Mentions Légales</h1>

    <h2>Éditeur</h2>
    <p>MenuCraft — Plateforme SaaS de création de sites vitrines pour restaurateurs.</p>
    <p>Email : contact.menucraft@gmail.com</p>

    <h2>Hébergement</h2>
    <p>L'application est hébergée en France sur des serveurs conformes au RGPD.</p>

    <h2>Directeur de la publication</h2>
    <p>Le directeur de la publication est le fondateur de MenuCraft.</p>
    <?php endif; ?>

    <div style="margin-top:40px;padding-top:20px;border-top:1px solid var(--color-border);text-align:center;">
        <p style="font-size:0.8rem;color:var(--color-text-muted);">© <?= date('Y') ?> MenuCraft — Tous droits réservés</p>
    </div>
</div>
</body>
</html>
