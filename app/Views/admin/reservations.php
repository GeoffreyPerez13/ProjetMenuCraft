<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php
if (!isset($pendingCount)) $pendingCount = 0;
if (!isset($todayCount)) $todayCount = 0;
if (!isset($confirmedCount)) $confirmedCount = 0;
if (!isset($reservations)) $reservations = [];
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($filterStatus)) $filterStatus = '';
if (!isset($filterDate)) $filterDate = '';
if (!isset($floorTables)) $floorTables = [];
?>

<style>
/* ─── Reservations: Stats ─── */
.resa-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}
.resa-stat {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    text-align: center;
    transition: box-shadow 0.2s, transform 0.2s;
}
.resa-stat:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.resa-stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-primary);
    line-height: 1.2;
}
.resa-stat-label {
    font-size: 0.82rem;
    color: var(--color-text-muted);
    margin-top: 4px;
}
.resa-stat--pending .resa-stat-value { color: #d97706; }
.resa-stat--today .resa-stat-value { color: #2563eb; }
.resa-stat--confirmed .resa-stat-value { color: #16a34a; }

/* ─── Reservations: Filters ─── */
.resa-filters {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.resa-filters .form-control {
    min-width: 0;
    flex: 1 1 140px;
    max-width: 200px;
}
.resa-filters .resa-filter-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

/* ─── Reservations: Desktop table ─── */
.resa-table-wrap {
    display: block;
}
.resa-cards-wrap {
    display: none;
}

/* ─── Reservations: Mobile cards ─── */
.resa-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-sm);
    transition: box-shadow 0.2s;
}
.resa-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.resa-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
}
.resa-card-client { font-weight: 600; font-size: 0.95rem; }
.resa-card-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 16px;
    font-size: 0.84rem;
    color: var(--color-text-light);
    margin-bottom: 10px;
}
.resa-card-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.resa-card-meta-item i {
    width: 14px;
    text-align: center;
    color: var(--color-text-muted);
    font-size: 0.75rem;
}
.resa-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding-top: 10px;
    border-top: 1px solid var(--color-border-light);
}
.resa-card-actions {
    display: flex;
    gap: 6px;
}
.resa-card-note {
    margin-top: 10px;
    padding: 8px 12px;
    background: var(--color-bg-alt);
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    color: var(--color-text-muted);
    font-style: italic;
}

/* ─── Responsive ─── */
@media (max-width: 992px) {
    .resa-stats { gap: var(--spacing-sm); }
    .resa-stat { padding: var(--spacing-md); }
    .resa-stat-value { font-size: 1.6rem; }
}

