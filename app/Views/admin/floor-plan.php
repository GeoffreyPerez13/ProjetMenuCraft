<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-map"></i> Plan de salle</h2>
        <div style="display:flex;gap:8px;">
            <button type="button" class="btn btn-primary btn-sm" onclick="addTable()"><i class="fas fa-plus"></i> Table</button>
            <button type="button" class="btn btn-success btn-sm" onclick="saveFloorPlan()"><i class="fas fa-save"></i> Sauvegarder</button>
        </div>
    </div>

    <?php if (!empty($floors)): ?>
    <!-- Sélection de l'étage -->
    <div style="display:flex;gap:8px;margin-bottom:var(--spacing-lg);">
        <?php foreach ($floors as $floor): ?>
        <button type="button" class="btn btn-sm <?= $floor === reset($floors) ? 'btn-primary' : 'btn-secondary' ?>"
                onclick="selectFloor(<?= $floor->id ?>)" data-floor="<?= $floor->id ?>">
            <?= htmlspecialchars($floor->name) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Zone du plan -->
    <div id="floorPlanArea" style="position:relative;width:100%;height:500px;background:var(--color-bg-alt);border:2px solid var(--color-border);border-radius:var(--radius-md);overflow:hidden;cursor:crosshair;">
        <!-- Les tables seront générées en JS -->
    </div>

    <div style="margin-top:var(--spacing-md);display:flex;gap:16px;font-size:0.8rem;color:var(--color-text-muted);">
        <span><i class="fas fa-info-circle"></i> Glissez les tables pour les positionner</span>
        <span><i class="fas fa-mouse-pointer"></i> Double-cliquez pour éditer</span>
    </div>
    <?php endif; ?>
</div>

<script>
let currentFloorId = <?= $floors[0]->id ?? 0 ?>;
let floorData = <?= json_encode($floorData ?? []) ?>;
let tables = [];

function selectFloor(id) {
    currentFloorId = id;
    document.querySelectorAll('[data-floor]').forEach(btn => {
        btn.className = btn.dataset.floor == id ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-secondary';
    });
    renderFloor();
}

function renderFloor() {
    const area = document.getElementById('floorPlanArea');
    area.innerHTML = '';
    const data = floorData[currentFloorId];
    if (!data) return;

    tables = data.tables || [];
    tables.forEach((t, i) => {
        const el = document.createElement('div');
        el.style.cssText = `position:absolute;left:${t.x}px;top:${t.y}px;width:${t.width || 80}px;height:${t.height || 80}px;background:var(--color-primary-bg);border:2px solid var(--color-primary);border-radius:${t.shape === 'round' ? '50%' : 'var(--radius-sm)'};display:flex;align-items:center;justify-content:center;cursor:grab;font-size:0.75rem;font-weight:600;color:var(--color-primary);user-select:none;`;
        el.textContent = t.table_number || (i + 1);
        el.dataset.index = i;
        makeDraggable(el);
        area.appendChild(el);
    });
}

function makeDraggable(el) {
    let offsetX, offsetY, isDragging = false;
    el.addEventListener('mousedown', function(e) {
        isDragging = true;
        offsetX = e.clientX - el.offsetLeft;
        offsetY = e.clientY - el.offsetTop;
        el.style.cursor = 'grabbing';
        el.style.zIndex = 100;
    });
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        const area = document.getElementById('floorPlanArea');
        const rect = area.getBoundingClientRect();
        let x = e.clientX - rect.left - (el.offsetWidth / 2);
        let y = e.clientY - rect.top - (el.offsetHeight / 2);
        x = Math.max(0, Math.min(x, rect.width - el.offsetWidth));
        y = Math.max(0, Math.min(y, rect.height - el.offsetHeight));
        el.style.left = x + 'px';
        el.style.top = y + 'px';
        const idx = parseInt(el.dataset.index);
        if (tables[idx]) {
            tables[idx].x = x;
            tables[idx].y = y;
        }
    });
    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            el.style.cursor = 'grab';
            el.style.zIndex = '';
        }
    });
}

function addTable() {
    tables.push({ table_number: String(tables.length + 1), seats: 4, x: 50, y: 50, width: 80, height: 80, shape: 'square', rotation: 0 });
    if (!floorData[currentFloorId]) floorData[currentFloorId] = { tables: [], elements: [] };
    floorData[currentFloorId].tables = tables;
    renderFloor();
}

function saveFloorPlan() {
    fetch('<?= APP_URL ?>?page=floor-plan-save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            floor_id: currentFloorId,
            tables: tables,
            elements: floorData[currentFloorId]?.elements || []
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) alert('Plan sauvegardé !');
    });
}

renderFloor();
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
