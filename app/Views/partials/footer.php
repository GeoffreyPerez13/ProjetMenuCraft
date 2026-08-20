    </main>
</div><!-- /.admin-layout -->

<?php if (empty($_hideTourButton)): ?>
<button class="tour-trigger-btn" id="tourTriggerBtn" onclick="startPageTour()" title="Guide interactif">
    <i class="fas fa-question"></i>
</button>
<?php endif; ?>

<?php if (empty($options['hide_reservation_fab']) || ($options['hide_reservation_fab'] ?? '0') !== '1'): ?>
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
        <h3><i class="fas fa-concierge-bell"></i> Réservations en attente</h3>
        <button onclick="toggleReservationPanel()" class="reservation-panel-close"><i class="fas fa-times"></i></button>
    </div>
    <div class="reservation-panel-body" id="reservationPanelBody">
        <div class="reservation-panel-loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>
    </div>
</div>
<div class="reservation-panel-overlay" id="reservationPanelOverlay" onclick="toggleReservationPanel()"></div>

<script src="<?= APP_URL ?>/assets/js/notification-sound.js"></script>
<script>
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
function toggleReservationPanel() {
    reservationPanelOpen = !reservationPanelOpen;
    document.getElementById('reservationPanel').classList.toggle('open', reservationPanelOpen);
    document.getElementById('reservationPanelOverlay').classList.toggle('active', reservationPanelOpen);
    if (reservationPanelOpen) loadPendingReservations();
}

function loadPendingReservations() {
    const body = document.getElementById('reservationPanelBody');
    body.innerHTML = '<div class="reservation-panel-loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    fetch('<?= APP_URL ?>?page=reservation-pending-list', {credentials: 'same-origin'})
        .then(r => r.json())
        .then(data => {
            if (!data.reservations || data.reservations.length === 0) {
                body.innerHTML = '<div class="reservation-panel-empty"><i class="fas fa-check-circle"></i><p>Aucune réservation en attente</p></div>';
                return;
            }
            let html = '';
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
                
                // Table select
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

function updateFabBadge() {
    fetch('<?= APP_URL ?>?page=reservation-pending-count', {credentials: 'same-origin'})
        .then(r => r.json())
        .then(data => {
            const count = data.count || 0;
            const fabBadge = document.getElementById('fabBadge');
            const sidebarBadge = document.getElementById('pendingBadge');
            fabBadge.textContent = count;
            fabBadge.style.display = count > 0 ? '' : 'none';
            if (sidebarBadge) {
                sidebarBadge.textContent = count;
                sidebarBadge.style.display = count > 0 ? '' : 'none';
            }
        }).catch(() => {});
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// Real-time reservation notifications polling with sound
(function() {
    const badge = document.getElementById('pendingBadge');
    const fabBadge = document.getElementById('fabBadge');
    if (!badge) return;
    let lastCount = parseInt(badge.textContent) || 0;

    function checkPending() {
        fetch('<?= APP_URL ?>?page=reservation-pending-count', {credentials: 'same-origin'})
            .then(r => r.json())
            .then(data => {
                const count = data.count || 0;
                if (count > lastCount && lastCount >= 0) {
                    showReservationNotif(count - lastCount);
                    if (!notifMuted) playNotificationSound();
                }
                lastCount = count;
                badge.textContent = count;
                badge.style.display = count > 0 ? '' : 'none';
                if (fabBadge) {
                    fabBadge.textContent = count;
                    fabBadge.style.display = count > 0 ? '' : 'none';
                }
            })
            .catch(() => {});
    }

    function showReservationNotif(newCount) {
        const notif = document.createElement('div');
        notif.className = 'reservation-notif-toast';
        notif.innerHTML = '<i class="fas fa-bell"></i> ' + newCount + ' nouvelle' + (newCount > 1 ? 's' : '') + ' réservation' + (newCount > 1 ? 's' : '');
        document.body.appendChild(notif);
        setTimeout(() => notif.classList.add('show'), 10);
        setTimeout(() => { notif.classList.remove('show'); setTimeout(() => notif.remove(), 300); }, 5000);
    }

    // Initial fab badge sync
    fabBadge.textContent = lastCount;
    fabBadge.style.display = lastCount > 0 ? '' : 'none';

    setInterval(checkPending, 15000);
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
