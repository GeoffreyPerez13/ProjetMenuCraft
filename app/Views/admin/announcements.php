<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php if (!isset($announcements)) $announcements = []; ?>

<div class="card" style="margin-bottom:var(--spacing-lg);">
    <div class="card-header">
        <h2><i class="fas fa-bullhorn"></i> Nouvelle annonce</h2>
    </div>
    <form method="POST" action="<?= APP_URL ?>?page=announcements" class="sa-announce-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="action" value="create">
        <textarea name="message" rows="3" placeholder="Rédigez votre annonce ici... Elle sera visible par tous les administrateurs." required></textarea>
        <div class="sa-announce-form-footer">
            <div class="sa-type-selector">
                <input type="radio" name="type" value="info" id="type-info" checked>
                <label for="type-info"><i class="fas fa-info-circle"></i> Information</label>
                <input type="radio" name="type" value="warning" id="type-warning">
                <label for="type-warning"><i class="fas fa-exclamation-triangle"></i> Avertissement</label>
                <input type="radio" name="type" value="danger" id="type-danger">
                <label for="type-danger"><i class="fas fa-exclamation-circle"></i> Urgent</label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-left:auto;"><i class="fas fa-paper-plane"></i> Publier</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Annonces existantes</h2>
        <span class="badge badge-info"><?= count($announcements) ?></span>
    </div>

    <?php if (empty($announcements)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-bullhorn"></i>
            <h3>Aucune annonce</h3>
            <p>Créez une annonce pour informer tous les administrateurs.</p>
        </div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($announcements as $ann): ?>
            <?php
                $annIcons = ['info' => 'info-circle', 'warning' => 'exclamation-triangle', 'danger' => 'exclamation-circle'];
                $annIconName = $annIcons[$ann->type] ?? 'info-circle';
            ?>
            <div class="sa-announce-card type-<?= $ann->type ?> <?= !$ann->is_active ? 'is-inactive' : '' ?>">
                <div class="sa-announce-icon">
                    <i class="fas fa-<?= $annIconName ?>"></i>
                </div>
                <div class="sa-announce-body">
                    <p><?= htmlspecialchars($ann->message) ?></p>
                    <div class="sa-announce-meta">
                        <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($ann->created_at)) ?></span>
                        <span class="badge <?= $ann->is_active ? 'badge-success' : 'badge-warning' ?>" style="font-size:0.7rem;">
                            <?= $ann->is_active ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>
                <div class="sa-announce-actions">
                    <form method="POST" action="<?= APP_URL ?>?page=announcements">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="announcement_id" value="<?= $ann->id ?>">
                        <button type="submit" class="btn btn-outline btn-sm" title="<?= $ann->is_active ? 'Désactiver' : 'Activer' ?>">
                            <i class="fas fa-<?= $ann->is_active ? 'eye-slash' : 'eye' ?>"></i>
                        </button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>?page=announcements" onsubmit="return confirm('Supprimer cette annonce ?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="announcement_id" value="<?= $ann->id ?>">
                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
