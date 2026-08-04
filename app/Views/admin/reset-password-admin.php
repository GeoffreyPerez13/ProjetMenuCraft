<?php
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($flash)) $flash = null;
if (!isset($token)) $token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe — MenuCraft</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <h1><i class="fas fa-utensils"></i> MenuCraft</h1>
        </div>
        <h2 class="auth-title">Nouveau mot de passe</h2>

        <?php if (!empty($flash)): ?>
            <div class="flash-message <?= $flash['type'] ?>">
                <i class="fas fa-exclamation-circle"></i>
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>?page=reset-password-admin&token=<?= htmlspecialchars($token ?? ($_GET['token'] ?? '')) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" required placeholder="Min. 8 caractères" minlength="8">
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="Confirmer">
            </div>

            <p class="form-hint" style="margin-bottom:16px;">
                Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial.
            </p>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-save"></i> Réinitialiser
            </button>
        </form>

        <div class="auth-footer">
            <a href="<?= APP_URL ?>?page=login">
                <i class="fas fa-arrow-left"></i> Retour à la connexion
            </a>
        </div>
    </div>

    <button type="button" id="darkModeToggle" style="position:absolute;top:16px;right:16px;background:var(--color-bg-alt, #f3f4f6);border:1px solid var(--color-border, #e5e7eb);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--color-text, #1f2937);transition:all 0.2s;" aria-label="Basculer mode sombre">
        <i class="fas fa-moon"></i>
    </button>
</div>
<script>
const dmToggle = document.getElementById('darkModeToggle');
const dmIcon = dmToggle.querySelector('i');
function updateDmIcon() {
    dmIcon.className = document.documentElement.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
}
updateDmIcon();
dmToggle.addEventListener('click', function() {
    document.documentElement.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark-mode') ? 'true' : 'false');
    updateDmIcon();
});
</script>
</body>
</html>
