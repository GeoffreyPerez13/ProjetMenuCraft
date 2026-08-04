<?php
if (!isset($pageTitle)) $pageTitle = 'Inscription';
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($flash)) $flash = null;
if (!isset($old)) $old = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="max-width: 500px;">
        <div class="auth-logo">
            <h1><i class="fas fa-utensils"></i> MenuCraft</h1>
        </div>
        <h2 class="auth-title">Créer votre compte</h2>

        <?php if (!empty($flash)): ?>
            <div class="flash-message <?= $flash['type'] ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>?page=auto-register">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="form-control" required
                       value="<?= htmlspecialchars($old['username'] ?? '') ?>" placeholder="Min. 3 caractères" minlength="3">
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="votre@email.com">
            </div>

            <div class="form-group">
                <label for="restaurant_name"><i class="fas fa-store"></i> Nom du restaurant</label>
                <input type="text" id="restaurant_name" name="restaurant_name" class="form-control" required
                       value="<?= htmlspecialchars($old['restaurant_name'] ?? '') ?>" placeholder="Le nom de votre restaurant">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="Min. 8 caractères" minlength="8">
                </div>
                <div class="form-group">
                    <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmer</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required
                           placeholder="Confirmer le mot de passe">
                </div>
            </div>

            <p class="form-hint" style="margin-bottom: 16px;">
                Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 chiffre et 1 caractère spécial.
            </p>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-user-plus"></i> Créer mon compte
            </button>
        </form>

        <div class="auth-footer">
            Déjà un compte ?
            <a href="<?= APP_URL ?>?page=login">Se connecter</a>
            <br><br>
            <a href="<?= APP_URL ?>" style="color: var(--color-text-muted); font-size: 0.8rem;">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>
</body>
</html>
