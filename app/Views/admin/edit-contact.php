<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-address-book"></i> Informations de contact</h2>
    </div>

    <form method="POST" action="<?= APP_URL ?>?page=edit-contact" id="contactForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
                <input type="tel" id="telephone" name="telephone" class="form-control"
                       value="<?= htmlspecialchars($contact->telephone ?? '') ?>" placeholder="01 23 45 67 89">
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($contact->email ?? '') ?>" placeholder="contact@restaurant.com">
            </div>
        </div>

        <div class="form-group">
            <label for="adresse"><i class="fas fa-map-marker-alt"></i> Adresse</label>
            <textarea id="adresse" name="adresse" class="form-control" rows="2" placeholder="123 Rue de Paris, 75001 Paris"><?= htmlspecialchars($contact->adresse ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="horaires"><i class="fas fa-clock"></i> Horaires d'ouverture</label>
            <textarea id="horaires" name="horaires" class="form-control" rows="5" placeholder="Lundi - Vendredi : 12h00 - 14h30 / 19h00 - 22h30&#10;Samedi : 19h00 - 23h00&#10;Dimanche : Fermé"><?= htmlspecialchars($contact->horaires ?? '') ?></textarea>
            <p class="form-hint">Écrivez une ligne par jour ou groupe de jours</p>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Enregistrer
        </button>
    </form>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
