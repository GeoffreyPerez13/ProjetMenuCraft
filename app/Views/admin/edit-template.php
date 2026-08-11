<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($currentPalette)) $currentPalette = 'classic';
if (!isset($currentLayout)) $currentLayout = 'standard';
if (!isset($slug)) $slug = '';
$palettes = [
    'classic' => ['Classique', 'Tons chauds, ambre/brun', '#b45309', '#fef7ed'],
    'modern' => ['Moderne', 'Minimaliste bleu', '#2563eb', '#eff6ff'],
    'elegant' => ['Élégant', 'Sombre + dorés', '#d4a853', '#12121f'],
    'nature' => ['Nature', 'Vert naturel', '#16a34a', '#f0fdf4'],
    'rose' => ['Rosé', 'Pastel féminin', '#db2777', '#fdf2f8'],
    'bistro' => ['Bistro', 'Rouge traditionnel', '#dc2626', '#fef2f2'],
    'ocean' => ['Océan', 'Bleu marin', '#0891b2', '#ecfeff'],
];
$customPrimary = $options['custom_primary'] ?? '#b45309';
$customBg = $options['custom_bg'] ?? '#ffffff';
$customFont = $options['custom_font'] ?? 'Inter';
$layouts = [
    'standard' => ['Standard', 'Disposition verticale classique', 'align-left'],
    'bistro' => ['Bistro', 'Accent sur les photos', 'images'],
    'ocean' => ['Océan', 'Visuels aquatiques', 'water'],
    'elegant' => ['Élégant', 'Centré, aéré et raffiné', 'feather'],
    'magazine' => ['Magazine', 'Style éditorial moderne', 'newspaper'],
];
$slug = $restaurant->slug ?? '';
$siteUrl = $slug ? APP_URL . '?page=display&slug=' . urlencode($slug) : '';
?>

<?php if ($slug): ?>
<div style="margin-bottom:var(--spacing-lg);display:flex;justify-content:flex-end;">
    <a href="<?= $siteUrl ?>" target="_blank" class="btn btn-outline btn-sm">
        <i class="fas fa-external-link-alt"></i> Voir le site
    </a>
