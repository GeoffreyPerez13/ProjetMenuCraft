<?php
if (!isset($pageTitle)) $pageTitle = 'Inscription';
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($flash)) $flash = null;
if (!isset($invitation)) $invitation = null;
if (!isset($token)) $token = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
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

        <form method="POST" action="<?= APP_URL ?>?page=register&amp;token=<?= htmlspecialchars($token ?? '') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required placeholder="Min. 3 caractères" minlength="3">
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Mot de passe</label>
                <div style="position:relative;">
                    <input type="password" id="regPwd" name="password" class="form-control" required placeholder="Min. 8 caractères" minlength="8" style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePwd(this)" title="Afficher/Masquer"><i class="fas fa-eye"></i></button>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Confirmer le mot de passe</label>
                <div style="position:relative;">
                    <input type="password" id="regConfirm" name="password_confirmation" class="form-control" required placeholder="Retapez votre mot de passe" style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePwd(this)" title="Afficher/Masquer"><i class="fas fa-eye"></i></button>
                </div>
            </div>

            <div class="reg-pwd-rules" id="regPwdRules">
                <div class="reg-rule" id="regRuleLength"><i class="fas fa-circle"></i> Min. 8 caractères</div>
                <div class="reg-rule" id="regRuleUpper"><i class="fas fa-circle"></i> 1 majuscule</div>
                <div class="reg-rule" id="regRuleDigit"><i class="fas fa-circle"></i> 1 chiffre</div>
                <div class="reg-rule" id="regRuleSpecial"><i class="fas fa-circle"></i> 1 caractère spécial</div>
                <div class="reg-rule" id="regRuleMatch"><i class="fas fa-circle"></i> Mots de passe identiques</div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="regSubmitBtn" disabled>
                <i class="fas fa-user-plus"></i> Créer mon compte
            </button>
        </form>

        <div class="auth-footer">
            <a href="<?= APP_URL ?>" style="color:var(--color-text-muted);font-size:0.8rem;">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>

    <button type="button" id="darkModeToggle" style="position:absolute;top:16px;right:16px;background:var(--color-bg-alt, #f3f4f6);border:1px solid var(--color-border, #e5e7eb);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--color-text, #1f2937);transition:all 0.2s;" aria-label="Basculer mode sombre">
        <i class="fas fa-moon"></i>
    </button>
</div>
<style>
.pwd-toggle-btn { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--color-text-muted);font-size:1rem;padding:4px; }
.reg-pwd-rules { display:grid;grid-template-columns:1fr 1fr;gap:4px 16px;margin-bottom:16px;font-size:0.82rem; }
.reg-rule { display:flex;align-items:center;gap:6px;color:var(--color-text-muted);transition:color 0.2s; }
.reg-rule i { font-size:0.5rem;transition:color 0.2s; }
.reg-rule.valid { color:#16a34a; } .reg-rule.valid i { color:#16a34a; }
.reg-rule.invalid { color:#dc2626; } .reg-rule.invalid i { color:#dc2626; }
@media(max-width:480px){ .reg-pwd-rules { grid-template-columns:1fr;gap:3px; } }
</style>
<script>
function togglePwd(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fas fa-eye'; }
}
(function(){
    const np = document.getElementById('regPwd');
    const cp = document.getElementById('regConfirm');
    const btn = document.getElementById('regSubmitBtn');
    const rules = {
        regRuleLength:  v => v.length >= 8,
        regRuleUpper:   v => /[A-Z]/.test(v),
        regRuleDigit:   v => /[0-9]/.test(v),
        regRuleSpecial: v => /[^A-Za-z0-9]/.test(v),
        regRuleMatch:   (v,c) => v.length > 0 && v === c
    };
    function check() {
        const v = np.value, c = cp.value;
        let ok = true;
        for (const [id, fn] of Object.entries(rules)) {
            const el = document.getElementById(id);
            const pass = fn(v, c);
            el.classList.toggle('valid', pass);
            el.classList.toggle('invalid', !pass && v.length > 0);
            el.querySelector('i').className = pass ? 'fas fa-check-circle' : (v.length > 0 ? 'fas fa-times-circle' : 'fas fa-circle');
            if (!pass) ok = false;
        }
        btn.disabled = !ok;
    }
    np.addEventListener('input', check);
    cp.addEventListener('input', check);
})();

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
