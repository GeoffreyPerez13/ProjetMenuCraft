<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Inscription') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-logo">
            <h1><i class="fas fa-utensils"></i> MenuCraft</h1>
        </div>
        <h2 class="auth-title">Créer votre compte</h2>

        <div style="background:var(--color-primary-bg);padding:12px 16px;border-radius:var(--radius-sm);margin-bottom:var(--spacing-lg);font-size:0.85rem;">
            <i class="fas fa-store" style="color:var(--color-primary);"></i>
            Restaurant : <strong><?= htmlspecialchars($invitation->restaurant_name ?? '') ?></strong>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="flash-message <?= $flash['type'] ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>?page=register&token=<?= htmlspecialchars($token ?? '') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required placeholder="Min. 3 caractères" minlength="3">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min. 8 caractères" minlength="8">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmer</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Confirmer">
                </div>
            </div>

            <p class="form-hint" style="margin-bottom:16px;">
                Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial.
            </p>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-user-plus"></i> Créer mon compte
            </button>
        </form>

        <div class="auth-footer">
            <a href="<?= APP_URL ?>" style="color:var(--color-text-muted);font-size:0.8rem;">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>
</body>
</html>
