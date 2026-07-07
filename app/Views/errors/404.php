<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — MenuCraft</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="text-align:center;">
        <div style="font-size:5rem;color:var(--color-primary);margin-bottom:16px;">
            <i class="fas fa-map-signs"></i>
        </div>
        <h1 style="font-size:2rem;font-weight:800;margin-bottom:8px;">404</h1>
        <p style="color:var(--color-text-muted);margin-bottom:24px;">La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <a href="<?= APP_URL ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
    </div>
</div>
</body>
</html>
