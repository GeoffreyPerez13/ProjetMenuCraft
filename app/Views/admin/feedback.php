<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php if (!isset($csrf_token)) $csrf_token = ''; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-comment-dots"></i> Donnez-nous votre avis</h2>
    </div>

    <form method="POST" action="<?= APP_URL ?>?page=submit-feedback">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-row">
            <div class="form-group">
                <label>Votre nom</label>
                <input type="text" name="name" class="form-control" placeholder="Optionnel">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Optionnel">
            </div>
        </div>

        <div class="form-group">
            <label>Note globale</label>
            <div style="display:flex;gap:8px;" id="ratingStars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <label style="cursor:pointer;font-size:1.8rem;color:var(--color-border);" data-star="<?= $i ?>">
                    <input type="radio" name="rating" value="<?= $i ?>" style="display:none;">
                    <i class="fas fa-star"></i>
                </label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Facilité d'utilisation</label>
            <select name="ease_of_use" class="form-control">
                <option value="">— Choisir —</option>
                <option value="very_easy">Très facile</option>
                <option value="easy">Facile</option>
                <option value="moderate">Moyen</option>
                <option value="difficult">Difficile</option>
            </select>
        </div>

        <div class="form-group">
            <label>Fonctionnalité préférée</label>
            <textarea name="favorite_feature" class="form-control" rows="2" placeholder="Qu'aimez-vous le plus ?"></textarea>
        </div>

        <div class="form-group">
            <label>Améliorations suggérées</label>
            <textarea name="improvements" class="form-control" rows="2" placeholder="Que pouvons-nous améliorer ?"></textarea>
        </div>

        <div class="form-group">
            <label>Commentaires supplémentaires</label>
            <textarea name="comments" class="form-control" rows="3" placeholder="Tout autre retour..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-paper-plane"></i> Envoyer mon feedback
        </button>
    </form>
</div>

<script>
document.querySelectorAll('#ratingStars label').forEach(label => {
    label.addEventListener('click', function() {
        const star = parseInt(this.dataset.star);
        document.querySelectorAll('#ratingStars label').forEach((l, i) => {
            l.style.color = i < star ? '#f59e0b' : 'var(--color-border)';
        });
        this.querySelector('input').checked = true;
    });
    label.addEventListener('mouseenter', function() {
        const star = parseInt(this.dataset.star);
        document.querySelectorAll('#ratingStars label').forEach((l, i) => {
            l.style.color = i < star ? '#f59e0b' : 'var(--color-border)';
        });
    });
});
document.getElementById('ratingStars').addEventListener('mouseleave', function() {
    const checked = this.querySelector('input:checked');
    const val = checked ? parseInt(checked.value) : 0;
    document.querySelectorAll('#ratingStars label').forEach((l, i) => {
        l.style.color = i < val ? '#f59e0b' : 'var(--color-border)';
    });
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
