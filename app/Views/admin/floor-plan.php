<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($floors)) $floors = [];
if (!isset($floorData)) $floorData = [];
?>

<style>
/* ─── Floor Plan Layout ─── */
.fp-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: var(--spacing-lg);
}
.fp-main { min-width: 0; }

/* ─── Room Tabs ─── */
.fp-rooms {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: var(--spacing-md);
}
.fp-room-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    background: var(--color-bg);
    font-size: 0.84rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    position: relative;
}
.fp-room-tab:hover { background: var(--color-bg-alt); }
.fp-room-tab.active {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
}
.fp-room-tab .fp-room-actions {
    display: inline-flex;
    gap: 4px;
    margin-left: 4px;
    opacity: 0;
    transition: opacity 0.15s;
}
.fp-room-tab.active .fp-room-actions,
.fp-room-tab:hover .fp-room-actions { opacity: 1; }
.fp-room-tab .fp-room-actions button {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 0.7rem;
    opacity: 0.8;
    padding: 2px;
}
.fp-room-tab .fp-room-actions button:hover { opacity: 1; }
.fp-room-add {
    padding: 8px 12px;
    border: 1px dashed var(--color-border);
    border-radius: var(--radius-sm);
    background: none;
    color: var(--color-text-muted);
    font-size: 0.84rem;
    cursor: pointer;
    transition: all 0.15s;
}
.fp-room-add:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}

/* ─── Canvas Area ─── */
.fp-canvas {
    position: relative;
    width: 100%;
    height: 500px;
    background: var(--color-bg-alt);
    border: 2px solid var(--color-border);
    border-radius: var(--radius-md);
    overflow: auto;
}

/* ─── Structural Elements ─── */
.fp-element {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    cursor: grab;
    user-select: none;
    font-size: 0.7rem;
    font-weight: 600;
    transition: box-shadow 0.15s, border-color 0.15s;
    padding: 4px 6px;
    border-radius: var(--radius-sm);
}
.fp-element:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
.fp-element.selected {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}
.fp-element i { font-size: 0.8rem; }
.fp-element.type-door {
    background: rgba(59, 130, 246, 0.08);
    border: 2px solid #3b82f6;
    color: #3b82f6;
}
.fp-element.type-stairs {
    background: rgba(139, 92, 246, 0.08);
    border: 2px solid #8b5cf6;
    color: #8b5cf6;
}
.fp-element.type-wall {
    background: rgba(107, 114, 128, 0.15);
    border: 2px solid #6b7280;
    color: #6b7280;
    min-height: 8px;
}
.fp-element.type-bar {
    background: rgba(245, 158, 11, 0.08);
    border: 2px solid #f59e0b;
    color: #f59e0b;
}
.fp-element.type-wc {
    background: rgba(16, 185, 129, 0.08);
    border: 2px solid #10b981;
    color: #10b981;
}
.fp-element.type-cuisine {
    background: rgba(239, 68, 68, 0.08);
    border: 2px solid #ef4444;
    color: #ef4444;
}

/* Element add dropdown */
.fp-add-elements {
    position: relative;
    display: inline-block;
}
.fp-add-elements-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 4px;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    z-index: 200;
    min-width: 160px;
    padding: 4px;
}
.fp-add-elements-menu.show { display: block; }
.fp-add-elements-menu button {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: none;
    font-size: 0.82rem;
    color: var(--color-text);
    cursor: pointer;
    border-radius: var(--radius-sm);
    transition: background 0.1s;
}
.fp-add-elements-menu button:hover { background: var(--color-bg-alt); }
.fp-add-elements-menu button i { width: 16px; text-align: center; }

/* ─── Table Elements ─── */
.fp-table {
    position: absolute;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(180, 83, 9, 0.08);
    border: 2px solid var(--color-primary);
    cursor: grab;
    user-select: none;
    transition: box-shadow 0.15s, border-color 0.15s;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--color-primary);
    line-height: 1.2;
    text-align: center;
    padding: 4px;
}
.fp-table:hover {
    box-shadow: 0 2px 8px rgba(180, 83, 9, 0.2);
}
.fp-table.selected {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}
.fp-table.shape-square { border-radius: var(--radius-sm); }
.fp-table.shape-round { border-radius: 50%; }
.fp-table.shape-rectangle { border-radius: var(--radius-sm); }
.fp-table-name {
    font-size: 0.68rem;
    opacity: 0.7;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 90%;
}
.fp-table-seats {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--color-primary);
    color: #fff;
    font-size: 0.6rem;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

