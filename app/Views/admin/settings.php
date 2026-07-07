<?php require BASE_PATH . '/app/Views/partials/header.php';
$section = $section ?? 'profile';
$adminRole = $admin->role ?? 'ADMIN';
?>

<div style="display:flex;gap:var(--spacing-lg);flex-wrap:wrap;">
    <!-- Sidebar Settings -->
    <div style="width:220px;flex-shrink:0;">
        <div class="card" style="position:sticky;top:80px;">
            <nav style="display:flex;flex-direction:column;gap:4px;">
                <?php
                $sections = [
                    'profile' => ['Profil', 'fa-user'],
                    'password' => ['Mot de passe', 'fa-lock'],
                    'general' => ['Général', 'fa-cog'],
                    'closure-dates' => ['Fermetures', 'fa-calendar-times'],
                ];
                if ($adminRole !== 'SUPER_ADMIN') {
                    $sections['premium'] = ['Premium', 'fa-crown'];
                    $sections['google-reviews'] = ['Avis Google', 'fa-star'];
                    $sections['online-booking'] = ['Réservations', 'fa-calendar-check'];
                    $sections['subscriptions'] = ['Abonnement', 'fa-credit-card'];
                }
                foreach ($sections as $key => [$label, $icon]):
                ?>
                <a href="<?= APP_URL ?>?page=settings&section=<?= $key ?>"
                   style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:var(--radius-sm);font-size:0.85rem;font-weight:500;color:<?= $section === $key ? 'var(--color-primary)' : 'var(--color-text-light)' ?>;background:<?= $section === $key ? 'var(--color-primary-bg)' : 'transparent' ?>;transition:all 0.15s;">
                    <i class="fas <?= $icon ?>" style="width:16px;text-align:center;"></i> <?= $label ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div style="flex:1;min-width:0;">
        <?php if ($section === 'profile'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-user"></i> Profil</h2></div>
            <form method="POST" action="<?= APP_URL ?>?page=update-profile">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin->username) ?>" required minlength="3">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin->email) ?>" required>
                </div>
                <div class="form-group">
                    <label>Nom du restaurant</label>
                    <input type="text" name="restaurant_name" class="form-control" value="<?= htmlspecialchars($admin->restaurant_name ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>

        <?php elseif ($section === 'password'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-lock"></i> Changer le mot de passe</h2></div>
            <form method="POST" action="<?= APP_URL ?>?page=update-password">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label>Confirmer</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                </div>
                <p class="form-hint" style="margin-bottom:16px;">Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial.</p>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Changer le mot de passe</button>
            </form>
        </div>

        <?php elseif ($section === 'general'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-cog"></i> Options générales</h2></div>
            <form method="POST" action="<?= APP_URL ?>?page=update-options">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="section" value="general">

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="site_online" value="0">
                        <input type="checkbox" name="site_online" value="1" <?= ($options['site_online'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Site en ligne</span>
                    </label>
                    <p class="form-hint">Quand désactivé, votre site affiche une page de maintenance.</p>
                </div>

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="email_notifications" value="0">
                        <input type="checkbox" name="email_notifications" value="1" <?= ($options['email_notifications'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Notifications email</span>
                    </label>
                    <p class="form-hint">Recevoir un email lors de nouvelles réservations.</p>
                </div>

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="mail_reminder" value="0">
                        <input type="checkbox" name="mail_reminder" value="1" <?= ($options['mail_reminder'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Rappel mensuel</span>
                    </label>
                    <p class="form-hint">Recevoir un rappel mensuel pour mettre à jour votre carte.</p>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>

        <?php elseif ($section === 'premium'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-crown"></i> Fonctionnalités Premium</h2></div>

            <?php if (defined('BETA_MODE') && BETA_MODE): ?>
            <div class="flash-message success">
                <i class="fas fa-gift"></i>
                <span><strong>Mode Beta actif</strong> — Toutes les fonctionnalités premium sont gratuites jusqu'au <?= date('d/m/Y', strtotime(BETA_EXPIRES)) ?>.</span>
            </div>
            <?php endif; ?>

            <div class="grid grid-2">
                <?php
                $featuresList = [
                    'google_reviews' => ['Avis Google', 'fa-star', '3,99€/mois'],
                    'advanced_analytics' => ['Statistiques avancées', 'fa-chart-line', '3,99€/mois'],
                    'online_booking' => ['Réservations en ligne', 'fa-calendar-check', '10,99€/mois'],
                    'delivery_integration' => ['Intégration livraison', 'fa-truck', '3,99€/mois'],
                ];
                foreach ($featuresList as $fKey => [$fName, $fIcon, $fPrice]):
                    $isActive = false;
                    foreach ($premiumFeatures as $pf) {
                        if ($pf->feature_name === $fKey && $pf->is_active) $isActive = true;
                    }
                ?>
                <div style="padding:var(--spacing-lg);border:1px solid var(--color-border);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="fas <?= $fIcon ?>" style="color:var(--color-primary);"></i>
                            <strong><?= $fName ?></strong>
                        </div>
                        <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:4px;"><?= $fPrice ?></p>
                    </div>
                    <span class="badge <?= $isActive ? 'badge-success' : 'badge-warning' ?>">
                        <?= $isActive ? 'Actif' : 'Inactif' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php elseif ($section === 'online-booking'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-calendar-check"></i> Configuration des réservations</h2></div>
            <form method="POST" action="<?= APP_URL ?>?page=update-options">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="section" value="online-booking">

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="booking_enabled" value="0">
                        <input type="checkbox" name="booking_enabled" value="1" <?= ($options['booking_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Activer les réservations en ligne</span>
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Taille min. du groupe</label>
                        <input type="number" name="booking_min_party" class="form-control" value="<?= htmlspecialchars($options['booking_min_party'] ?? '1') ?>" min="1">
                    </div>
                    <div class="form-group">
                        <label>Taille max. du groupe</label>
                        <input type="number" name="booking_max_party" class="form-control" value="<?= htmlspecialchars($options['booking_max_party'] ?? '20') ?>" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label>Jours de réservation à l'avance</label>
                    <input type="number" name="booking_advance_days" class="form-control" value="<?= htmlspecialchars($options['booking_advance_days'] ?? '30') ?>" min="1">
                </div>

                <div class="form-group">
                    <label>Message personnalisé</label>
                    <textarea name="booking_message" class="form-control" rows="3" placeholder="Message affiché sur le formulaire de réservation..."><?= htmlspecialchars($options['booking_message'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="booking_auto_complete" value="0">
                        <input type="checkbox" name="booking_auto_complete" value="1" <?= ($options['booking_auto_complete'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Marquage automatique des réservations terminées</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>

        <?php elseif ($section === 'subscriptions'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-credit-card"></i> Mon abonnement</h2></div>
            <?php if ($subscription): ?>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--spacing-md);margin-bottom:var(--spacing-lg);">
                <div class="stat-card">
                    <div class="stat-value"><span class="badge <?= $subscription->status === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($subscription->status) ?></span></div>
                    <div class="stat-label">Statut</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= htmlspecialchars($subscription->plan_type) ?></div>
                    <div class="stat-label">Plan</div>
                </div>
            </div>
            <?php if ($subscription->started_at): ?>
            <p style="font-size:0.85rem;color:var(--color-text-muted);">Démarré le <?= date('d/m/Y', strtotime($subscription->started_at)) ?></p>
            <?php endif; ?>
            <?php else: ?>
            <div class="empty-state" style="padding:32px;">
                <i class="fas fa-credit-card"></i>
                <h3>Aucun abonnement</h3>
                <p>Souscrivez à un abonnement pour activer votre site.</p>
            </div>
            <?php endif; ?>
        </div>

        <?php elseif ($section === 'google-reviews'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-star"></i> Avis Google</h2></div>
            <form method="POST" action="<?= APP_URL ?>?page=update-options">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="section" value="google-reviews">
                <div class="form-group">
                    <label>Google Place ID</label>
                    <input type="text" name="google_place_id" class="form-control" value="<?= htmlspecialchars($options['google_place_id'] ?? '') ?>" placeholder="ChIJ...">
                    <p class="form-hint">Trouvez votre Place ID sur <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Google</a></p>
                </div>
                <div class="form-group">
                    <label>Clé API Google</label>
                    <input type="text" name="google_api_key" class="form-control" value="<?= htmlspecialchars($options['google_api_key'] ?? '') ?>" placeholder="AIza...">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>

        <?php else: ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-calendar-times"></i> Dates de fermeture</h2></div>
            <form method="POST" action="<?= APP_URL ?>?page=update-options">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="section" value="closure-dates">
                <div class="form-group">
                    <label>Dates de fermeture exceptionnelle (JSON)</label>
                    <textarea name="closure_dates" class="form-control" rows="4" placeholder='["2026-12-25","2026-01-01"]'><?= htmlspecialchars($options['closure_dates'] ?? '[]') ?></textarea>
                    <p class="form-hint">Format JSON : tableau de dates au format YYYY-MM-DD</p>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
