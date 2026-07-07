<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Démo expirée — MenuCraft</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="text-align:center;">
        <div style="font-size:4rem;color:var(--color-warning);margin-bottom:16px;">
            <i class="fas fa-hourglass-end"></i>
        </div>
        <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:8px;">Démonstration expirée</h1>
        <p style="color:var(--color-text-muted);margin-bottom:24px;">Ce lien de démonstration a expiré ou n'est plus valide.</p>
        <?php if (!empty($flash)): ?>
        <div class="flash-message <?= $flash['type'] ?>" style="margin-bottom:16px;">
            <i class="fas fa-exclamation-circle"></i> <?= $flash['message'] ?>
        </div>
        <?php endif; ?>
        <a href="<?= APP_URL ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
    </div>
</div>
</body>
</html>