/* ─── Sidebar Panel ─── */
.fp-sidebar .card { position: sticky; top: 80px; }
.fp-panel-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: var(--spacing-md);
    display: flex;
    align-items: center;
    gap: 8px;
}
.fp-form-group {
    margin-bottom: 12px;
}
.fp-form-group label {
    display: block;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--color-text-muted);
    margin-bottom: 4px;
}
.fp-form-group input,
.fp-form-group select,
.fp-form-group textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-size: 0.84rem;
    background: var(--color-bg);
    color: var(--color-text);
}
.fp-form-group textarea { resize: vertical; min-height: 60px; }
.fp-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.fp-shapes {
    display: flex;
    gap: 6px;
}
.fp-shape-btn {
    width: 40px;
    height: 40px;
    border: 2px solid var(--color-border);
    background: var(--color-bg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    color: var(--color-text-muted);
}
.fp-shape-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }
.fp-shape-btn.active { border-color: var(--color-primary); background: rgba(180,83,9,0.08); color: var(--color-primary); }
.fp-shape-btn[data-shape="square"] { border-radius: 4px; }
.fp-shape-btn[data-shape="round"] { border-radius: 50%; }
.fp-shape-btn[data-shape="rectangle"] { border-radius: 4px; }

.fp-empty-panel {
    text-align: center;
    padding: var(--spacing-lg) var(--spacing-md);
    color: var(--color-text-muted);
    font-size: 0.84rem;
}
.fp-empty-panel i { font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.4; }

/* ─── Toolbar ─── */
.fp-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: var(--spacing-md);
    flex-wrap: wrap;
}
.fp-toolbar-left { display: flex; gap: 8px; flex-wrap: wrap; }
.fp-hints {
    display: flex;
    gap: 16px;
    font-size: 0.78rem;
    color: var(--color-text-muted);
    margin-top: var(--spacing-sm);
    flex-wrap: wrap;
}

.fp-save-reminder {
    font-size: 0.8rem;
    color: var(--color-text-muted);
    background: var(--color-bg-alt);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    margin-top: var(--spacing-sm);
    display: flex;
    align-items: center;
    gap: 8px;
}
.fp-save-reminder i {
    color: var(--color-primary);
    flex-shrink: 0;
}

/* ─── Responsive ─── */
@media (max-width: 1024px) {
    .fp-layout {
        grid-template-columns: 1fr;
    }
    .fp-sidebar .card { position: static; }
    .fp-canvas { height: 400px; }
}

@media (max-width: 768px) {
    .fp-canvas { height: 350px; }
    .fp-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
    }
    .fp-toolbar-left {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .fp-toolbar-left .btn,
    .fp-toolbar-left .fp-add-elements { text-align: center; justify-content: center; }
    .fp-toolbar-left .fp-add-elements { display: flex; }
    .fp-toolbar-left .fp-add-elements .btn { width: 100%; justify-content: center; }
    .fp-toolbar .btn-success { justify-content: center; text-align: center; }
    .fp-rooms { gap: 4px; }
    .fp-room-tab { padding: 6px 10px; font-size: 0.78rem; }
    .fp-room-tab .fp-room-actions { opacity: 1; }
    .fp-add-elements-menu { left: auto; right: 0; }
    .fp-save-reminder { font-size: 0.75rem; }
    .fp-panel-title { font-size: 0.82rem; }
}

@media (max-width: 480px) {
    .fp-canvas { height: 260px; }
    .fp-form-row { grid-template-columns: 1fr; }
    .fp-hints { flex-direction: column; gap: 6px; font-size: 0.72rem; }
    .fp-room-tab { padding: 5px 8px; font-size: 0.72rem; }
    .fp-room-tab .fp-room-actions { opacity: 1; margin-left: 2px; }
    .fp-room-tab .fp-room-actions button { font-size: 0.65rem; }
    .fp-toolbar-left {
        grid-template-columns: 1fr;
    }
    .fp-toolbar-left .btn { font-size: 0.76rem; padding: 7px 10px; }
    .fp-form-group label { font-size: 0.72rem; }
    .fp-form-group input,
    .fp-form-group select,
    .fp-form-group textarea { font-size: 0.8rem; padding: 7px 8px; }
    .fp-save-reminder { font-size: 0.7rem; padding: 6px 10px; }
    .card-header h2 { font-size: 1rem; }
}
</style>

