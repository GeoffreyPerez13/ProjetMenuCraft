<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-lg);flex-wrap:wrap;gap:8px;">
    <a href="<?= APP_URL ?>?page=edit-card" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Retour à l'édition
    </a>
    <span class="badge <?= $carteMode === 'editable' ? 'badge-primary' : 'badge-warning' ?>">
        Mode : <?= $carteMode === 'editable' ? 'Éditable' : 'Images' ?>
    </span>
</div>

<?php if ($carteMode === 'editable'): ?>

    <!-- Menus du jour -->
    <?php if (!empty($dailyMenus)): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-day"></i> Menus du jour / Formules</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--spacing-md);">
            <?php foreach ($dailyMenus as $menu): ?>
            <div style="border:2px solid var(--color-primary);border-radius:var(--radius-md);overflow:hidden;">
                <div style="background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));padding:12px 16px;color:#fff;display:flex;justify-content:space-between;align-items:center;">
                    <strong><?= htmlspecialchars($menu->title) ?></strong>
                    <?php if ($menu->price): ?>
                    <span style="font-weight:800;font-size:1.1rem;"><?= number_format($menu->price, 2, ',', ' ') ?> €</span>
                    <?php endif; ?>
                </div>
                <div style="padding:16px;">
                    <?php if ($menu->description): ?>
                    <p style="font-size:0.85rem;color:var(--color-text-muted);margin-bottom:12px;font-style:italic;"><?= htmlspecialchars($menu->description) ?></p>
                    <?php endif; ?>
                    <?php $items = json_decode($menu->items ?? '[]', true) ?: []; ?>
                    <?php foreach ($items as $item): ?>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:0.88rem;border-bottom:1px dashed var(--color-border-light);">
                        <span style="font-weight:600;color:var(--color-text-light);"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                        <span><?= htmlspecialchars($item['value'] ?? '') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Carte -->
    <?php if (empty($categories)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-utensils"></i>
            <h3>Aucune catégorie</h3>
            <p>Ajoutez des catégories et des plats pour voir la prévisualisation.</p>
        </div>
    </div>
    <?php else: ?>
        <?php foreach ($categories as $cat): ?>
            <?php $dishes = $dishesByCategory[$cat->id] ?? []; ?>
            <?php if (!empty($dishes)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 style="font-family:var(--font-display);"><?= htmlspecialchars($cat->name) ?></h2>
                </div>
                <div class="view-card-grid">
                    <?php foreach ($dishes as $dish): ?>
                    <div class="view-card-dish" <?= !$dish->is_active ? 'style="opacity:0.4;"' : '' ?>>
                        <?php if ($dish->image): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($dish->image) ?>" alt="" class="view-card-dish-img">
                        <?php endif; ?>
                        <div style="flex:1;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <strong style="font-size:0.9rem;"><?= htmlspecialchars($dish->name) ?></strong>
                                <span style="color:var(--color-primary);font-weight:700;white-space:nowrap;"><?= number_format($dish->price, 2, ',', ' ') ?> €</span>
                            </div>
                            <?php if ($dish->description): ?>
                            <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:4px;"><?= htmlspecialchars($dish->description) ?></p>
                            <?php endif; ?>
                            <?php if (!$dish->is_active): ?>
                            <span class="badge badge-warning" style="margin-top:4px;font-size:0.65rem;">Inactif</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

<?php else: ?>
    <!-- Mode images -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-images"></i> Carte en images</h2>
        </div>
        <?php if (!empty($cardImages)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--spacing-md);">
            <?php foreach ($cardImages as $img): ?>
            <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($img->filename) ?>" alt="Carte" style="width:100%;border-radius:var(--radius-md);border:1px solid var(--color-border);">
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-images"></i>
            <h3>Aucune image</h3>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
