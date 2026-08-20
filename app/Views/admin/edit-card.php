<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php
if (!isset($carteMode)) $carteMode = 'editable';
if (!isset($categories)) $categories = [];
if (!isset($dishesByCategory)) $dishesByCategory = [];
if (!isset($allergenesByDish)) $allergenesByDish = [];
if (!isset($allergenes)) $allergenes = [];
if (!isset($cardImages)) $cardImages = [];
if (!isset($dailyMenus)) $dailyMenus = [];
if (!isset($admin)) $admin = null;
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-lg);flex-wrap:wrap;gap:8px;">
    <span class="badge <?= $carteMode === 'editable' ? 'badge-primary' : 'badge-warning' ?>">
        Mode : <?= $carteMode === 'editable' ? 'Éditable' : 'Images' ?>
    </span>
    <a href="<?= APP_URL ?>?page=view-card" class="btn btn-outline btn-sm">
        <i class="fas fa-eye"></i> Prévisualiser
    </a>
</div>

<?php if ($carteMode === 'editable'): ?>

<!-- ─── Catégories & Plats ────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-layer-group"></i> Catégories</h2>
        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('newCategoryForm').style.display='block'" title="Ajouter une catégorie">
                <i class="fas fa-plus"></i> Ajouter
            </button>
            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('batchCategoryForm').style.display=document.getElementById('batchCategoryForm').style.display==='none'?'block':'none'" title="Ajouter plusieurs catégories d'un coup">
                <i class="fas fa-list"></i> Ajout rapide
            </button>
        </div>
    </div>

    <!-- Formulaire ajout rapide catégories -->
    <div id="batchCategoryForm" style="display:none;margin-bottom:var(--spacing-lg);padding:var(--spacing-lg);background:var(--color-bg-alt);border-radius:var(--radius-md);">
        <form method="POST" action="<?= APP_URL ?>?page=batch-categories">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <label>Catégories <span style="font-weight:400;color:var(--color-text-muted);">(une par ligne)</span></label>
                <textarea name="names" class="form-control" rows="5" required placeholder="Entrées&#10;Plats&#10;Desserts&#10;Boissons&#10;Vins"></textarea>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Créer toutes</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('batchCategoryForm').style.display='none'">Annuler</button>
            </div>
        </form>
    </div>

    <!-- Formulaire nouvelle catégorie -->
    <div id="newCategoryForm" style="display:none;margin-bottom:var(--spacing-lg);padding:var(--spacing-lg);background:var(--color-bg-alt);border-radius:var(--radius-md);">
        <form method="POST" action="<?= APP_URL ?>?page=save-category" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom de la catégorie</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Entrées, Plats, Desserts...">
                </div>
                <div class="form-group">
                    <label>Description (optionnel)</label>
                    <input type="text" name="description" class="form-control" placeholder="Courte description">
                </div>
            </div>
            <div class="form-group">
                <label>Image (optionnel)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Créer</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="this.closest('#newCategoryForm').style.display='none'">Annuler</button>
            </div>
        </form>
    </div>

    <!-- Liste des catégories -->
    <div id="categoriesList">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-utensils"></i>
                <h3>Aucune catégorie</h3>
                <p>Commencez par créer votre première catégorie (ex: Entrées, Plats, Desserts...)</p>
            </div>
        <?php else: ?>
            <?php foreach ($categories as $cat): ?>
            <div class="category-card" data-id="<?= $cat->id ?>">
                <div class="category-header">
                    <h3>
                        <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                        <?= htmlspecialchars($cat->name) ?>
                    </h3>
                    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="toggleDishForm(<?= $cat->id ?>)" title="Ajouter un plat">
                            <i class="fas fa-plus"></i> Plat
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleBatchDishes(<?= $cat->id ?>)" title="Ajout rapide de plusieurs plats">
                            <i class="fas fa-list"></i>
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleEditCategory(<?= $cat->id ?>)" title="Modifier la catégorie">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form method="POST" action="<?= APP_URL ?>?page=delete-category" style="display:flex;" onsubmit="return confirm('Supprimer cette catégorie et tous ses plats ?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="category_id" value="<?= $cat->id ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer la catégorie"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>

                <!-- Formulaire édition catégorie (masqué) -->
                <div id="editCategoryForm-<?= $cat->id ?>" style="display:none;padding:var(--spacing-md);background:var(--color-bg-alt);border-bottom:1px solid var(--color-border);">
                    <form method="POST" action="<?= APP_URL ?>?page=save-category" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="category_id" value="<?= $cat->id ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($cat->name) ?>">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($cat->description ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nouvelle image (optionnel)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Enregistrer</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditCategory(<?= $cat->id ?>)">Annuler</button>
                        </div>
                    </form>
                </div>

                <!-- Formulaire ajout rapide plats (masqué) -->
                <div id="batchDishForm-<?= $cat->id ?>" style="display:none;padding:var(--spacing-md);background:var(--color-bg-alt);border-bottom:1px solid var(--color-border);">
                    <form method="POST" action="<?= APP_URL ?>?page=batch-dishes">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="category_id" value="<?= $cat->id ?>">
                        <div class="form-group">
                            <label>Plats <span style="font-weight:400;color:var(--color-text-muted);">(un par ligne, format : Nom ; Prix ; Description)</span></label>
                            <textarea name="dishes" class="form-control" rows="5" required placeholder="Salade César ; 12.50 ; Laitue romaine, parmesan, croûtons&#10;Soupe du jour ; 8 ; Préparée avec des légumes frais&#10;Bruschetta ; 9.90"></textarea>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Créer tous</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleBatchDishes(<?= $cat->id ?>)">Annuler</button>
                        </div>
                    </form>
                </div>

                <!-- Formulaire nouveau plat (masqué) -->
                <div id="dishForm-<?= $cat->id ?>" style="display:none;padding:var(--spacing-lg);background:var(--color-bg-warm);">
                    <form method="POST" action="<?= APP_URL ?>?page=save-dish" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="category_id" value="<?= $cat->id ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nom du plat</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Prix (€)</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Description du plat..."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Image (optionnel)</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="form-group" style="display:flex;align-items:center;padding-top:24px;">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="is_active" checked>
                                    <span class="toggle-slider"></span>
                                    <span>Actif</span>
                                </label>
                            </div>
                        </div>
                        <!-- Allergènes -->
                        <div class="form-group">
                            <label>Allergènes</label>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                <?php foreach ($allergenes as $a): ?>
                                <label style="display:inline-flex;align-items:center;gap:4px;font-size:0.8rem;padding:4px 8px;background:var(--color-bg-alt);border:1px solid var(--color-border);border-radius:var(--radius-sm);cursor:pointer;">
                                    <input type="checkbox" name="allergenes[]" value="<?= $a->id ?>">
                                    <i class="fas <?= htmlspecialchars($a->icone) ?>" style="font-size:0.75rem;"></i>
                                    <?= htmlspecialchars($a->nom) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Ajouter</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleDishForm(<?= $cat->id ?>)">Annuler</button>
                        </div>
                    </form>
                </div>

                <!-- Plats de la catégorie -->
                <div class="dishes-list" data-category="<?= $cat->id ?>">
                    <?php $dishes = $dishesByCategory[$cat->id] ?? []; ?>
                    <?php if (empty($dishes)): ?>
                        <div style="padding:var(--spacing-lg);text-align:center;color:var(--color-text-muted);font-size:0.85rem;">
                            Aucun plat dans cette catégorie
                        </div>
                    <?php else: ?>
                        <?php foreach ($dishes as $dish): ?>
                        <div class="dish-item <?= !$dish->is_active ? 'dish-inactive' : '' ?>" data-id="<?= $dish->id ?>">
                            <span class="dish-drag"><i class="fas fa-grip-vertical"></i></span>
                            <?php if ($dish->image): ?>
                                <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($dish->image) ?>" alt="" style="width:50px;height:50px;border-radius:var(--radius-sm);object-fit:cover;">
                            <?php endif; ?>
                            <div class="dish-info">
                                <div class="dish-name">
                                    <?= htmlspecialchars($dish->name) ?>
                                    <?php if (!$dish->is_active): ?>
                                        <span class="badge badge-warning" style="font-size:0.65rem;">Inactif</span>
                                    <?php endif; ?>
                                </div>
                                <div class="dish-desc"><?= htmlspecialchars($dish->description ?? '') ?></div>
                                <?php $da = $allergenesByDish[$dish->id] ?? []; ?>
                                <?php if (!empty($da)): ?>
                                <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:4px;">
                                    <?php foreach ($da as $al): ?>
                                    <span class="allergene-tag"><i class="fas <?= htmlspecialchars($al->icone) ?>"></i> <?= htmlspecialchars($al->nom) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="dish-price"><?= number_format($dish->price, 2, ',', ' ') ?> €</div>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px;" onclick="toggleEditDish(<?= $dish->id ?>)" title="Modifier le plat"><i class="fas fa-pen"></i></button>
                                <form method="POST" action="<?= APP_URL ?>?page=delete-dish" style="display:flex;" onsubmit="return confirm('Supprimer ce plat ?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <input type="hidden" name="dish_id" value="<?= $dish->id ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;" title="Supprimer le plat"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <!-- Formulaire édition plat (masqué) -->
                        <?php $dishAllergenIds = array_map(fn($a) => $a->id, $allergenesByDish[$dish->id] ?? []); ?>
                        <div id="editDishForm-<?= $dish->id ?>" style="display:none;padding:var(--spacing-md);background:var(--color-bg-alt);border-bottom:1px solid var(--color-border);">
                            <form method="POST" action="<?= APP_URL ?>?page=save-dish" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="dish_id" value="<?= $dish->id ?>">
                                <input type="hidden" name="category_id" value="<?= $cat->id ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nom</label>
                                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($dish->name) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Prix (€)</label>
                                        <input type="number" name="price" class="form-control" step="0.01" min="0" required value="<?= $dish->price ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($dish->description ?? '') ?></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Image (optionnel)</label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="form-group" style="display:flex;align-items:center;padding-top:24px;">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="is_active" <?= $dish->is_active ? 'checked' : '' ?>>
                                            <span class="toggle-slider"></span>
                                            <span>Actif</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Allergènes</label>
                                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                        <?php foreach ($allergenes as $a): ?>
                                        <label style="display:inline-flex;align-items:center;gap:4px;font-size:0.8rem;padding:4px 8px;background:var(--color-bg-alt);border:1px solid var(--color-border);border-radius:var(--radius-sm);cursor:pointer;">
                                            <input type="checkbox" name="allergenes[]" value="<?= $a->id ?>" <?= in_array($a->id, $dishAllergenIds) ? 'checked' : '' ?>>
                                            <i class="fas <?= htmlspecialchars($a->icone) ?>" style="font-size:0.75rem;"></i>
                                            <?= htmlspecialchars($a->nom) ?>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div style="display:flex;gap:8px;">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Enregistrer</button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditDish(<?= $dish->id ?>)">Annuler</button>
                                </div>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<!-- ─── Mode Images ───────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-images"></i> Images de la carte</h2>
    </div>

    <form method="POST" action="<?= APP_URL ?>?page=upload-card-image" enctype="multipart/form-data" style="margin-bottom:var(--spacing-lg);">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="upload-area" onclick="this.querySelector('input[type=file]').click()">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Cliquez pour ajouter une image de votre carte</p>
            <input type="file" name="image" accept="image/*" style="display:none;" onchange="this.closest('form').submit()">
        </div>
    </form>

    <?php if (!empty($cardImages)): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:var(--spacing-md);">
        <?php foreach ($cardImages as $img): ?>
        <div style="position:relative;">
            <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($img->filename) ?>" alt="Carte" style="width:100%;border-radius:var(--radius-md);border:1px solid var(--color-border);">
            <form method="POST" action="<?= APP_URL ?>?page=delete-card-image" style="position:absolute;top:8px;right:8px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="image_id" value="<?= $img->id ?>">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- ─── Menus du jour ─────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-day"></i> Menus du jour / Formules</h2>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('newMenuForm').style.display='block'">
            <i class="fas fa-plus"></i> Ajouter
        </button>
    </div>

    <div id="newMenuForm" style="display:none;margin-bottom:var(--spacing-lg);padding:var(--spacing-lg);background:var(--color-bg-alt);border-radius:var(--radius-md);">
        <form method="POST" action="<?= APP_URL ?>?page=save-daily-menu">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="title" class="form-control" required placeholder="Ex: Menu du jour, Formule midi...">
                </div>
                <div class="form-group">
                    <label>Prix (€)</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="14.90">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Description du menu..."></textarea>
            </div>
            <div class="form-group">
                <label>Contenu du menu</label>
                <div id="menuItems">
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <input type="text" name="item_label[]" class="form-control" placeholder="Entrée" style="flex:1;">
                        <input type="text" name="item_value[]" class="form-control" placeholder="Salade César" style="flex:2;">
                    </div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addMenuItem()">
                    <i class="fas fa-plus"></i> Ajouter une ligne
                </button>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Créer</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('newMenuForm').style.display='none'">Annuler</button>
            </div>
        </form>
    </div>

    <?php if (empty($dailyMenus)): ?>
        <div class="empty-state" style="padding:32px;">
            <i class="fas fa-calendar-day"></i>
            <p>Aucun menu du jour configuré</p>
        </div>
    <?php else: ?>
        <?php foreach ($dailyMenus as $menu): ?>
        <?php $menuItems = json_decode($menu->items ?? '[]', true) ?: []; ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--spacing-md);border-bottom:1px solid var(--color-border-light);<?= !$menu->is_active ? 'opacity:0.5;' : '' ?>">
            <div>
                <strong><?= htmlspecialchars($menu->title) ?></strong>
                <?php if ($menu->price): ?>
                    <span style="color:var(--color-primary);font-weight:700;margin-left:8px;"><?= number_format($menu->price, 2, ',', ' ') ?> €</span>
                <?php endif; ?>
                <span class="badge <?= $menu->is_active ? 'badge-success' : 'badge-warning' ?>" style="margin-left:8px;">
                    <?= $menu->is_active ? 'Actif' : 'Inactif' ?>
                </span>
            </div>
            <div style="display:flex;gap:4px;align-items:center;">
                <button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px;" onclick="toggleEditMenu(<?= $menu->id ?>)" title="Modifier la formule"><i class="fas fa-pen"></i></button>
                <form method="POST" action="<?= APP_URL ?>?page=delete-daily-menu" style="display:flex;" onsubmit="return confirm('Supprimer ce menu ?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="menu_id" value="<?= $menu->id ?>">
                    <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;" title="Supprimer la formule"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
        <!-- Formulaire édition menu (masqué) -->
        <div id="editMenuForm-<?= $menu->id ?>" style="display:none;padding:var(--spacing-md);background:var(--color-bg-alt);border-bottom:1px solid var(--color-border);">
            <form method="POST" action="<?= APP_URL ?>?page=save-daily-menu">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="menu_id" value="<?= $menu->id ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($menu->title) ?>">
                    </div>
                    <div class="form-group">
                        <label>Prix (€)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= $menu->price ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($menu->description ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Contenu du menu</label>
                    <div id="menuItems-<?= $menu->id ?>">
                        <?php if (!empty($menuItems)): ?>
                            <?php foreach ($menuItems as $item): ?>
                            <div style="display:flex;gap:8px;margin-bottom:8px;">
                                <input type="text" name="item_label[]" class="form-control" value="<?= htmlspecialchars($item['label'] ?? '') ?>" style="flex:1;">
                                <input type="text" name="item_value[]" class="form-control" value="<?= htmlspecialchars($item['value'] ?? '') ?>" style="flex:2;">
                                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()" style="padding:4px 8px;"><i class="fas fa-times"></i></button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="display:flex;gap:8px;margin-bottom:8px;">
                                <input type="text" name="item_label[]" class="form-control" placeholder="Label" style="flex:1;">
                                <input type="text" name="item_value[]" class="form-control" placeholder="Valeur" style="flex:2;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addMenuItemTo('menuItems-<?= $menu->id ?>')">
                        <i class="fas fa-plus"></i> Ajouter une ligne
                    </button>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Enregistrer</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditMenu(<?= $menu->id ?>)">Annuler</button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function toggleDishForm(catId) {
    const form = document.getElementById('dishForm-' + catId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleEditCategory(catId) {
    const form = document.getElementById('editCategoryForm-' + catId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleBatchDishes(catId) {
    const form = document.getElementById('batchDishForm-' + catId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleEditDish(dishId) {
    const form = document.getElementById('editDishForm-' + dishId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function toggleEditMenu(menuId) {
    const form = document.getElementById('editMenuForm-' + menuId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function addMenuItem() {
    addMenuItemTo('menuItems');
}

function addMenuItemTo(containerId) {
    const container = document.getElementById(containerId);
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
    row.innerHTML = `
        <input type="text" name="item_label[]" class="form-control" placeholder="Label" style="flex:1;">
        <input type="text" name="item_value[]" class="form-control" placeholder="Valeur" style="flex:2;">
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()" style="padding:4px 8px;"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(row);
}

// CSRF token pour les requêtes AJAX
const CSRF_TOKEN = '<?= htmlspecialchars($csrf_token) ?>';

// Drag & drop catégories (réordonner les catégories)
const catList = document.getElementById('categoriesList');
if (catList) {
    new Sortable(catList, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
            const ids = Array.from(evt.target.children)
                .filter(el => el.dataset.id)
                .map(el => parseInt(el.dataset.id));
            fetch('<?= APP_URL ?>?page=reorder-categories', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN},
                body: JSON.stringify({ids: ids})
            }).then(r => r.json()).then(res => {
                if (res.error) showToast(res.error, 'error');
            }).catch(() => showToast('Erreur de connexion', 'error'));
        }
    });
}

// Drag & drop plats (réordonner les plats dans une catégorie)
document.querySelectorAll('.dishes-list').forEach(list => {
    new Sortable(list, {
        handle: '.dish-drag',
        animation: 150,
        onEnd: function(evt) {
            const ids = Array.from(evt.target.children)
                .filter(el => el.dataset.id)
                .map(el => parseInt(el.dataset.id));
            fetch('<?= APP_URL ?>?page=reorder-dishes', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN},
                body: JSON.stringify({ids: ids})
            }).then(r => r.json()).then(res => {
                if (res.error) showToast(res.error, 'error');
            }).catch(() => showToast('Erreur de connexion', 'error'));
        }
    });
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