<div class="fp-layout">
    <!-- Main Area -->
    <div class="fp-main">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-map"></i> Plan de salle</h2>
            </div>

            <!-- Room Tabs -->
            <div class="fp-rooms" id="roomTabs">
                <?php foreach ($floors as $i => $floor): ?>
                <div class="fp-room-tab <?= $i === 0 ? 'active' : '' ?>" data-floor-id="<?= $floor->id ?>" onclick="selectFloor(<?= $floor->id ?>)">
                    <span class="fp-room-name"><?= htmlspecialchars($floor->name) ?></span>
                    <span class="fp-room-actions">
                        <button type="button" onclick="renameRoom(event, <?= $floor->id ?>)" title="Renommer"><i class="fas fa-pen"></i></button>
                        <button type="button" onclick="deleteRoom(event, <?= $floor->id ?>)" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </span>
                </div>
                <?php endforeach; ?>
                <button type="button" class="fp-room-add" onclick="createRoom()"><i class="fas fa-plus"></i> Salle</button>
            </div>

            <!-- Toolbar -->
            <div class="fp-toolbar">
                <div class="fp-toolbar-left">
                    <button type="button" class="btn btn-primary btn-sm" onclick="addTable()"><i class="fas fa-plus"></i> Table</button>
                    <div class="fp-add-elements">
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleElementMenu()">
                            <i class="fas fa-shapes"></i> Élément <i class="fas fa-caret-down" style="font-size:0.7rem;"></i>
                        </button>
                        <div class="fp-add-elements-menu" id="elementMenu">
                            <button type="button" onclick="addElement('door')"><i class="fas fa-door-open" style="color:#3b82f6;"></i> Porte</button>
                            <button type="button" onclick="addElement('stairs')"><i class="fas fa-stairs" style="color:#8b5cf6;"></i> Escalier</button>
                            <button type="button" onclick="addElement('wall')"><i class="fas fa-grip-lines" style="color:#6b7280;"></i> Mur</button>
                            <button type="button" onclick="addElement('bar')"><i class="fas fa-wine-glass" style="color:#f59e0b;"></i> Bar / Comptoir</button>
                            <button type="button" onclick="addElement('wc')"><i class="fas fa-restroom" style="color:#10b981;"></i> WC</button>
                            <button type="button" onclick="addElement('cuisine')"><i class="fas fa-fire-burner" style="color:#ef4444;"></i> Cuisine</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSelected()" id="btnDeleteTable" style="display:none;"><i class="fas fa-trash"></i> Supprimer</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteAllTables()" style="opacity:0.8;"><i class="fas fa-trash-alt"></i> Tout supprimer</button>
                </div>
                <button type="button" class="btn btn-success btn-sm" onclick="saveFloorPlan()"><i class="fas fa-save"></i> Sauvegarder</button>
            </div>

            <!-- Canvas -->
            <div class="fp-canvas" id="floorPlanArea">
                <!-- Tables rendered by JS -->
            </div>

            <p class="fp-save-reminder"><i class="fas fa-info-circle"></i> Pensez à sauvegarder vos modifications avant de quitter la page.</p>

            <div class="fp-hints">
                <span><i class="fas fa-arrows-alt"></i> Glissez pour positionner</span>
                <span><i class="fas fa-mouse-pointer"></i> Cliquez pour sélectionner</span>
                <span><i class="fas fa-hand-pointer"></i> Double-cliquez pour éditer</span>
            </div>
        </div>
    </div>

    <!-- Sidebar: Properties -->
    <div class="fp-sidebar">
        <div class="card" id="tablePanel">
            <div id="tablePanelEmpty">
                <div class="fp-empty-panel">
                    <i class="fas fa-chair"></i>
                    <p>Sélectionnez une table ou un élément pour modifier ses propriétés</p>
                </div>
            </div>
            <div id="tablePanelForm" style="display:none;padding:var(--spacing-md);">
                <div class="fp-panel-title"><i class="fas fa-chair"></i> Propriétés de la table</div>

                <div class="fp-form-row">
                    <div class="fp-form-group">
                        <label>N° table</label>
                        <input type="text" id="propNumber" placeholder="1, A1..." onchange="updateTableProp('table_number', this.value)">
                    </div>
                    <div class="fp-form-group">
                        <label>Nom</label>
                        <input type="text" id="propName" placeholder="Table VIP..." onchange="updateTableProp('name', this.value)">
                    </div>
                </div>

                <div class="fp-form-group">
                    <label>Forme</label>
                    <div class="fp-shapes">
                        <button type="button" class="fp-shape-btn" data-shape="square" onclick="setShape('square')" title="Carrée">
                            <i class="fas fa-square"></i>
                        </button>
                        <button type="button" class="fp-shape-btn" data-shape="rectangle" onclick="setShape('rectangle')" title="Rectangulaire">
                            <i class="fas fa-minus" style="font-size:1.2rem;"></i>
                        </button>
                        <button type="button" class="fp-shape-btn" data-shape="round" onclick="setShape('round')" title="Ronde">
                            <i class="fas fa-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="fp-form-row">
                    <div class="fp-form-group">
                        <label>Couverts max</label>
                        <input type="number" id="propSeats" min="1" max="30" onchange="updateTableProp('seats', parseInt(this.value))">
                    </div>
                    <div class="fp-form-group">
                        <label>Zone</label>
                        <select id="propZone" onchange="updateTableProp('zone', this.value)">
                            <option value="interieur">Intérieur</option>
                            <option value="terrasse">Terrasse</option>
                            <option value="prive">Privé</option>
                            <option value="bar">Bar</option>
                        </select>
                    </div>
                </div>

                <div class="fp-form-row">
                    <div class="fp-form-group">
                        <label>Largeur (px)</label>
                        <input type="number" id="propWidth" min="40" max="300" step="10" onchange="updateTableProp('width', parseInt(this.value))">
                    </div>
                    <div class="fp-form-group">
                        <label>Hauteur (px)</label>
                        <input type="number" id="propHeight" min="40" max="300" step="10" onchange="updateTableProp('height', parseInt(this.value))">
                    </div>
                </div>

                <div class="fp-form-group">
                    <label>Rotation (<span id="tableRotationValue">0</span>°)</label>
                    <input type="range" id="propRotation" min="0" max="360" step="15" value="0" oninput="document.getElementById('tableRotationValue').textContent=this.value; updateTableProp('rotation', parseInt(this.value))" style="width:100%;cursor:pointer;">
                </div>

                <div class="fp-form-group">
                    <label>Notes</label>
                    <textarea id="propNotes" placeholder="Proche fenêtre, PMR, anniversaire..." onchange="updateTableProp('notes', this.value)"></textarea>
                </div>
            </div>

            <!-- Element Properties Panel -->
            <div id="elementPanelForm" style="display:none;padding:var(--spacing-md);">
                <div class="fp-panel-title"><i class="fas fa-shapes"></i> Propriétés de l'élément</div>

                <div class="fp-form-group">
                    <label>Type</label>
                    <select id="elPropType" onchange="updateElementProp('element_type', this.value)">
                        <option value="door">Porte</option>
                        <option value="stairs">Escalier</option>
                        <option value="wall">Mur</option>
                        <option value="bar">Bar / Comptoir</option>
                        <option value="wc">WC</option>
                        <option value="cuisine">Cuisine</option>
                    </select>
                </div>

                <div class="fp-form-group">
                    <label>Label (optionnel)</label>
                    <input type="text" id="elPropLabel" placeholder="Entrée principale..." onchange="updateElementProp('label', this.value)">
                </div>

                <div class="fp-form-row">
                    <div class="fp-form-group">
                        <label>Largeur (px)</label>
                        <input type="number" id="elPropWidth" min="20" max="500" step="10" onchange="updateElementProp('width', parseInt(this.value))">
                    </div>
                    <div class="fp-form-group">
                        <label>Hauteur (px)</label>
                        <input type="number" id="elPropHeight" min="8" max="500" step="10" onchange="updateElementProp('height', parseInt(this.value))">
                    </div>
                </div>

                <div class="fp-form-group">
                    <label>Rotation (<span id="elRotationValue">0</span>°)</label>
                    <input type="range" id="elPropRotation" min="0" max="360" step="15" value="0" oninput="document.getElementById('elRotationValue').textContent=this.value; updateElementProp('rotation', parseInt(this.value))" style="width:100%;cursor:pointer;">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const APP = '<?= APP_URL ?>';
