<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — MenuCraft</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <h1><i class="fas fa-utensils"></i> MenuCraft</h1>
        </div>
        <h2 class="auth-title">Mot de passe oublié</h2>

        <?php if (!empty($flash)): ?>
            <div class="flash-message <?= $flash['type'] ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>

        <p style="color:var(--color-text-muted);font-size:0.9rem;margin-bottom:var(--spacing-lg);text-align:center;">
            Entrez votre email pour recevoir un lien de réinitialisation.
        </p>

        <form method="POST" action="<?= APP_URL ?>?page=reset-password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" class="form-control" required placeholder="votre@email.com" autofocus>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-paper-plane"></i> Envoyer le lien
            </button>
        </form>

        <div class="auth-footer">
            <a href="<?= APP_URL ?>?page=login">
                <i class="fas fa-arrow-left"></i> Retour à la connexion
            </a>
        </div>
    </div>
</div>
</body>
</html>
