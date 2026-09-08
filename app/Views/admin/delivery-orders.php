<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php
if (!isset($orders)) $orders = [];
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($newCount)) $newCount = 0;
if (!isset($todayCount)) $todayCount = 0;
if (!isset($todayRevenue)) $todayRevenue = 0;
if (!isset($filterStatus)) $filterStatus = '';
if (!isset($deliveryModel)) $deliveryModel = null;

$statusMap = [
    'new'        => ['badge' => 'badge-warning', 'label' => 'Nouvelle',      'icon' => 'fa-bell'],
    'preparing'  => ['badge' => 'badge-primary', 'label' => 'En préparation','icon' => 'fa-fire'],
    'delivering' => ['badge' => 'badge-primary', 'label' => 'En livraison',  'icon' => 'fa-motorcycle'],
    'delivered'  => ['badge' => 'badge-success', 'label' => 'Livrée',        'icon' => 'fa-check-double'],
    'cancelled'  => ['badge' => 'badge-danger',  'label' => 'Annulée',       'icon' => 'fa-ban'],
];
?>

<style>
.delivery-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}
.delivery-stat {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    text-align: center;
    transition: box-shadow 0.2s, transform 0.2s;
}
.delivery-stat:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.delivery-stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
}
.delivery-stat-label {
    font-size: 0.82rem;
    color: var(--color-text-muted);
    margin-top: 4px;
}
.delivery-stat--new .delivery-stat-value { color: #d97706; }
.delivery-stat--today .delivery-stat-value { color: #2563eb; }
.delivery-stat--revenue .delivery-stat-value { color: #16a34a; }

.delivery-order-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-md);
    transition: box-shadow 0.2s;
}
.delivery-order-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.delivery-order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: var(--spacing-sm);
}
.delivery-order-client {
    font-weight: 600;
    font-size: 0.95rem;
}
.delivery-order-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 0.82rem;
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-sm);
}
.delivery-order-items {
    background: var(--color-bg-alt);
    border-radius: var(--radius-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: 0.82rem;
    margin-bottom: var(--spacing-sm);
}
.delivery-order-items ul {
    margin: 0;
    padding-left: 16px;
}
.delivery-order-items li {
    padding: 2px 0;
}
.delivery-order-total {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--color-primary);
}
.delivery-order-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
    margin-top: var(--spacing-sm);
}
.delivery-order-actions .btn {
    white-space: nowrap;
    min-width: 0;
    flex-shrink: 0;
}
.delivery-order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
@media (max-width: 600px) {
    .delivery-stats { grid-template-columns: 1fr; }
    .delivery-order-header { flex-direction: column; }
    .delivery-order-footer { flex-direction: column; align-items: flex-start; }
    .delivery-order-actions { width: 100%; }
    .delivery-order-actions .btn { flex: 1; min-width: 0; justify-content: center; text-align: center; }
}
</style>

<!-- Statut & liens rapides -->
<div class="card" style="padding:var(--spacing-md);margin-bottom:var(--spacing-lg);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <?php if ($deliveryEnabled ?? false): ?>
        <span class="badge badge-success" style="font-size:0.82rem;"><i class="fas fa-check-circle" style="margin-right:4px;"></i> Livraison activée</span>
        <?php else: ?>
        <span class="badge badge-danger" style="font-size:0.82rem;"><i class="fas fa-times-circle" style="margin-right:4px;"></i> Livraison désactivée</span>
        <?php endif; ?>
        <a href="<?= APP_URL ?>?page=settings&section=delivery" class="btn btn-secondary btn-sm"><i class="fas fa-cog"></i> Paramètres</a>
    </div>
    <a href="<?= APP_URL ?>?page=settings&section=delivery#platforms" class="btn btn-secondary btn-sm"><i class="fas fa-link"></i> Plateformes externes</a>
</div>

<!-- Stats -->
<div class="delivery-stats">
    <div class="delivery-stat delivery-stat--new">
        <div class="delivery-stat-value"><?= $newCount ?></div>
        <div class="delivery-stat-label"><i class="fas fa-bell"></i> Nouvelles</div>
    </div>
    <div class="delivery-stat delivery-stat--today">
        <div class="delivery-stat-value"><?= $todayCount ?></div>
        <div class="delivery-stat-label"><i class="fas fa-calendar-day"></i> Aujourd'hui</div>
    </div>
    <div class="delivery-stat delivery-stat--revenue">
        <div class="delivery-stat-value"><?= number_format($todayRevenue, 2, ',', '') ?> €</div>
        <div class="delivery-stat-label"><i class="fas fa-euro-sign"></i> CA du jour</div>
    </div>
</div>

