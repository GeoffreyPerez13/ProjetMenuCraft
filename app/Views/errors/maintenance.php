<?php if (!isset($restaurantName)) $restaurantName = 'Restaurant'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site en maintenance — <?= htmlspecialchars($restaurantName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="auth-page" style="background:linear-gradient(135deg,#fef7ed,#fff);">
    <div class="auth-card" style="text-align:center;max-width:500px;">
        <div style="font-size:4rem;margin-bottom:16px;">🛠️</div>
        <h1 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:12px;">
            <?= htmlspecialchars($restaurantName ?? 'Restaurant') ?>
        </h1>
        <p style="color:var(--color-text-muted);font-size:1rem;margin-bottom:24px;">
            Notre site est actuellement en cours de mise à jour. Nous serons bientôt de retour !
        </p>
        <a href="<?= APP_URL ?>" class="btn btn-outline" style="margin-top:8px;">
            <i class="fas fa-home"></i> Retour à MenuCraft
        </a>
    </div>
</div>
</body>
</html>
