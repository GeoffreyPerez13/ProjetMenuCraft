<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="grid grid-2">
    <!-- Logo -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-image"></i> Logo</h2>
        </div>

        <?php if ($logo): ?>
            <div style="text-align:center;margin-bottom:var(--spacing-lg);">
                <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($logo->filename) ?>" alt="Logo" class="preview-image">
            </div>
            <form method="POST" action="<?= APP_URL ?>?page=delete-logo" style="text-align:center;margin-bottom:var(--spacing-lg);">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer le logo ?')">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </form>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>?page=upload-logo" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="upload-area" onclick="this.querySelector('input[type=file]').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p><?= $logo ? 'Remplacer le logo' : 'Cliquez pour uploader votre logo' ?></p>
                <p class="form-hint">JPG, PNG, WebP — Max 5 Mo</p>
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="this.closest('form').submit()">
            </div>
        </form>
    </div>

    <!-- Bannière -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-panorama"></i> Bannière</h2>
        </div>

        <?php if ($banner): ?>
            <div style="text-align:center;margin-bottom:var(--spacing-lg);">
                <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($banner->filename) ?>" alt="Bannière" style="max-width:100%;border-radius:var(--radius-md);border:1px solid var(--color-border);">
            </div>
            <form method="POST" action="<?= APP_URL ?>?page=delete-banner" style="text-align:center;margin-bottom:var(--spacing-lg);">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer la bannière ?')">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </form>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>?page=upload-banner" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="upload-area" onclick="this.querySelector('input[type=file]').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p><?= $banner ? 'Remplacer la bannière' : 'Cliquez pour uploader une bannière' ?></p>
                <p class="form-hint">JPG, PNG, WebP — Max 5 Mo — Ratio 16:9 recommandé</p>
                <input type="file" name="banner" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="this.closest('form').submit()">
            </div>
        </form>

        <!-- Texte de bannière -->
        <form method="POST" action="<?= APP_URL ?>?page=save-banner-text" style="margin-top:var(--spacing-lg);">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <label><i class="fas fa-pen"></i> Texte de la bannière</label>
                <input type="text" name="banner_text" class="form-control"
                       value="<?= htmlspecialchars($banner->text ?? '') ?>" placeholder="Bienvenue dans notre restaurant">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Enregistrer le texte
            </button>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
