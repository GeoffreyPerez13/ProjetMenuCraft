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

        <form method="POST" action="<?= APP_URL ?>?page=reset-password-admin&amp;token=<?= htmlspecialchars($token ?? ($_GET['token'] ?? '')) ?>" id="resetPwdForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                <div style="position:relative;">
                    <input type="password" id="newPwd" name="password" class="form-control" required placeholder="Min. 8 caractères" minlength="8" style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePwd(this)" title="Afficher/Masquer"><i class="fas fa-eye"></i></button>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Confirmer le mot de passe</label>
                <div style="position:relative;">
                    <input type="password" id="confirmPwd" name="password_confirmation" class="form-control" required placeholder="Confirmer" style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePwd(this)" title="Afficher/Masquer"><i class="fas fa-eye"></i></button>
                </div>
            </div>

            <div class="pwd-rules" id="pwdRules">
                <div class="pwd-rule" id="ruleLength"><i class="fas fa-circle"></i> Min. 8 caractères</div>
                <div class="pwd-rule" id="ruleUpper"><i class="fas fa-circle"></i> 1 majuscule</div>
                <div class="pwd-rule" id="ruleDigit"><i class="fas fa-circle"></i> 1 chiffre</div>
                <div class="pwd-rule" id="ruleSpecial"><i class="fas fa-circle"></i> 1 caractère spécial</div>
                <div class="pwd-rule" id="ruleMatch"><i class="fas fa-circle"></i> Les mots de passe correspondent</div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="resetSubmitBtn" disabled>
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
<style>
.pwd-toggle-btn {
    position:absolute;right:10px;top:50%;transform:translateY(-50%);
    background:none;border:none;cursor:pointer;color:var(--color-text-muted);font-size:1rem;padding:4px;
}
.pwd-rules {
    display:grid;grid-template-columns:1fr 1fr;gap:6px 16px;
    margin-bottom:16px;font-size:0.82rem;
}
.pwd-rule { display:flex;align-items:center;gap:6px;color:var(--color-text-muted);transition:color 0.2s; }
.pwd-rule i { font-size:0.5rem;transition:color 0.2s; }
.pwd-rule.valid { color:#16a34a; }
.pwd-rule.valid i { color:#16a34a; }
.pwd-rule.invalid { color:#dc2626; }
.pwd-rule.invalid i { color:#dc2626; }
@media(max-width:480px){
    .pwd-rules { grid-template-columns:1fr;gap:4px; }
}
</style>
<script>
function togglePwd(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fas fa-eye'; }
}

const newPwd = document.getElementById('newPwd');
const confirmPwd = document.getElementById('confirmPwd');
const submitBtn = document.getElementById('resetSubmitBtn');

const rules = {
    ruleLength:  v => v.length >= 8,
    ruleUpper:   v => /[A-Z]/.test(v),
    ruleDigit:   v => /[0-9]/.test(v),
    ruleSpecial: v => /[^A-Za-z0-9]/.test(v),
    ruleMatch:   (v, c) => v.length > 0 && v === c
};

function checkRules() {
    const v = newPwd.value;
    const c = confirmPwd.value;
    let allValid = true;
    for (const [id, fn] of Object.entries(rules)) {
        const el = document.getElementById(id);
        const ok = fn(v, c);
        el.classList.toggle('valid', ok);
        el.classList.toggle('invalid', !ok && v.length > 0);
        el.querySelector('i').className = ok ? 'fas fa-check-circle' : (v.length > 0 ? 'fas fa-times-circle' : 'fas fa-circle');
        if (!ok) allValid = false;
    }
    submitBtn.disabled = !allValid;
}

newPwd.addEventListener('input', checkRules);
confirmPwd.addEventListener('input', checkRules);

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