const CSRF_TOKEN = '<?= htmlspecialchars($csrf_token) ?>';
let currentFloorId = <?= !empty($floors) ? $floors[0]->id : 0 ?>;
let floorData = <?= json_encode(array_map(function($d) {
    return [
        'tables' => array_map(function($t) {
            return [
                'table_number' => $t->table_number ?? '',
                'name' => $t->name ?? '',
                'seats' => (int)($t->seats ?? 4),
                'x' => (int)($t->x ?? 0),
                'y' => (int)($t->y ?? 0),
                'width' => (int)($t->width ?? 80),
                'height' => (int)($t->height ?? 80),
                'shape' => $t->shape ?? 'square',
                'rotation' => (int)($t->rotation ?? 0),
                'zone' => $t->zone ?? 'interieur',
                'notes' => $t->notes ?? '',
            ];
        }, $d['tables']),
        'elements' => array_map(function($e) {
            return [
                'element_type' => $e->element_type ?? 'bar',
                'label' => $e->label ?? '',
                'x' => (int)($e->x ?? 0),
                'y' => (int)($e->y ?? 0),
                'width' => (int)($e->width ?? 100),
                'height' => (int)($e->height ?? 60),
                'rotation' => (int)($e->rotation ?? 0),
            ];
        }, $d['elements']),
    ];
}, $floorData)) ?>;
let tables = [];
let selectedIndex = -1;

