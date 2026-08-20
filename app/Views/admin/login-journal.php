<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php if (!isset($attempts)) $attempts = []; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-shield-alt"></i> Journal des connexions</h2>
        <span class="badge badge-info"><?= $total ?? 0 ?> entrée<?= ($total ?? 0) > 1 ? 's' : '' ?></span>
    </div>

    <!-- Filtres -->
    <form method="GET" action="<?= APP_URL ?>" class="sa-filter-bar">
        <input type="hidden" name="page" value="login-journal">
        <div class="filter-field">
            <i class="fas fa-network-wired filter-icon"></i>
            <input type="text" name="filter_ip" value="<?= htmlspecialchars($filterIp ?? '') ?>" placeholder="Adresse IP...">
        </div>
        <div class="filter-field">
            <i class="fas fa-user filter-icon"></i>
            <input type="text" name="filter_user" value="<?= htmlspecialchars($filterUser ?? '') ?>" placeholder="Nom d'utilisateur...">
        </div>
        <div class="filter-field">
            <i class="fas fa-filter filter-icon"></i>
            <select name="filter_status">
                <option value="">Tous les statuts</option>
                <option value="success" <?= ($filterStatus ?? '') === 'success' ? 'selected' : '' ?>>Réussies</option>
                <option value="failed" <?= ($filterStatus ?? '') === 'failed' ? 'selected' : '' ?>>Échouées</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrer</button>
            <a href="<?= APP_URL ?>?page=login-journal" class="btn btn-secondary btn-sm" title="Réinitialiser"><i class="fas fa-undo"></i></a>
        </div>
    </form>

    <?php if (empty($attempts)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-check-circle"></i>
            <h3>Aucune tentative</h3>
            <p>Aucune entrée ne correspond aux filtres.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>IP</th>
                    <th>Utilisateur</th>
                    <th>Restaurant</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attempts as $a): ?>
                <tr>
                    <td style="font-size:0.8rem;white-space:nowrap;"><?= date('d/m/Y H:i:s', strtotime($a->attempted_at)) ?></td>
                    <td><code style="font-size:0.75rem;background:var(--color-bg-alt);padding:3px 8px;border-radius:4px;border:1px solid var(--color-border-light);"><?= htmlspecialchars($a->ip_address) ?></code></td>
                    <td><?= htmlspecialchars($a->username ?? '—') ?></td>
                    <td style="font-size:0.8rem;color:var(--color-text-muted);"><?= htmlspecialchars($a->restaurant_name ?? '—') ?></td>
                    <td>
                        <?php if ($a->success): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Réussie</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-times"></i> Échouée</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <div style="display:flex;justify-content:center;gap:4px;margin-top:20px;flex-wrap:wrap;">
        <?php
        $baseUrl = APP_URL . '?page=login-journal';
        if ($filterIp ?? '') $baseUrl .= '&filter_ip=' . urlencode($filterIp);
        if ($filterUser ?? '') $baseUrl .= '&filter_user=' . urlencode($filterUser);
        if ($filterStatus ?? '') $baseUrl .= '&filter_status=' . urlencode($filterStatus);
        $p = $page ?? 1;
        $tp = $totalPages ?? 1;
        ?>
        <?php if ($p > 1): ?>
            <a href="<?= $baseUrl ?>&p=<?= $p - 1 ?>" class="btn btn-outline btn-sm"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = max(1, $p - 2); $i <= min($tp, $p + 2); $i++): ?>
            <a href="<?= $baseUrl ?>&p=<?= $i ?>" class="btn <?= $i === $p ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($p < $tp): ?>
            <a href="<?= $baseUrl ?>&p=<?= $p + 1 ?>" class="btn btn-outline btn-sm"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
