    </main>
</div><!-- /.admin-layout -->

<?php if (empty($_hideTourButton)):
    $currentPageForTour = $_GET['page'] ?? 'dashboard';
    $pagesWithTour = ['dashboard', 'edit-card', 'edit-contact', 'edit-logo-banner', 'edit-services', 'edit-template', 'reservations', 'stats', 'delivery-orders', 'floor-plan'];
    $hasTour = in_array($currentPageForTour, $pagesWithTour);
?>
<?php if ($hasTour): ?>
<button class="tour-trigger-btn" id="tourTriggerBtn" onclick="startPageTour()" title="Guide interactif">
    <i class="fas fa-question"></i>
</button>
<?php else: ?>
<button class="tour-trigger-btn" id="tourTriggerBtn" disabled title="Aucun guide disponible pour cette page" style="opacity:0.35;cursor:default;pointer-events:none;">
    <i class="fas fa-question"></i>
</button>
<?php endif; ?>
<?php endif; ?>

<?php if (!isset($options) || empty($options['hide_reservation_fab']) || ($options['hide_reservation_fab'] ?? '0') !== '1'): ?>
<!-- Floating reservation panel button -->
<button class="reservation-fab" id="reservationFab" onclick="toggleReservationPanel()" title="Réservations en attente">
    <i class="fas fa-concierge-bell"></i>
    <span class="reservation-fab-badge" id="fabBadge" style="display:none;">0</span>
    <span class="reservation-fab-mute" id="muteToggle" onclick="event.stopPropagation();toggleMute();" title="Couper/activer le son">
        <i class="fas fa-volume-up" id="muteIcon"></i>
    </span>
</button>
<?php endif; ?>

<!-- Reservation quick panel -->
<div class="reservation-panel" id="reservationPanel">
    <div class="reservation-panel-header">
        <h3><i class="fas fa-concierge-bell"></i> Notifications</h3>
        <button onclick="toggleReservationPanel()" class="reservation-panel-close"><i class="fas fa-times"></i></button>
    </div>
    <div class="reservation-panel-body" id="reservationPanelBody">
        <div class="reservation-panel-loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>
    </div>
</div>
<div class="reservation-panel-overlay" id="reservationPanelOverlay" onclick="toggleReservationPanel()"></div>

<!-- Toast notifications -->
<div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:8px;pointer-events:none;"></div>