// ─── Room Management ───
function selectFloor(id) {
    currentFloorId = id;
    selectedIndex = -1;
    document.querySelectorAll('.fp-room-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.floorId == id);
    });
    renderFloor();
    showPanel(false);
}

function createRoom() {
    const name = prompt('Nom de la nouvelle salle :');
    if (!name || !name.trim()) return;
    fetch(APP + '?page=floor-plan-create-room', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ name: name.trim() })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            floorData[res.id] = { tables: [], elements: [] };
            const tabs = document.getElementById('roomTabs');
            const addBtn = tabs.querySelector('.fp-room-add');
            const tab = document.createElement('div');
            tab.className = 'fp-room-tab';
            tab.dataset.floorId = res.id;
            tab.onclick = () => selectFloor(res.id);
            tab.innerHTML = `<span class="fp-room-name">${escHtml(res.name)}</span>
                <span class="fp-room-actions">
                    <button type="button" onclick="renameRoom(event, ${res.id})" title="Renommer"><i class="fas fa-pen"></i></button>
                    <button type="button" onclick="deleteRoom(event, ${res.id})" title="Supprimer"><i class="fas fa-trash"></i></button>
                </span>`;
            tabs.insertBefore(tab, addBtn);
            selectFloor(res.id);
            showToast('Salle créée');
        } else {
            showToast(res.error || 'Erreur', 'error');
        }
    })
    .catch(() => showToast('Erreur de connexion', 'error'));
}

function renameRoom(e, id) {
    e.stopPropagation();
    const tab = document.querySelector(`.fp-room-tab[data-floor-id="${id}"]`);
    const current = tab.querySelector('.fp-room-name').textContent;
    const name = prompt('Nouveau nom :', current);
    if (!name || !name.trim() || name.trim() === current) return;
    fetch(APP + '?page=floor-plan-rename-room', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ id, name: name.trim() })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            tab.querySelector('.fp-room-name').textContent = name.trim();
            showToast('Salle renommée');
        } else {
            showToast(res.error || 'Erreur', 'error');
        }
    })
    .catch(() => showToast('Erreur de connexion', 'error'));
}

function deleteRoom(e, id) {
    e.stopPropagation();
    if (!confirm('Supprimer cette salle et toutes ses tables ?')) return;
    fetch(APP + '?page=floor-plan-delete-room', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            delete floorData[id];
            const tab = document.querySelector(`.fp-room-tab[data-floor-id="${id}"]`);
            if (tab) tab.remove();
            const first = document.querySelector('.fp-room-tab');
            if (first) selectFloor(parseInt(first.dataset.floorId));
            showToast('Salle supprimée');
        } else {
            showToast(res.error || 'Erreur', 'error');
        }
    })
    .catch(() => showToast('Erreur de connexion', 'error'));
}

// ─── State ───
let isDirty = false;
let selectedType = null; // 'table' or 'element'
let selectedElementIndex = -1;

const elementIcons = {
    door: 'fa-door-open',
    stairs: 'fa-stairs',
    wall: 'fa-grip-lines',
    bar: 'fa-wine-glass',
    wc: 'fa-restroom',
    cuisine: 'fa-fire-burner'
};
const elementLabels = {
    door: 'Porte',
    stairs: 'Escalier',
    wall: 'Mur',
    bar: 'Bar',
    wc: 'WC',
    cuisine: 'Cuisine'
};
const elementDefaults = {
    door: { width: 60, height: 14 },
    stairs: { width: 80, height: 60 },
    wall: { width: 150, height: 10 },
    bar: { width: 160, height: 40 },
    wc: { width: 60, height: 60 },
    cuisine: { width: 120, height: 80 }
};