@media (max-width: 768px) {
    .resa-table-wrap { display: none; }
    .resa-cards-wrap { display: block; }

    .resa-stats {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .resa-stat {
        padding: 12px 8px;
    }
    .resa-stat-value { font-size: 1.4rem; }
    .resa-stat-label { font-size: 0.75rem; }

    .resa-filters {
        flex-direction: column;
        align-items: stretch;
    }
    .resa-filters .form-control {
        max-width: 100%;
        flex: 1 1 auto;
    }
    .resa-filters .resa-filter-actions {
        justify-content: stretch;
    }
    .resa-filters .resa-filter-actions .btn {
        flex: 1;
    }
}

@media (max-width: 480px) {
    .resa-stats {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .resa-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: left;
        padding: 12px 16px;
    }
    .resa-stat-value { font-size: 1.5rem; margin: 0; }
    .resa-stat-label { margin: 0; font-size: 0.82rem; }

    .resa-card-meta {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    .resa-card-footer {
        flex-direction: column;
        align-items: stretch;
    }
    .resa-card-actions {
        justify-content: stretch;
    }
    .resa-card-actions form,
    .resa-card-actions .btn {
        flex: 1;
    }
    .resa-card-actions .btn {
        justify-content: center;
    }
}
</style>

<!-- Stats rapides -->
<div class="resa-stats">
    <div class="resa-stat resa-stat--pending">
        <div class="resa-stat-value"><?= $pendingCount ?></div>
        <div class="resa-stat-label"><i class="fas fa-clock"></i> En attente</div>
    </div>
    <div class="resa-stat resa-stat--today">
        <div class="resa-stat-value"><?= $todayCount ?></div>
        <div class="resa-stat-label"><i class="fas fa-calendar-day"></i> Aujourd'hui</div>
    </div>
    <div class="resa-stat resa-stat--confirmed">
        <div class="resa-stat-value"><?= $confirmedCount ?></div>
        <div class="resa-stat-label"><i class="fas fa-check-circle"></i> Confirmées</div>
    </div>
</div>

<!-- Filtres -->
<div class="card" style="padding:var(--spacing-md);margin-bottom:var(--spacing-lg);">
    <form method="GET" action="<?= APP_URL ?>" class="resa-filters">
        <input type="hidden" name="page" value="reservations">
        <select name="status" class="form-control">
            <option value="">Tous les statuts</option>
            <?php foreach (['pending' => 'En attente', 'confirmed' => 'Confirmée', 'rejected' => 'Refusée', 'completed' => 'Terminée', 'cancelled' => 'Annulée', 'no_show' => 'No show'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= $filterStatus === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
        <div class="resa-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
            <a href="<?= APP_URL ?>?page=reservations" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
        </div>
    </form>
</div>

<!-- Liste des réservations -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-check"></i> Réservations</h2>
        <span class="badge badge-primary"><?= count($reservations) ?></span>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-calendar-check"></i>
            <h3>Aucune réservation</h3>
            <p>Les réservations de vos clients apparaîtront ici.</p>
        </div>
    <?php else: ?>

    <?php
    // Pre-compute status data for each reservation
    $statusMap = [
        'pending'   => ['badge' => 'badge-warning', 'label' => 'En attente'],
        'confirmed' => ['badge' => 'badge-success', 'label' => 'Confirmée'],
        'rejected'  => ['badge' => 'badge-danger',  'label' => 'Refusée'],
        'completed' => ['badge' => 'badge-primary', 'label' => 'Terminée'],
        'cancelled' => ['badge' => 'badge-danger',  'label' => 'Annulée'],
        'no_show'   => ['badge' => 'badge-danger',  'label' => 'No show'],
    ];
    // Table lookup map (id => label)
    $tableLabels = [];
    foreach ($floorTables as $ft) {
        $label = $ft->table_number;
        if ($ft->name) $label .= ' - ' . $ft->name;
        $tableLabels[$ft->id] = $label;
    }
    ?>

    <!-- Desktop: Table -->
    <div class="resa-table-wrap">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Date & Heure</th>
                        <th>Pers.</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res):
                        $st = $statusMap[$res->status] ?? ['badge' => 'badge-warning', 'label' => $res->status];
                    ?>
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
                        <td><span class="badge <?= $st['badge'] ?>"><?= $st['label'] ?></span></td>
                        <td>
                            <?php if ($res->status === 'pending'): ?>
                            <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                                <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="display:inline-flex;gap:4px;align-items:center;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <?php if (!empty($floorTables)): ?>
                                    <select name="table_id" style="padding:4px 8px;font-size:0.78rem;border:1px solid var(--color-border);border-radius:var(--radius-sm);max-width:120px;">
                                        <option value="">— Table —</option>
                                        <?php foreach ($floorTables as $ft): ?>
                                        <option value="<?= $ft->id ?>"><?= htmlspecialchars($ft->table_number . ($ft->name ? ' - ' . $ft->name : '')) ?> (<?= $ft->seats ?>p)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
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
                            <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                                <?php if (!empty($res->table_id) && isset($tableLabels[$res->table_id])): ?>
                                <span style="font-size:0.75rem;background:var(--color-primary-bg);color:var(--color-primary);padding:3px 8px;border-radius:var(--radius-full);font-weight:600;">
                                    <i class="fas fa-chair"></i> <?= htmlspecialchars($tableLabels[$res->table_id]) ?>
                                </span>
                                <?php endif; ?>
                                <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Terminée"><i class="fas fa-check-double"></i></button>
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
    </div>

    <!-- Mobile: Cards -->
    <div class="resa-cards-wrap">
        <?php foreach ($reservations as $res):
            $st = $statusMap[$res->status] ?? ['badge' => 'badge-warning', 'label' => $res->status];
        ?>
        <div class="resa-card">
            <div class="resa-card-top">
                <div class="resa-card-client"><?= htmlspecialchars($res->customer_name) ?></div>
                <span class="badge <?= $st['badge'] ?>"><?= $st['label'] ?></span>
            </div>
            <div class="resa-card-meta">
                <div class="resa-card-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?= date('d/m/Y', strtotime($res->reservation_date)) ?></span>
                </div>
                <div class="resa-card-meta-item">
                    <i class="fas fa-clock"></i>
                    <span><?= date('H:i', strtotime($res->reservation_time)) ?></span>
                </div>
                <div class="resa-card-meta-item">
                    <i class="fas fa-users"></i>
                    <span><?= $res->party_size ?> personne<?= $res->party_size > 1 ? 's' : '' ?></span>
                </div>
                <?php if ($res->customer_phone): ?>
                <div class="resa-card-meta-item">
                    <i class="fas fa-phone"></i>
                    <span><?= htmlspecialchars($res->customer_phone) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($res->customer_email): ?>
                <div class="resa-card-meta-item">
                    <i class="fas fa-envelope"></i>
                    <span><?= htmlspecialchars($res->customer_email) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($res->special_requests): ?>
            <div class="resa-card-note">
                <i class="fas fa-comment"></i> <?= htmlspecialchars($res->special_requests) ?>
            </div>
            <?php endif; ?>

            <?php if ($res->status === 'pending' || $res->status === 'confirmed'): ?>
            <div class="resa-card-footer">
                <div class="resa-card-actions" style="flex-direction:column;width:100%;gap:8px;">
                    <?php if ($res->status === 'pending'): ?>
                    <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;width:100%;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                        <input type="hidden" name="status" value="confirmed">
                        <?php if (!empty($floorTables)): ?>
                        <select name="table_id" class="form-control" style="flex:1;min-width:0;font-size:0.8rem;padding:6px 8px;">
                            <option value="">— Assigner une table —</option>
                            <?php foreach ($floorTables as $ft): ?>
                            <option value="<?= $ft->id ?>"><?= htmlspecialchars($ft->table_number . ($ft->name ? ' - ' . $ft->name : '')) ?> (<?= $ft->seats ?>p)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Confirmer</button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger btn-sm" style="width:100%;"><i class="fas fa-times"></i> Refuser</button>
                    </form>
                    <?php elseif ($res->status === 'confirmed'): ?>
                    <?php if (!empty($res->table_id) && isset($tableLabels[$res->table_id])): ?>
                    <span style="font-size:0.8rem;background:var(--color-primary-bg);color:var(--color-primary);padding:5px 10px;border-radius:var(--radius-full);font-weight:600;align-self:flex-start;">
                        <i class="fas fa-chair"></i> <?= htmlspecialchars($tableLabels[$res->table_id]) ?>
                    </span>
                    <?php endif; ?>
                    <div style="display:flex;gap:6px;width:100%;">
                        <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="flex:1;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%;"><i class="fas fa-check-double"></i> Terminée</button>
                        </form>
                        <form method="POST" action="<?= APP_URL ?>?page=reservation-update-status" style="flex:1;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                            <input type="hidden" name="status" value="no_show">
                            <button type="submit" class="btn btn-danger btn-sm" style="width:100%;"><i class="fas fa-user-slash"></i> No show</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
