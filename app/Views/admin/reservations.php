<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<!-- Stats rapides -->
<div class="grid grid-3" style="margin-bottom:var(--spacing-lg);">
    <div class="stat-card">
        <div class="stat-value"><?= $pendingCount ?? 0 ?></div>
        <div class="stat-label">En attente</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $todayCount ?? 0 ?></div>
        <div class="stat-label">Aujourd'hui</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $confirmedCount ?? 0 ?></div>
        <div class="stat-label">Confirmées</div>
    </div>
</div>

<!-- Filtres -->
<div class="card" style="padding:var(--spacing-md);">
    <form method="GET" action="<?= APP_URL ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="page" value="reservations">
        <select name="status" class="form-control" style="width:auto;">
            <option value="">Tous les statuts</option>
            <?php foreach (['pending' => 'En attente', 'confirmed' => 'Confirmée', 'rejected' => 'Refusée', 'completed' => 'Terminée', 'cancelled' => 'Annulée', 'no_show' => 'No show'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($filterStatus ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date" class="form-control" style="width:auto;" value="<?= htmlspecialchars($filterDate ?? '') ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="<?= APP_URL ?>?page=reservations" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
    </form>
</div>

<!-- Liste -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-check"></i> Réservations</h2>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-calendar-check"></i>
            <h3>Aucune réservation</h3>
            <p>Les réservations de vos clients apparaîtront ici.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Date & Heure</th>
                    <th>Personnes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($res->customer_name) ?></strong>
                        <?php if ($res->customer_phone): ?>
                        <br><span style="font-size:0.78rem;color:var(--color-text-muted);"><i class="fas fa-phone"></i> <?= htmlspecialchars($res->customer_phone) ?></span>
                        <?php endif; ?>
                        <?php if ($res->customer_email): ?>
                        <br><span style="font-size:0.78rem;color:var(--color-text-muted);"><i class="fas fa-envelope"></i> <?= htmlspecialchars($res->customer_email) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= date('d/m/Y', strtotime($res->reservation_date)) ?></strong>
                        <br><span style="font-size:0.85rem;color:var(--color-text-light);"><?= date('H:i', strtotime($res->reservation_time)) ?></span>
                    </td>
                    <td><?= $res->party_size ?> <i class="fas fa-user" style="color:var(--color-text-muted);font-size:0.75rem;"></i></td>
                    <td>
                        <?php
                        $statusBadge = match($res->status) {
                            'pending' => 'badge-warning',
                            'confirmed' => 'badge-success',
                            'rejected' => 'badge-danger',
                            'completed' => 'badge-primary',
                            'cancelled' => 'badge-danger',
                            'no_show' => 'badge-danger',
                            default => 'badge-warning',
                        };
                        $statusLabel = match($res->status) {
                            'pending' => 'En attente',
                            'confirmed' => 'Confirmée',
                            'rejected' => 'Refusée',
                            'completed' => 'Terminée',
                            'cancelled' => 'Annulée',
                            'no_show' => 'No show',
                            default => $res->status,
                        };
                        ?>
                        <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
                    </td>
                    <td>
                        <?php if ($res->status === 'pending'): ?>
                        <div style="display:flex;gap:4px;">
                            <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-success btn-sm" title="Confirmer"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-danger btn-sm" title="Refuser"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                        <?php elseif ($res->status === 'confirmed'): ?>
                        <div style="display:flex;gap:4px;">
                            <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-primary btn-sm" title="Marquer terminée"><i class="fas fa-check-double"></i></button>
                            </form>
                            <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                                <input type="hidden" name="status" value="no_show">
                                <button type="submit" class="btn btn-danger btn-sm" title="No show"><i class="fas fa-user-slash"></i></button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($res->special_requests): ?>
                <tr>
                    <td colspan="5" style="padding:4px 16px 12px;font-size:0.8rem;color:var(--color-text-muted);font-style:italic;">
                        <i class="fas fa-comment"></i> <?= htmlspecialchars($res->special_requests) ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