function markDirty() { isDirty = true; }

// ─── Floor Rendering ───
function renderFloor() {
    const area = document.getElementById('floorPlanArea');
    area.innerHTML = '';
    const data = floorData[currentFloorId];
    if (!data) return;

    tables = data.tables || [];
    const elements = data.elements || [];

    // Calculate bounding box for spacer (tables + elements)
    let maxX = 0, maxY = 0;
    tables.forEach((t) => {
        const right = (t.x || 0) + (t.width || 80) + 20;
        const bottom = (t.y || 0) + (t.height || 80) + 20;
        if (right > maxX) maxX = right;
        if (bottom > maxY) maxY = bottom;
    });
    elements.forEach((e) => {
        const right = (e.x || 0) + (e.width || 80) + 20;
        const bottom = (e.y || 0) + (e.height || 60) + 20;
        if (right > maxX) maxX = right;
        if (bottom > maxY) maxY = bottom;
    });

    // Spacer for scrollable expansion
    const spacer = document.createElement('div');
    spacer.style.width = Math.max(maxX, area.clientWidth) + 'px';
    spacer.style.height = Math.max(maxY, area.clientHeight) + 'px';
    spacer.style.position = 'absolute';
    spacer.style.top = '0';
    spacer.style.left = '0';
    spacer.style.pointerEvents = 'none';
    area.appendChild(spacer);

    // Render structural elements
    elements.forEach((e, i) => {
        const el = document.createElement('div');
        el.className = `fp-element type-${e.element_type}` + (selectedType === 'element' && selectedElementIndex === i ? ' selected' : '');
        el.style.left = e.x + 'px';
        el.style.top = e.y + 'px';
        el.style.width = (e.width || 80) + 'px';
        el.style.height = (e.height || 40) + 'px';
        if (e.rotation) el.style.transform = `rotate(${e.rotation}deg)`;
        el.dataset.elIndex = i;

        const icon = elementIcons[e.element_type] || 'fa-cube';
        el.innerHTML = `<i class="fas ${icon}"></i>`;

        el.addEventListener('click', (ev) => { ev.stopPropagation(); selectElement(i); });
        makeDraggable(el, i, 'element');
        area.appendChild(el);
    });

    // Render tables
    tables.forEach((t, i) => {
        const el = document.createElement('div');
        const shapeClass = 'shape-' + (t.shape || 'square');
        el.className = `fp-table ${shapeClass}` + (selectedType === 'table' && selectedIndex === i ? ' selected' : '');
        el.style.left = t.x + 'px';
        el.style.top = t.y + 'px';
        el.style.width = (t.width || 80) + 'px';
        el.style.height = (t.height || 80) + 'px';
        if (t.rotation) el.style.transform = `rotate(${t.rotation}deg)`;
        el.dataset.index = i;

        const rot = t.rotation || 0;
        const counterRot = rot ? `style="transform:rotate(-${rot}deg)"` : '';
        let label = t.table_number || String(i + 1);
        el.innerHTML = `<span ${counterRot}>${escHtml(label)}</span>` +
            (t.name ? `<span class="fp-table-name" ${counterRot}>${escHtml(t.name)}</span>` : '') +
            `<span class="fp-table-seats" ${counterRot}>${t.seats || 4}</span>`;

        el.addEventListener('click', (ev) => { ev.stopPropagation(); selectTable(i); });
        el.addEventListener('dblclick', (ev) => { ev.stopPropagation(); selectTable(i); });
        makeDraggable(el, i, 'table');
        area.appendChild(el);
    });
}

// ─── Selection ───
function selectTable(idx) {
    selectedType = 'table';
    selectedIndex = idx;
    selectedElementIndex = -1;
    document.querySelectorAll('.fp-table').forEach((el, i) => el.classList.toggle('selected', i === idx));
    document.querySelectorAll('.fp-element').forEach(el => el.classList.remove('selected'));
    showPanel('table');
    populatePanel(tables[idx]);
    document.getElementById('btnDeleteTable').style.display = 'inline-flex';
}

function selectElement(idx) {
    selectedType = 'element';
    selectedElementIndex = idx;
    selectedIndex = -1;
    document.querySelectorAll('.fp-element').forEach((el, i) => el.classList.toggle('selected', i === idx));
    document.querySelectorAll('.fp-table').forEach(el => el.classList.remove('selected'));
    showPanel('element');
    populateElementPanel(floorData[currentFloorId].elements[idx]);
    document.getElementById('btnDeleteTable').style.display = 'inline-flex';
}

