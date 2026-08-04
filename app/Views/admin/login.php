<?php
if (!isset($pageTitle)) $pageTitle = 'Connexion';
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($flash)) $flash = null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= rtrim(APP_URL, '/') ?>/assets/css/admin.css">
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
<?php $baseUrl = rtrim(APP_URL, '/') . '/'; ?>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <h1><i class="fas fa-utensils"></i> MenuCraft</h1>
        </div>
        <h2 class="auth-title">Connexion</h2>

        <?php if (!empty($flash)): ?>
            <div class="flash-message <?= $flash['type'] ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $baseUrl ?>?page=login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="Votre identifiant">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Votre mot de passe" style="padding-right:40px;">
                    <button type="button" id="togglePassword" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--color-text-muted);font-size:1rem;" aria-label="Afficher le mot de passe">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div style="text-align: center; margin-top: 16px;">
            <a href="<?= $baseUrl ?>?page=reset-password" style="font-size: 0.85rem; color: var(--color-text-muted);">
                Mot de passe oublié ?
            </a>
        </div>

        <div class="auth-footer">
            <a href="<?= $baseUrl ?>" style="color: var(--color-text-muted); font-size: 0.8rem;">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>

    <button type="button" id="darkModeToggle" style="position:absolute;top:16px;right:16px;background:var(--color-bg-alt, #f3f4f6);border:1px solid var(--color-border, #e5e7eb);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--color-text, #1f2937);transition:all 0.2s;" aria-label="Basculer mode sombre">
        <i class="fas fa-moon"></i>
    </button>
</div>
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// Dark mode toggle
const dmToggle = document.getElementById('darkModeToggle');
const dmIcon = dmToggle.querySelector('i');
function updateDmIcon() {
    const isDark = document.documentElement.classList.contains('dark-mode');
    dmIcon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}
updateDmIcon();
dmToggle.addEventListener('click', function() {
    document.documentElement.classList.toggle('dark-mode');
    const isDark = document.documentElement.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark ? 'true' : 'false');
    updateDmIcon();
});
</script>
</body>
</html>
