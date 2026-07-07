<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h2><i class="fas fa-envelope-open-text"></i> Envoyer une invitation</h2>
    </div>

    <form method="POST" action="<?= APP_URL ?>?page=send-invitation">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email du restaurateur</label>
            <input type="email" name="email" class="form-control" required placeholder="restaurateur@email.com">
        </div>

        <div class="form-group">
            <label><i class="fas fa-store"></i> Nom du restaurant</label>
            <input type="text" name="restaurant_name" class="form-control" required placeholder="Le Petit Bistro">
        </div>

        <p class="form-hint" style="margin-bottom:16px;">
            Un email d'invitation sera envoyé avec un lien valable 7 jours pour créer un compte.
        </p>

        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-paper-plane"></i> Envoyer l'invitation
        </button>
    </form>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