function deselectAll() {
    selectedType = null;
    selectedIndex = -1;
    selectedElementIndex = -1;
    document.querySelectorAll('.fp-table, .fp-element').forEach(el => el.classList.remove('selected'));
    showPanel(null);
    document.getElementById('btnDeleteTable').style.display = 'none';
}

function showPanel(type) {
    document.getElementById('tablePanelEmpty').style.display = type ? 'none' : 'block';
    document.getElementById('tablePanelForm').style.display = type === 'table' ? 'block' : 'none';
    document.getElementById('elementPanelForm').style.display = type === 'element' ? 'block' : 'none';
}

function populatePanel(t) {
    document.getElementById('propNumber').value = t.table_number || '';
    document.getElementById('propName').value = t.name || '';
    document.getElementById('propSeats').value = t.seats || 4;
    document.getElementById('propZone').value = t.zone || 'interieur';
    document.getElementById('propWidth').value = t.width || 80;
    document.getElementById('propHeight').value = t.height || 80;
    document.getElementById('propRotation').value = t.rotation || 0;
    document.getElementById('tableRotationValue').textContent = t.rotation || 0;
    document.getElementById('propNotes').value = t.notes || '';
    document.querySelectorAll('.fp-shape-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.shape === (t.shape || 'square'));
    });
}

function populateElementPanel(e) {
    document.getElementById('elPropType').value = e.element_type || 'door';
    document.getElementById('elPropLabel').value = e.label || '';
    document.getElementById('elPropWidth').value = e.width || 80;
    document.getElementById('elPropHeight').value = e.height || 40;
    document.getElementById('elPropRotation').value = e.rotation || 0;
    document.getElementById('elRotationValue').textContent = e.rotation || 0;
}

// ─── Table Properties ───
function updateTableProp(key, value) {
    if (selectedIndex < 0) return;
    tables[selectedIndex][key] = value;
    floorData[currentFloorId].tables = tables;
    markDirty();
    renderFloor();
}

function setShape(shape) {
    if (selectedIndex < 0) return;
    let t = tables[selectedIndex];
    t.shape = shape;
    if (shape === 'rectangle') {
        if (t.width <= t.height) t.width = Math.max(t.height * 1.8, 120);
    } else if (shape === 'round') {
        const size = Math.max(t.width, t.height);
        t.width = size; t.height = size;
    } else if (shape === 'square') {
        const size = Math.min(t.width, t.height);
        t.width = size; t.height = size;
    }
    floorData[currentFloorId].tables = tables;
    markDirty();
    renderFloor();
    populatePanel(t);
}

// ─── Element Properties ───
function updateElementProp(key, value) {
    if (selectedElementIndex < 0) return;
    const elements = floorData[currentFloorId].elements;
    elements[selectedElementIndex][key] = value;
    markDirty();
    renderFloor();
}

// ─── Drag & Drop (expandable canvas) ───
function makeDraggable(el, idx, type) {
    let startX, startY, isDragging = false, hasMoved = false;

    const onStart = (cx, cy) => {
        isDragging = true;
        hasMoved = false;
        const rect = el.getBoundingClientRect();
        startX = cx - rect.left;
        startY = cy - rect.top;
        el.style.cursor = 'grabbing';
        el.style.zIndex = 100;
    };

    const onMove = (cx, cy) => {
        if (!isDragging) return;
        hasMoved = true;
        const area = document.getElementById('floorPlanArea');
        const areaRect = area.getBoundingClientRect();
        let x = cx - areaRect.left + area.scrollLeft - startX;
        let y = cy - areaRect.top + area.scrollTop - startY;
        x = Math.max(0, x);
        y = Math.max(0, y);
        el.style.left = x + 'px';
        el.style.top = y + 'px';

        if (type === 'table') {
            tables[idx].x = Math.round(x);
            tables[idx].y = Math.round(y);
        } else {
            floorData[currentFloorId].elements[idx].x = Math.round(x);
            floorData[currentFloorId].elements[idx].y = Math.round(y);
        }

        // Expand spacer if dragged beyond current bounds
        const spacer = area.querySelector('div');
        if (spacer) {
            const newW = Math.max(parseInt(spacer.style.width), x + el.offsetWidth + 40);
            const newH = Math.max(parseInt(spacer.style.height), y + el.offsetHeight + 40);
            spacer.style.width = newW + 'px';
            spacer.style.height = newH + 'px';
        }
    };

    const onEnd = () => {
        if (!isDragging) return;
        isDragging = false;
        el.style.cursor = 'grab';
        el.style.zIndex = '';
        if (hasMoved) markDirty();
        if (!hasMoved) {
            if (type === 'table') selectTable(idx);
            else selectElement(idx);
        }
    };

    el.addEventListener('mousedown', (e) => { e.preventDefault(); onStart(e.clientX, e.clientY); });
    document.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY));
    document.addEventListener('mouseup', onEnd);

    el.addEventListener('touchstart', (e) => {
        const touch = e.touches[0];
        onStart(touch.clientX, touch.clientY);
    }, { passive: true });
    document.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const touch = e.touches[0];
        onMove(touch.clientX, touch.clientY);
    }, { passive: false });
    document.addEventListener('touchend', onEnd);
}

