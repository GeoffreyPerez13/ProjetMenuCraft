<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php if (!isset($clients)) $clients = []; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-users-cog"></i> Gestion des clients</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <span class="badge badge-info"><?= count($clients) ?> client<?= count($clients) > 1 ? 's' : '' ?></span>
            <a href="<?= APP_URL ?>?page=send-invitation" class="btn btn-primary btn-sm">
                <i class="fas fa-envelope-open-text"></i> Inviter
            </a>
        </div>
    </div>

    <?php if (empty($clients)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-users"></i>
            <h3>Aucun client</h3>
            <p>Envoyez une invitation pour ajouter un premier client.</p>
        </div>
    <?php else: ?>

    <!-- Desktop table -->
    <div class="table-responsive sa-desktop-table">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Restaurant</th>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Abonnement</th>
                    <th>Statut</th>
                    <th>Inscrit le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <?php $isSuspended = !empty($client->suspended); $subStatus = $client->sub_status ?? 'inactive'; ?>
                <tr style="<?= $isSuspended ? 'opacity:0.6;' : '' ?>">
                    <td><strong><?= htmlspecialchars($client->restaurant_name ?? '—') ?></strong></td>
                    <td><?= htmlspecialchars($client->username) ?></td>
                    <td style="font-size:0.8rem;"><?= htmlspecialchars($client->email) ?></td>
                    <td>
                        <span class="badge <?= $subStatus === 'active' ? 'badge-success' : 'badge-warning' ?>">
                            <?= ucfirst($subStatus) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($isSuspended): ?>
                            <span class="badge badge-danger" title="<?= htmlspecialchars($client->suspended_reason ?? '') ?>"><i class="fas fa-ban"></i> Suspendu</span>
                        <?php else: ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Actif</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.8rem;"><?= date('d/m/Y', strtotime($client->created_at)) ?></td>
                    <td>
                        <div class="sa-actions" style="justify-content:flex-end;">
                            <form method="POST" action="<?= APP_URL ?>?page=impersonate-client">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="client_id" value="<?= $client->id ?>">
                                <button type="submit" class="btn btn-outline btn-sm" title="Se connecter en tant que"><i class="fas fa-sign-in-alt"></i></button>
                            </form>
                            <?php if ($subStatus !== 'active'): ?>
                            <form method="POST" action="<?= APP_URL ?>?page=activate-subscription">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="client_id" value="<?= $client->id ?>">
                                <button type="submit" class="btn btn-success btn-sm" title="Activer abonnement"><i class="fas fa-check"></i></button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="<?= APP_URL ?>?page=deactivate-subscription">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="client_id" value="<?= $client->id ?>">
                                <button type="submit" class="btn btn-warning btn-sm" title="Désactiver abonnement"><i class="fas fa-pause"></i></button>
                            </form>
                            <?php endif; ?>
                            <?php if ($isSuspended): ?>
                            <form method="POST" action="<?= APP_URL ?>?page=unsuspend-client">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="client_id" value="<?= $client->id ?>">
                                <button type="submit" class="btn btn-success btn-sm" title="Réactiver le compte"><i class="fas fa-unlock"></i></button>
                            </form>
                            <?php else: ?>
                            <button type="button" class="btn btn-outline btn-sm" style="border-color:var(--color-error);color:var(--color-error);" title="Suspendre" onclick="openSuspendModal(<?= $client->id ?>, '<?= htmlspecialchars(addslashes($client->username)) ?>')"><i class="fas fa-ban"></i></button>
                            <?php endif; ?>
                            <form method="POST" action="<?= APP_URL ?>?page=delete-client" onsubmit="return confirm('Supprimer définitivement le compte de <?= htmlspecialchars(addslashes($client->username)) ?> et toutes ses données ? Cette action est irréversible.')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="client_id" value="<?= $client->id ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile cards -->
    <div class="sa-client-cards">
        <?php foreach ($clients as $client): ?>
        <?php $isSuspended = !empty($client->suspended); $subStatus = $client->sub_status ?? 'inactive'; ?>
        <div class="sa-client-card <?= $isSuspended ? 'is-suspended' : '' ?>">
            <div class="sa-client-card-header">
                <h4><?= htmlspecialchars($client->restaurant_name ?? '—') ?></h4>
                <div style="display:flex;gap:4px;">
                    <?php if ($isSuspended): ?>
                        <span class="badge badge-danger"><i class="fas fa-ban"></i> Suspendu</span>
                    <?php else: ?>
                        <span class="badge badge-success"><i class="fas fa-check"></i> Actif</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="sa-client-card-meta">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($client->username) ?></span>
                <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($client->email) ?></span>
                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($client->created_at)) ?></span>
                <span>
                    <span class="badge <?= $subStatus === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($subStatus) ?></span>
                </span>
            </div>
            <div class="sa-client-card-actions">
                <form method="POST" action="<?= APP_URL ?>?page=impersonate-client">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="client_id" value="<?= $client->id ?>">
                    <button type="submit" class="btn btn-outline btn-sm" title="Se connecter en tant que"><i class="fas fa-sign-in-alt"></i> Connexion</button>
                </form>
                <?php if ($subStatus !== 'active'): ?>
                <form method="POST" action="<?= APP_URL ?>?page=activate-subscription">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="client_id" value="<?= $client->id ?>">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Activer</button>
                </form>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>?page=deactivate-subscription">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="client_id" value="<?= $client->id ?>">
                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-pause"></i> Désactiver</button>
                </form>
                <?php endif; ?>
                <?php if ($isSuspended): ?>
                <form method="POST" action="<?= APP_URL ?>?page=unsuspend-client">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="client_id" value="<?= $client->id ?>">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-unlock"></i> Réactiver</button>
                </form>
                <?php else: ?>
                <button type="button" class="btn btn-outline btn-sm" style="border-color:var(--color-error);color:var(--color-error);" onclick="openSuspendModal(<?= $client->id ?>, '<?= htmlspecialchars(addslashes($client->username)) ?>')"><i class="fas fa-ban"></i> Suspendre</button>
                <?php endif; ?>
                <form method="POST" action="<?= APP_URL ?>?page=delete-client" onsubmit="return confirm('Supprimer définitivement le compte de <?= htmlspecialchars(addslashes($client->username)) ?> ?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="client_id" value="<?= $client->id ?>">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<!-- Modal suspension -->
<div id="suspendModal" class="sa-modal-overlay">
    <div class="sa-modal">
        <h3><i class="fas fa-ban" style="color:var(--color-error);"></i> Suspendre le compte</h3>
        <p class="sa-modal-desc">Compte : <strong id="suspendUsername"></strong></p>
        <form method="POST" action="<?= APP_URL ?>?page=suspend-client">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="client_id" id="suspendClientId">
            <textarea name="reason" rows="3" class="form-input" placeholder="Raison de la suspension (optionnel)..." style="resize:vertical;"></textarea>
            <div class="sa-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeSuspendModal()">Annuler</button>
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Suspendre</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSuspendModal(clientId, username) {
    document.getElementById('suspendClientId').value = clientId;
    document.getElementById('suspendUsername').textContent = username;
    document.getElementById('suspendModal').style.display = 'flex';
}
function closeSuspendModal() {
    document.getElementById('suspendModal').style.display = 'none';
}
document.getElementById('suspendModal').addEventListener('click', function(e) {
    if (e.target === this) closeSuspendModal();
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