<script src="<?= APP_URL ?>/assets/js/notification-sound.js"></script>
<script>
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const icons = {success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle'};
    const colors = {success:'#28a745', error:'#dc3545', info:'#17a2b8'};
    toast.style.cssText = `pointer-events:auto;display:flex;align-items:center;gap:8px;padding:12px 18px;background:var(--color-bg, #fff);color:var(--color-text, #1f2937);border-left:4px solid ${colors[type]||colors.info};border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.15);font-size:.875rem;opacity:0;transform:translateX(30px);transition:all .3s ease;`;
    toast.innerHTML = `<i class="fas ${icons[type]||icons.info}" style="color:${colors[type]||colors.info}"></i><span>${message}</span>`;
    container.appendChild(toast);
    requestAnimationFrame(() => { toast.style.opacity = '1'; toast.style.transform = 'translateX(0)'; });
    setTimeout(() => {
        toast.style.opacity = '0'; toast.style.transform = 'translateX(30px)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}
function closeSidebar() {
    document.getElementById('adminSidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
}
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark-mode');
    const isDark = document.documentElement.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    const icon = document.getElementById('darkModeIcon');
    if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}
if (document.documentElement.classList.contains('dark-mode')) {
    const icon = document.getElementById('darkModeIcon');
    if (icon) icon.className = 'fas fa-sun';
}

// Mute toggle
let notifMuted = localStorage.getItem('reservationMuted') === '1';
function toggleMute() {
    notifMuted = !notifMuted;
    localStorage.setItem('reservationMuted', notifMuted ? '1' : '0');
    const icon = document.getElementById('muteIcon');
    const btn = document.getElementById('muteToggle');
    if (icon) icon.className = notifMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
    if (btn) btn.classList.toggle('muted', notifMuted);
}
(function() {
    const icon = document.getElementById('muteIcon');
    const btn = document.getElementById('muteToggle');
    if (icon && notifMuted) { icon.className = 'fas fa-volume-mute'; btn.classList.add('muted'); }
})();

// Reservation Panel
let reservationPanelOpen = false;
let notifActionTaken = false;
function toggleReservationPanel() {
    reservationPanelOpen = !reservationPanelOpen;
    document.getElementById('reservationPanel').classList.toggle('open', reservationPanelOpen);
    document.getElementById('reservationPanelOverlay').classList.toggle('active', reservationPanelOpen);
    if (reservationPanelOpen) {
        loadPendingReservations();
    } else if (notifActionTaken) {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page');
        if (page === 'delivery-orders' || page === 'reservations') {
            window.location.reload();
        }
        notifActionTaken = false;
    }
}

function loadPendingReservations() {
    const body = document.getElementById('reservationPanelBody');
    body.innerHTML = '<div class="reservation-panel-loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    fetch('<?= APP_URL ?>?page=reservation-pending-list', {credentials: 'same-origin'})
        .then(r => r.json())
        .then(data => {
            const hasRes = data.reservations && data.reservations.length > 0;
            const hasDel = data.delivery_orders && data.delivery_orders.length > 0;
            if (!hasRes && !hasDel) {
                body.innerHTML = '<div class="reservation-panel-empty"><i class="fas fa-check-circle"></i><p>Aucune notification en attente</p></div>';
                return;
            }
            let html = '';

            // Delivery orders section
            if (hasDel) {
                html += '<div style="padding:8px 12px;font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--color-text-muted);background:var(--color-bg-alt);border-bottom:1px solid var(--color-border);"><i class="fas fa-motorcycle"></i> Commandes livraison</div>';
                data.delivery_orders.forEach(order => {
                    const total = (parseFloat(order.total_amount) + parseFloat(order.delivery_fee)).toFixed(2).replace('.', ',');
                    html += `<div class="reservation-panel-card" id="delCard${order.id}">
                        <div class="rp-card-header">
                            <strong><i class="fas fa-motorcycle"></i> #${order.id} — ${escHtml(order.customer_name)}</strong>
                            <span class="rp-card-size" style="color:var(--color-primary);font-weight:700;">${total} €</span>
                        </div>
                        <div class="rp-card-details">
                            <span><i class="fas fa-map-marker-alt"></i> ${escHtml(order.delivery_address)}</span>
                            ${order.delivery_time_slot ? '<span><i class="fas fa-clock"></i> ' + escHtml(order.delivery_time_slot) + '</span>' : ''}
                        </div>`;
                    if (order.customer_phone) html += `<div class="rp-card-contact"><a href="tel:${escHtml(order.customer_phone)}" style="color:inherit;text-decoration:none;"><i class="fas fa-phone"></i> ${escHtml(order.customer_phone)}</a></div>`;
                    html += `<div class="rp-card-actions">
                        <button class="rp-btn rp-btn-confirm" onclick="handleDeliveryOrder(${order.id},'preparing')"><i class="fas fa-fire"></i> Préparer</button>
                        <button class="rp-btn rp-btn-reject" onclick="handleDeliveryOrder(${order.id},'cancelled')"><i class="fas fa-ban"></i> Annuler</button>
                    </div></div>`;
                });
            }

            // Reservations section
            if (hasRes) {
                html += '<div style="padding:8px 12px;font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--color-text-muted);background:var(--color-bg-alt);border-bottom:1px solid var(--color-border);"><i class="fas fa-calendar-check"></i> Réservations</div>';
                data.reservations.forEach(res => {
                    const date = new Date(res.reservation_date).toLocaleDateString('fr-FR', {day:'numeric',month:'short'});
                    html += `<div class="reservation-panel-card" id="rpCard${res.id}">
                        <div class="rp-card-header">
                            <strong><i class="fas fa-user"></i> ${escHtml(res.customer_name)}</strong>
                            <span class="rp-card-size"><i class="fas fa-users"></i> ${res.party_size}</span>
                        </div>
                        <div class="rp-card-details">
                            <span><i class="fas fa-calendar"></i> ${date}</span>
                            <span><i class="fas fa-clock"></i> ${res.reservation_time}</span>
                        </div>`;
                    if (res.customer_phone) html += `<div class="rp-card-contact"><i class="fas fa-phone"></i> ${escHtml(res.customer_phone)}</div>`;
                    if (res.customer_email) html += `<div class="rp-card-contact"><i class="fas fa-envelope"></i> ${escHtml(res.customer_email)}</div>`;
                    if (res.special_requests) html += `<div class="rp-card-note"><i class="fas fa-comment"></i> ${escHtml(res.special_requests)}</div>`;
                    
                    if (data.tables && data.tables.length > 0) {
                        html += `<div class="rp-card-table"><select id="rpTable${res.id}" class="rp-table-select">
                            <option value="">— Table (optionnel) —</option>`;
                        data.tables.forEach(t => {
                            const label = (t.name ? t.name : 'Table ' + t.table_number) + ' (' + t.seats + ' pl.) - ' + t.floor_name;
                            html += `<option value="${t.id}">${escHtml(label)}</option>`;
                        });
                        html += `</select></div>`;
                    }

                    html += `<div class="rp-card-actions">
                        <button class="rp-btn rp-btn-confirm" onclick="handleReservation(${res.id},'confirmed')"><i class="fas fa-check"></i> Confirmer</button>
                        <button class="rp-btn rp-btn-reject" onclick="handleReservation(${res.id},'rejected')"><i class="fas fa-times"></i> Refuser</button>
                    </div></div>`;
                });
            }
            body.innerHTML = html;
        })
        .catch(() => { body.innerHTML = '<div class="reservation-panel-empty"><i class="fas fa-exclamation-circle"></i><p>Erreur de chargement</p></div>'; });
}

function handleReservation(id, status) {
    const card = document.getElementById('rpCard' + id);
    const tableSelect = document.getElementById('rpTable' + id);
    const tableId = tableSelect ? tableSelect.value : '';
    
    const formData = new FormData();
    formData.append('csrf_token', '<?= htmlspecialchars($csrf_token ?? '') ?>');
    formData.append('reservation_id', id);
    formData.append('status', status);
    if (tableId) formData.append('table_id', tableId);

    card.style.opacity = '0.5';
    card.style.pointerEvents = 'none';

    fetch('<?= APP_URL ?>?page=reservation-update-status', {method: 'POST', credentials: 'same-origin', body: formData})
        .then(r => {
            notifActionTaken = true;
            card.style.transition = 'all 0.3s ease';
            card.style.transform = 'translateX(100%)';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                const remaining = document.querySelectorAll('.reservation-panel-card');
                if (remaining.length === 0) {
                    document.getElementById('reservationPanelBody').innerHTML = '<div class="reservation-panel-empty"><i class="fas fa-check-circle"></i><p>Aucune réservation en attente</p></div>';
                }
                updateFabBadge();
            }, 300);
        })
        .catch(() => { card.style.opacity = '1'; card.style.pointerEvents = ''; });
}

function handleDeliveryOrder(id, status) {
    const card = document.getElementById('delCard' + id);
    const formData = new FormData();
    formData.append('csrf_token', '<?= htmlspecialchars($csrf_token ?? '') ?>');
    formData.append('order_id', id);
    formData.append('status', status);

    card.style.opacity = '0.5';
    card.style.pointerEvents = 'none';

    fetch('<?= APP_URL ?>?page=delivery-update-status', {method: 'POST', credentials: 'same-origin', body: formData})
        .then(r => {
            notifActionTaken = true;
            card.style.transition = 'all 0.3s ease';
            card.style.transform = 'translateX(100%)';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                const remaining = document.querySelectorAll('.reservation-panel-card');
                if (remaining.length === 0) {
                    document.getElementById('reservationPanelBody').innerHTML = '<div class="reservation-panel-empty"><i class="fas fa-check-circle"></i><p>Aucune notification en attente</p></div>';
                }
                updateFabBadge();
            }, 300);
        })
        .catch(() => { card.style.opacity = '1'; card.style.pointerEvents = ''; });
}

