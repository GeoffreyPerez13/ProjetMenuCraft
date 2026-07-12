<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php
$serviceLabels = [
    'service_sur_place' => ['Sur place', 'fa-chair'],
    'service_a_emporter' => ['À emporter', 'fa-bag-shopping'],
    'service_livraison_ubereats' => ['Livraison Uber Eats', 'fa-motorcycle'],
    'service_livraison_etablissement' => ['Livraison propre', 'fa-truck'],
    'service_wifi' => ['WiFi', 'fa-wifi'],
    'service_climatisation' => ['Climatisation', 'fa-snowflake'],
    'service_pmr' => ['Accès PMR', 'fa-wheelchair'],
    'service_animaux' => ['Animaux acceptés', 'fa-paw'],
];
$paymentLabels = [
    'payment_visa' => ['Visa', 'fab fa-cc-visa'],
    'payment_mastercard' => ['Mastercard', 'fab fa-cc-mastercard'],
    'payment_cb' => ['CB', 'fas fa-credit-card'],
    'payment_especes' => ['Espèces', 'fas fa-money-bill-wave'],
    'payment_cheques' => ['Chèques', 'fas fa-money-check'],
    'payment_tickets_restaurant' => ['Tickets restaurant', 'fas fa-ticket'],
];
$socialLabels = [
    'social_instagram' => ['Instagram', 'fa-instagram'],
    'social_facebook' => ['Facebook', 'fa-facebook'],
    'social_x' => ['X (Twitter)', 'fa-x-twitter'],
    'social_tiktok' => ['TikTok', 'fa-tiktok'],
    'social_snapchat' => ['Snapchat', 'fa-snapchat'],
];
?>

<form method="POST" action="<?= APP_URL ?>?page=save-services">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <!-- Services -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-concierge-bell"></i> Services proposés</h2>
            <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllCheckboxes('services-grid')" title="Tout cocher / décocher">
                <i class="fas fa-check-double"></i> Tout cocher / décocher
            </button>
        </div>
        <div id="services-grid" class="services-grid">
            <?php foreach ($serviceLabels as $key => [$label, $icon]): ?>
            <label class="toggle-switch" style="padding:12px;background:var(--color-bg-alt);border-radius:var(--radius-sm);border:1px solid var(--color-border);">
                <input type="checkbox" name="<?= $key ?>" <?= ($options[$key] ?? '0') === '1' ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span><i class="fas <?= $icon ?>" style="margin-right:4px;color:var(--color-primary);"></i> <?= $label ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Paiements -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-credit-card"></i> Moyens de paiement</h2>
            <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllCheckboxes('payments-grid')" title="Tout cocher / décocher">
                <i class="fas fa-check-double"></i> Tout cocher / décocher
            </button>
        </div>
        <div id="payments-grid" class="services-grid">
            <?php foreach ($paymentLabels as $key => [$label, $icon]): ?>
            <label class="toggle-switch" style="padding:12px;background:var(--color-bg-alt);border-radius:var(--radius-sm);border:1px solid var(--color-border);">
                <input type="checkbox" name="<?= $key ?>" <?= ($options[$key] ?? '0') === '1' ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
                <span><i class="<?= $icon ?>" style="margin-right:4px;color:var(--color-primary);"></i> <?= $label ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Réseaux sociaux -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-share-alt"></i> Réseaux sociaux</h2>
        </div>
        <div class="grid grid-2">
            <?php foreach ($socialLabels as $key => [$label, $icon]): ?>
            <div class="form-group">
                <label><i class="fab <?= $icon ?>" style="color:var(--color-primary);"></i> <?= $label ?></label>
                <input type="url" name="<?= $key ?>" class="form-control"
                       value="<?= htmlspecialchars($options[$key] ?? '') ?>" placeholder="https://...">
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Enregistrer les services
    </button>
</form>

<script>
function toggleAllCheckboxes(gridId) {
    const boxes = document.querySelectorAll('#' + gridId + ' input[type="checkbox"]');
    const allChecked = Array.from(boxes).every(cb => cb.checked);
    boxes.forEach(cb => { cb.checked = !allChecked; });
}
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