// ─── Add Table ───
function addTable() {
    const num = String(tables.length + 1);
    tables.push({
        table_number: num, name: '', seats: 4,
        x: 40 + (tables.length % 5) * 90,
        y: 40 + Math.floor(tables.length / 5) * 90,
        width: 60, height: 60, shape: 'square',
        rotation: 0, zone: 'interieur', notes: ''
    });
    if (!floorData[currentFloorId]) floorData[currentFloorId] = { tables: [], elements: [] };
    floorData[currentFloorId].tables = tables;
    markDirty();
    renderFloor();
    selectTable(tables.length - 1);
}

// ─── Add Element ───
function addElement(type) {
    closeElementMenu();
    if (!floorData[currentFloorId]) floorData[currentFloorId] = { tables: [], elements: [] };
    if (!floorData[currentFloorId].elements) floorData[currentFloorId].elements = [];
    const elements = floorData[currentFloorId].elements;
    const defaults = elementDefaults[type] || { width: 80, height: 40 };
    elements.push({
        element_type: type,
        label: '',
        x: 20 + (elements.length % 4) * 100,
        y: 20 + Math.floor(elements.length / 4) * 80,
        width: defaults.width,
        height: defaults.height,
        rotation: 0
    });
    markDirty();
    renderFloor();
    selectElement(elements.length - 1);
}

// ─── Element Menu ───
function toggleElementMenu() {
    document.getElementById('elementMenu').classList.toggle('show');
}
function closeElementMenu() {
    document.getElementById('elementMenu').classList.remove('show');
}
document.addEventListener('click', (e) => {
    if (!e.target.closest('.fp-add-elements')) closeElementMenu();
});

// ─── Delete ───
function deleteSelected() {
    if (selectedType === 'table' && selectedIndex >= 0) {
        if (!confirm('Supprimer cette table ?')) return;
        tables.splice(selectedIndex, 1);
        floorData[currentFloorId].tables = tables;
    } else if (selectedType === 'element' && selectedElementIndex >= 0) {
        if (!confirm('Supprimer cet élément ?')) return;
        floorData[currentFloorId].elements.splice(selectedElementIndex, 1);
    } else {
        return;
    }
    markDirty();
    deselectAll();
    renderFloor();
}

function deleteAllTables() {
    const data = floorData[currentFloorId];
    if (!data) return;
    const total = (data.tables || []).length + (data.elements || []).length;
    if (!total) return;
    if (!confirm('Supprimer toutes les tables et éléments de cette salle (' + total + ' objets) ?')) return;
    data.tables = [];
    data.elements = [];
    tables = [];
    markDirty();
    deselectAll();
    renderFloor();
}

// ─── Save ───
function saveFloorPlan() {
    const btn = event.target.closest('.btn');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
    btn.disabled = true;

    fetch(APP + '?page=floor-plan-save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({
            floor_id: currentFloorId,
            tables: tables,
            elements: floorData[currentFloorId]?.elements || []
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            isDirty = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Sauvegardé !';
            showToast('Plan sauvegardé');
        } else {
            btn.innerHTML = '<i class="fas fa-times"></i> Erreur';
            showToast(res.error || 'Erreur lors de la sauvegarde', 'error');
        }
        setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 1500);
    })
    .catch(() => {
        btn.innerHTML = orig;
        btn.disabled = false;
        showToast('Erreur de connexion', 'error');
    });
}

// ─── Utils ───
function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

// Click on canvas background to deselect
document.getElementById('floorPlanArea').addEventListener('click', (e) => {
    if (e.target.id === 'floorPlanArea' || e.target.style.pointerEvents === 'none') deselectAll();
});

window.addEventListener('beforeunload', (e) => {
    if (isDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Init
renderFloor();
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