function updateFabBadge() {
    fetch('<?= APP_URL ?>?page=reservation-pending-count', {credentials: 'same-origin'})
        .then(r => r.json())
        .then(data => {
            const count = (data.count || 0) + (data.delivery_count || 0);
            const fabBadge = document.getElementById('fabBadge');
            const sidebarBadge = document.getElementById('pendingBadge');
            fabBadge.textContent = count;
            fabBadge.style.display = count > 0 ? '' : 'none';
            if (sidebarBadge) {
                sidebarBadge.textContent = data.count || 0;
                sidebarBadge.style.display = (data.count || 0) > 0 ? '' : 'none';
            }
            // Delivery sidebar badge
            const delBadge = document.getElementById('deliveryBadge');
            if (delBadge) {
                delBadge.textContent = data.delivery_count || 0;
                delBadge.style.display = (data.delivery_count || 0) > 0 ? '' : 'none';
            }
        }).catch(() => {});
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// Real-time notifications polling with sound (reservations + delivery)
(function() {
    const badge = document.getElementById('pendingBadge');
    const fabBadge = document.getElementById('fabBadge');
    const delBadge = document.getElementById('deliveryBadge');
    if (!badge) return;
    let lastResCount = parseInt(badge.textContent) || 0;
    let lastDelCount = 0;

    function checkPending() {
        fetch('<?= APP_URL ?>?page=reservation-pending-count', {credentials: 'same-origin'})
            .then(r => r.json())
            .then(data => {
                const resCount = data.count || 0;
                const delCount = data.delivery_count || 0;
                const totalCount = resCount + delCount;

                // New reservations
                if (resCount > lastResCount && lastResCount >= 0) {
                    showNotifToast('fa-calendar-check', (resCount - lastResCount) + ' nouvelle' + ((resCount - lastResCount) > 1 ? 's' : '') + ' réservation' + ((resCount - lastResCount) > 1 ? 's' : ''));
                    if (!notifMuted) playNotificationSound();
                }
                // New delivery orders
                if (delCount > lastDelCount && lastDelCount >= 0) {
                    showNotifToast('fa-motorcycle', (delCount - lastDelCount) + ' nouvelle' + ((delCount - lastDelCount) > 1 ? 's' : '') + ' commande' + ((delCount - lastDelCount) > 1 ? 's' : '') + ' livraison');
                    if (!notifMuted) playNotificationSound();
                }

                lastResCount = resCount;
                lastDelCount = delCount;

                badge.textContent = resCount;
                badge.style.display = resCount > 0 ? '' : 'none';
                if (delBadge) {
                    delBadge.textContent = delCount;
                    delBadge.style.display = delCount > 0 ? '' : 'none';
                }
                if (fabBadge) {
                    fabBadge.textContent = totalCount;
                    fabBadge.style.display = totalCount > 0 ? '' : 'none';
                }
            })
            .catch(() => {});
    }

    function showNotifToast(icon, message) {
        const notif = document.createElement('div');
        notif.className = 'reservation-notif-toast';
        notif.innerHTML = '<i class="fas ' + icon + '"></i> ' + message;
        document.body.appendChild(notif);
        setTimeout(() => notif.classList.add('show'), 10);
        setTimeout(() => { notif.classList.remove('show'); setTimeout(() => notif.remove(), 300); }, 5000);
    }

    // Initial fab badge sync
    const initTotal = lastResCount + lastDelCount;
    fabBadge.textContent = initTotal;
    fabBadge.style.display = initTotal > 0 ? '' : 'none';

    setInterval(checkPending, 15000);
    checkPending(); // Initial check for delivery count
})();
</script>
<script>
// Scroll sidebar to active nav item
(function() {
    const sidebar = document.getElementById('adminSidebar');
    const active = sidebar && sidebar.querySelector('.sidebar-nav a.active');
    if (active && sidebar) {
        setTimeout(function() {
            active.scrollIntoView({ block: 'center', behavior: 'instant' });
        }, 50);
    }
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
<script src="<?= APP_URL ?>/assets/js/tour.js"></script>
</body>
</html>