</div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>?page=update-template">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <!-- Palettes -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-palette"></i> Palette de couleurs</h2>
        </div>
        <div class="template-palettes-grid">
            <?php foreach ($palettes as $key => [$name, $desc, $primary, $bg]): ?>
            <label style="cursor:pointer;border:2px solid <?= $currentPalette === $key ? $primary : 'var(--color-border)' ?>;border-radius:var(--radius-md);overflow:hidden;transition:all 0.2s;">
                <input type="radio" name="site_palette" value="<?= $key ?>" <?= $currentPalette === $key ? 'checked' : '' ?> style="display:none;">
                <div style="height:60px;background:<?= $bg ?>;position:relative;">
                    <div style="position:absolute;bottom:8px;left:8px;right:8px;display:flex;gap:4px;">
                        <div style="width:20px;height:20px;border-radius:50%;background:<?= $primary ?>;border:2px solid white;"></div>
                    </div>
                </div>
                <div style="padding:10px 12px;">
                    <strong style="font-size:0.85rem;"><?= $name ?></strong>
                    <p style="font-size:0.7rem;color:var(--color-text-muted);margin-top:2px;"><?= $desc ?></p>
                </div>
                <?php if ($currentPalette === $key): ?>
                <div class="selected-badge" style="background:<?= $primary ?>;color:white;text-align:center;padding:4px;font-size:0.7rem;font-weight:600;">
                    <i class="fas fa-check"></i> Sélectionné
                </div>
                <?php endif; ?>
            </label>
            <?php endforeach; ?>

            <!-- Custom palette option -->
            <label style="cursor:pointer;border:2px solid <?= $currentPalette === 'custom' ? $customPrimary : 'var(--color-border)' ?>;border-radius:var(--radius-md);overflow:hidden;transition:all 0.2s;">
                <input type="radio" name="site_palette" value="custom" <?= $currentPalette === 'custom' ? 'checked' : '' ?> style="display:none;" onchange="document.getElementById('customPalettePanel').style.display='block'">
                <div style="height:60px;background:linear-gradient(135deg, <?= $customPrimary ?>, <?= $customBg ?>);position:relative;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-paintbrush" style="font-size:1.4rem;color:white;text-shadow:0 1px 3px rgba(0,0,0,0.3);"></i>
                </div>
                <div style="padding:10px 12px;">
                    <strong style="font-size:0.85rem;">Personnalisé</strong>
                    <p style="font-size:0.7rem;color:var(--color-text-muted);margin-top:2px;">Choisissez vos propres couleurs</p>
                </div>
                <?php if ($currentPalette === 'custom'): ?>
                <div class="selected-badge" style="background:<?= $customPrimary ?>;color:white;text-align:center;padding:4px;font-size:0.7rem;font-weight:600;">
                    <i class="fas fa-check"></i> Sélectionné
                </div>
                <?php endif; ?>
            </label>
        </div>

        <!-- Custom palette color pickers -->
        <div id="customPalettePanel" style="display:<?= $currentPalette === 'custom' ? 'block' : 'none' ?>;margin-top:var(--spacing-md);padding:var(--spacing-md);background:var(--color-bg-alt);border-radius:var(--radius-md);border:1px solid var(--color-border);">
            <p style="font-size:0.82rem;font-weight:600;margin-bottom:12px;"><i class="fas fa-sliders-h"></i> Couleurs personnalisées</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:400px;">
                <div class="form-group">
                    <label style="font-size:0.78rem;">Couleur principale</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="color" name="custom_primary" value="<?= htmlspecialchars($customPrimary) ?>" style="width:40px;height:40px;border:none;border-radius:var(--radius-sm);cursor:pointer;padding:0;">
                        <input type="text" name="custom_primary_hex" value="<?= htmlspecialchars($customPrimary) ?>" class="form-control" style="font-size:0.8rem;max-width:100px;" oninput="this.previousElementSibling.value=this.value" onchange="this.previousElementSibling.value=this.value">
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-size:0.78rem;">Couleur de fond</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="color" name="custom_bg" value="<?= htmlspecialchars($customBg) ?>" style="width:40px;height:40px;border:none;border-radius:var(--radius-sm);cursor:pointer;padding:0;">
                        <input type="text" name="custom_bg_hex" value="<?= htmlspecialchars($customBg) ?>" class="form-control" style="font-size:0.8rem;max-width:100px;" oninput="this.previousElementSibling.value=this.value" onchange="this.previousElementSibling.value=this.value">
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label style="font-size:0.78rem;"><i class="fas fa-font"></i> Police d'écriture</label>
                <select name="custom_font" class="form-control" style="max-width:250px;" id="customFontSelect">
                    <?php
                    $fonts = ['Inter', 'Playfair Display', 'Roboto', 'Lora', 'Montserrat', 'Open Sans', 'Raleway', 'Poppins', 'Merriweather', 'Oswald', 'Nunito', 'Cormorant Garamond'];
                    foreach ($fonts as $f): ?>
                    <option value="<?= $f ?>" <?= $customFont === $f ? 'selected' : '' ?> style="font-family:'<?= $f ?>'"><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Layouts -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-columns"></i> Disposition du site</h2>
        </div>
        <div class="template-layouts-grid">
            <?php foreach ($layouts as $key => [$name, $desc, $icon]): ?>
            <label style="cursor:pointer;border:2px solid <?= $currentLayout === $key ? 'var(--color-primary)' : 'var(--color-border)' ?>;border-radius:var(--radius-md);padding:var(--spacing-lg);text-align:center;transition:all 0.2s;">
                <input type="radio" name="site_layout" value="<?= $key ?>" <?= $currentLayout === $key ? 'checked' : '' ?> style="display:none;">
                <i class="fas fa-<?= $icon ?>" style="font-size:2rem;color:var(--color-primary);margin-bottom:8px;display:block;"></i>
                <strong style="display:block;font-size:0.9rem;"><?= $name ?></strong>
                <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:4px;"><?= $desc ?></p>
                <?php if ($currentLayout === $key): ?>
                <span class="selected-badge badge badge-primary" style="margin-top:8px;">Actif</span>
                <?php endif; ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;align-items:center;">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Enregistrer le template
        </button>
    </div>
</form>

<script>
document.querySelectorAll('input[name="site_palette"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const palettes = <?= json_encode($palettes) ?>;
        const customPanel = document.getElementById('customPalettePanel');
        document.querySelectorAll('input[name="site_palette"]').forEach(r => {
            const label = r.closest('label');
            const key = r.value;
            let primary = palettes[key] ? palettes[key][2] : 'var(--color-border)';
            if (key === 'custom') primary = document.querySelector('input[name="custom_primary"]').value;
            if (r.checked) {
                label.style.borderColor = primary;
                if (!label.querySelector('.selected-badge')) {
                    const badge = document.createElement('div');
                    badge.className = 'selected-badge';
                    badge.style.cssText = 'background:' + primary + ';color:white;text-align:center;padding:4px;font-size:0.7rem;font-weight:600;';
                    badge.innerHTML = '<i class="fas fa-check"></i> Sélectionné';
                    label.appendChild(badge);
                }
            } else {
                label.style.borderColor = 'var(--color-border)';
                const badge = label.querySelector('.selected-badge');
                if (badge) badge.remove();
            }
        });
        customPanel.style.display = this.value === 'custom' ? 'block' : 'none';
    });
});

// Sync color picker with hex input
document.querySelectorAll('input[type="color"]').forEach(picker => {
    picker.addEventListener('input', function() {
        const hex = this.nextElementSibling;
        if (hex) hex.value = this.value;
    });
});

document.querySelectorAll('input[name="site_layout"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="site_layout"]').forEach(r => {
            const label = r.closest('label');
            if (r.checked) {
                label.style.borderColor = 'var(--color-primary)';
                if (!label.querySelector('.selected-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'selected-badge badge badge-primary';
                    badge.style.marginTop = '8px';
                    badge.textContent = 'Actif';
                    label.appendChild(badge);
                }
            } else {
                label.style.borderColor = 'var(--color-border)';
                const badge = label.querySelector('.selected-badge');
                if (badge) badge.remove();
            }
        });
    });
});
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
