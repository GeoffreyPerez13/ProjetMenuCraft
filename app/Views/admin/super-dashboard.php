<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<!-- Stats globales -->
<div class="grid grid-3" style="margin-bottom:var(--spacing-lg);">
    <div class="stat-card">
        <div class="stat-value"><?= $totalClients ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-users"></i> Clients inscrits</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $activeClients ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-check-circle" style="color:var(--color-success);"></i> Abonnements actifs</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $onlineSites ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-globe" style="color:var(--color-primary);"></i> Sites en ligne</div>
    </div>
</div>

<div class="grid grid-3" style="margin-bottom:var(--spacing-lg);">
    <div class="stat-card">
        <div class="stat-value"><?= $newClientsThisWeek ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-user-plus" style="color:var(--color-info);"></i> Nouveaux (7 jours)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalReservationsToday ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-calendar-day" style="color:var(--color-warning);"></i> Réservations aujourd'hui</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="<?= ($recentFailures ?? 0) > 10 ? 'color:var(--color-error);' : '' ?>"><?= $recentFailures ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-shield-alt" style="color:var(--color-error);"></i> Échecs login (24h)</div>
    </div>
</div>

<!-- Accès rapides Super Admin -->
<div class="card" style="margin-bottom:var(--spacing-lg);">
    <div class="card-header">
        <h2><i class="fas fa-bolt"></i> Administration</h2>
    </div>
    <div class="quick-actions">
        <a href="<?= APP_URL ?>?page=manage-clients" class="quick-action">
            <i class="fas fa-users-cog"></i>
            <span>Clients</span>
        </a>
        <a href="<?= APP_URL ?>?page=login-journal" class="quick-action">
            <i class="fas fa-shield-alt"></i>
            <span>Journal connexions</span>
        </a>
        <a href="<?= APP_URL ?>?page=announcements" class="quick-action">
            <i class="fas fa-bullhorn"></i>
            <span>Annonces</span>
        </a>
        <a href="<?= APP_URL ?>?page=send-invitation" class="quick-action">
            <i class="fas fa-envelope-open-text"></i>
            <span>Invitations</span>
        </a>
        <a href="<?= APP_URL ?>?page=feedback-dashboard" class="quick-action">
            <i class="fas fa-comments"></i>
            <span>Feedbacks</span>
        </a>
    </div>
</div>

<!-- Activité récente des clients -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Activité récente des clients</h2>
    </div>

    <?php if (empty($recentClients)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-users"></i>
            <h3>Aucun client</h3>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Restaurant</th>
                    <th>Utilisateur</th>
                    <th>Abonnement</th>
                    <th>Dernière connexion</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentClients as $client): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($client->restaurant_name ?? '—') ?></strong></td>
                    <td style="font-size:0.85rem;"><?= htmlspecialchars($client->username) ?></td>
                    <td>
                        <?php $subStatus = $client->sub_status ?? 'inactive'; ?>
                        <span class="badge <?= $subStatus === 'active' ? 'badge-success' : 'badge-warning' ?>">
                            <?= ucfirst($subStatus) ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--color-text-muted);">
                        <?php if ($client->last_login_at): ?>
                            <?= date('d/m/Y H:i', strtotime($client->last_login_at)) ?>
                        <?php else: ?>
                            <span style="color:var(--color-text-light);">Jamais</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($client->suspended): ?>
                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Suspendu</span>
                        <?php else: ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Actif</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
