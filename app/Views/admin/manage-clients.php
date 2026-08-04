<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php if (!isset($clients)) $clients = []; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-users-cog"></i> Gestion des clients</h2>
        <a href="<?= APP_URL ?>?page=send-invitation" class="btn btn-primary btn-sm">
            <i class="fas fa-envelope-open-text"></i> Inviter un client
        </a>
    </div>

    <?php if (empty($clients)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-users"></i>
            <h3>Aucun client</h3>
            <p>Envoyez une invitation pour ajouter un premier client.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Restaurant</th>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Abonnement</th>
                    <th>Inscrit le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($client->restaurant_name ?? '—') ?></strong></td>
                    <td><?= htmlspecialchars($client->username) ?></td>
                    <td style="font-size:0.8rem;"><?= htmlspecialchars($client->email) ?></td>
                    <td>
                        <?php $subStatus = $client->sub_status ?? 'inactive'; ?>
                        <span class="badge <?= $subStatus === 'active' ? 'badge-success' : 'badge-warning' ?>">
                            <?= ucfirst($subStatus) ?>
                        </span>
                        <?php if ($client->plan_type): ?>
                        <br><span style="font-size:0.7rem;color:var(--color-text-muted);"><?= htmlspecialchars($client->plan_type) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.8rem;"><?= date('d/m/Y', strtotime($client->created_at)) ?></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <?php if ($subStatus !== 'active'): ?>
                            <form method="POST" action="<?= APP_URL ?>?page=activate-subscription" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="client_id" value="<?= $client->id ?>">
                                <button type="submit" class="btn btn-success btn-sm" title="Activer"><i class="fas fa-check"></i></button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="<?= APP_URL ?>?page=deactivate-subscription" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="client_id" value="<?= $client->id ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Désactiver"><i class="fas fa-times"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
