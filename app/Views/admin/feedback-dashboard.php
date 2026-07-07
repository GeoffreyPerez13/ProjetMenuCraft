<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-comments"></i> Tous les feedbacks</h2>
        <span class="badge badge-primary"><?= count($feedbacks ?? []) ?> retours</span>
    </div>

    <?php if (empty($feedbacks)): ?>
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-comments"></i>
            <h3>Aucun feedback</h3>
            <p>Les retours des utilisateurs apparaîtront ici.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Restaurant</th>
                    <th>Note</th>
                    <th>Facilité</th>
                    <th>Feature préférée</th>
                    <th>Améliorations</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $fb): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($fb->restaurant_name ?? '—') ?></strong>
                        <?php if ($fb->name): ?>
                        <br><span style="font-size:0.78rem;color:var(--color-text-muted);"><?= htmlspecialchars($fb->name) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($fb->rating): ?>
                        <span style="color:#f59e0b;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?= $i > $fb->rating ? '-o' : '' ?>" style="font-size:0.8rem;<?= $i > $fb->rating ? 'opacity:0.3;' : '' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="font-size:0.8rem;"><?= htmlspecialchars($fb->ease_of_use ?? '—') ?></td>
                    <td style="font-size:0.8rem;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($fb->favorite_feature ?? '') ?>">
                        <?= htmlspecialchars($fb->favorite_feature ?? '—') ?>
                    </td>
                    <td style="font-size:0.8rem;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($fb->improvements ?? '') ?>">
                        <?= htmlspecialchars($fb->improvements ?? '—') ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--color-text-muted);">
                        <?= date('d/m/Y', strtotime($fb->created_at)) ?>
                    </td>
                </tr>
                <?php if ($fb->comments): ?>
                <tr>
                    <td colspan="6" style="padding:4px 16px 12px;font-size:0.8rem;color:var(--color-text-muted);font-style:italic;">
                        <i class="fas fa-comment"></i> <?= htmlspecialchars($fb->comments) ?>
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