<!-- Filtres -->
<div class="card" style="padding:var(--spacing-md);margin-bottom:var(--spacing-lg);">
    <form method="GET" action="<?= APP_URL ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="page" value="delivery-orders">
        <select name="status" class="form-control" style="max-width:200px;">
            <option value="">Tous les statuts</option>
            <?php foreach ($statusMap as $val => $sm): ?>
            <option value="<?= $val ?>" <?= $filterStatus === $val ? 'selected' : '' ?>><?= $sm['label'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="<?= APP_URL ?>?page=delivery-orders" class="btn btn-secondary btn-sm"><i class="fas fa-undo"></i></a>
    </form>
</div>

<!-- Liste -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-motorcycle"></i> Commandes livraison</h2>
        <span class="badge badge-primary"><?= count($orders) ?></span>
    </div>

    <?php if (empty($orders)): ?>
    <div class="empty-state" style="padding:40px;">
        <i class="fas fa-motorcycle"></i>
        <h3>Aucune commande</h3>
        <p>Les commandes de livraison apparaîtront ici.</p>
    </div>
    <?php else: ?>

    <?php foreach ($orders as $order):
        $st = $statusMap[$order->status] ?? ['badge' => 'badge-warning', 'label' => $order->status, 'icon' => 'fa-question'];
        $items = $deliveryModel ? $deliveryModel->getItems($order->id) : [];
        $grandTotal = $order->total_amount + $order->delivery_fee;
    ?>
    <div class="delivery-order-card">
        <div class="delivery-order-header">
            <div>
                <div class="delivery-order-client">
                    #<?= $order->id ?> — <?= htmlspecialchars($order->customer_name) ?>
                </div>
                <div class="delivery-order-meta">
                    <span><a href="tel:<?= htmlspecialchars($order->customer_phone) ?>" style="color:inherit;text-decoration:none;"><i class="fas fa-phone"></i> <?= htmlspecialchars($order->customer_phone) ?></a></span>
                    <?php if ($order->customer_email): ?>
                    <span><a href="mailto:<?= htmlspecialchars($order->customer_email) ?>" style="color:inherit;text-decoration:none;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($order->customer_email) ?></a></span>
                    <?php endif; ?>
                    <span><i class="fas fa-clock"></i> <?= date('d/m H:i', strtotime($order->created_at)) ?></span>
                    <?php if (!empty($order->delivery_time_slot)): ?>
                    <span><i class="fas fa-calendar-check"></i> Créneau : <?= htmlspecialchars($order->delivery_time_slot) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="badge <?= $st['badge'] ?>"><i class="fas <?= $st['icon'] ?>" style="margin-right:4px;"></i> <?= $st['label'] ?></span>
        </div>

        <div class="delivery-order-meta">
            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($order->delivery_address) ?></span>
        </div>

        <?php if ($order->delivery_notes): ?>
        <div style="font-size:0.8rem;color:var(--color-text-muted);font-style:italic;margin-bottom:var(--spacing-sm);">
            <i class="fas fa-comment"></i> <?= htmlspecialchars($order->delivery_notes) ?>
        </div>
        <?php endif; ?>

        <?php if ($order->admin_notes): ?>
        <div style="font-size:0.8rem;color:var(--color-primary);margin-bottom:var(--spacing-sm);">
            <i class="fas fa-sticky-note"></i> <?= htmlspecialchars($order->admin_notes) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($items)): ?>
        <div class="delivery-order-items">
            <ul>
                <?php foreach ($items as $item): ?>
                <li><?= htmlspecialchars($item->dish_name) ?> x<?= $item->quantity ?> — <?= number_format($item->unit_price * $item->quantity, 2, ',', '') ?> €</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="delivery-order-footer">
            <div>
                <span style="font-size:0.82rem;color:var(--color-text-muted);">Sous-total: <?= number_format($order->total_amount, 2, ',', '') ?> € · Livraison: <?= number_format($order->delivery_fee, 2, ',', '') ?> €</span>
                <br><span class="delivery-order-total">Total: <?= number_format($grandTotal, 2, ',', '') ?> €</span>
            </div>
            <div class="delivery-order-actions">
                <?php if ($order->status === 'new'): ?>
                <form method="POST" action="<?= APP_URL ?>?page=delivery-update-status" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="order_id" value="<?= $order->id ?>">
                    <input type="hidden" name="status" value="preparing">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-fire"></i> Préparer</button>
                </form>
                <form method="POST" action="<?= APP_URL ?>?page=delivery-update-status" style="display:inline;" onsubmit="return confirm('Annuler cette commande ?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="order_id" value="<?= $order->id ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-ban"></i></button>
                </form>
                <?php elseif ($order->status === 'preparing'): ?>
                <form method="POST" action="<?= APP_URL ?>?page=delivery-update-status" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="order_id" value="<?= $order->id ?>">
                    <input type="hidden" name="status" value="delivering">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-motorcycle"></i> En livraison</button>
                </form>
                <?php elseif ($order->status === 'delivering'): ?>
                <form method="POST" action="<?= APP_URL ?>?page=delivery-update-status" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="order_id" value="<?= $order->id ?>">
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-double"></i> Livrée</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
