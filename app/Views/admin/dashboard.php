<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<?php
$slug = $restaurant->slug ?? '';
$siteUrl = APP_URL . '?page=display&slug=' . urlencode($slug);
$isOnline = ($siteOnline ?? '0') === '1';
$subStatus = $subscription->status ?? 'inactive';
$isSuperAdmin = ($admin->role ?? '') === 'SUPER_ADMIN';
?>

<!-- Résumé du restaurant -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-store"></i> <?= htmlspecialchars($admin->restaurant_name ?? 'Mon restaurant') ?></h2>
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="status-dot <?= $isOnline ? 'online' : 'offline' ?>"></span>
            <span style="font-size:0.85rem;color:var(--color-text-muted);">
                <?= $isOnline ? 'En ligne' : 'Hors ligne' ?>
            </span>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
            <p style="font-size:0.9rem;color:var(--color-text-light);">
                <i class="fas fa-link" style="color:var(--color-primary);"></i>
                Slug : <strong><?= htmlspecialchars($slug) ?></strong>
            </p>
            <?php if ($restaurant): ?>
            <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:4px;">
                Dernière mise à jour : <?= date('d/m/Y à H:i', strtotime($restaurant->updated_at ?? $restaurant->created_at)) ?>
            </p>
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if ($slug): ?>
            <a href="<?= $siteUrl ?>" target="_blank" class="btn btn-outline btn-sm">
                <i class="fas fa-external-link-alt"></i> Voir le site
            </a>
            <?php endif; ?>
            <form method="POST" action="<?= APP_URL ?>?page=update-options" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="site_online" value="<?= $isOnline ? '0' : '1' ?>">
                <input type="hidden" name="section" value="general">
                <button type="submit" class="btn <?= $isOnline ? 'btn-danger' : 'btn-success' ?> btn-sm">
                    <i class="fas fa-<?= $isOnline ? 'eye-slash' : 'eye' ?>"></i>
                    <?= $isOnline ? 'Mettre hors ligne' : 'Mettre en ligne' ?>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Stats rapides -->
<div class="grid grid-3" style="margin-bottom:var(--spacing-lg);">
    <div class="stat-card">
        <div class="stat-value">
            <span class="badge <?= $subStatus === 'active' ? 'badge-success' : 'badge-warning' ?>">
                <?= $subStatus === 'active' ? 'Actif' : ucfirst($subStatus) ?>
            </span>
        </div>
        <div class="stat-label">Abonnement</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $pendingReservations ?></div>
        <div class="stat-label">Réservations en attente</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $isOnline ? '🟢' : '🔴' ?></div>
        <div class="stat-label">Statut du site</div>
    </div>
</div>

<!-- Accès rapides -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-bolt"></i> Accès rapides</h2>
    </div>
    <div class="quick-actions">
        <a href="<?= APP_URL ?>?page=edit-card" class="quick-action">
            <i class="fas fa-utensils"></i>
            <span>Éditer la carte</span>
        </a>
        <a href="<?= APP_URL ?>?page=edit-contact" class="quick-action">
            <i class="fas fa-address-book"></i>
            <span>Contact</span>
        </a>
        <a href="<?= APP_URL ?>?page=edit-logo-banner" class="quick-action">
            <i class="fas fa-image"></i>
            <span>Logo & Bannière</span>
        </a>
        <a href="<?= APP_URL ?>?page=edit-services" class="quick-action">
            <i class="fas fa-concierge-bell"></i>
            <span>Services</span>
        </a>
        <a href="<?= APP_URL ?>?page=edit-template" class="quick-action">
            <i class="fas fa-palette"></i>
            <span>Template</span>
        </a>
        <a href="<?= APP_URL ?>?page=settings" class="quick-action">
            <i class="fas fa-cog"></i>
            <span>Paramètres</span>
        </a>
    </div>
</div>

<?php if ($isSuperAdmin): ?>
<!-- Section SUPER_ADMIN : Démos -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-flask"></i> Liens de démonstration</h2>
        <form method="POST" action="<?= APP_URL ?>?page=dashboard">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="generate_demo" value="1">
        </form>
    </div>

    <?php if (!empty($demoTokens)): ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Lien</th>
                    <th>Expire le</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demoTokens as $dt): ?>
                <tr>
                    <td>
                        <code style="font-size:0.75rem;background:var(--color-bg-alt);padding:4px 8px;border-radius:4px;">
                            <?= APP_URL ?>?page=demo&token=<?= htmlspecialchars(substr($dt->token, 0, 16)) ?>...
                        </code>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($dt->expires_at)) ?></td>
                    <td>
                        <?php if (strtotime($dt->expires_at) > time()): ?>
                            <span class="badge badge-success">Actif</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Expiré</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state" style="padding:32px;">
        <i class="fas fa-link"></i>
        <p>Aucun lien de démo actif</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
