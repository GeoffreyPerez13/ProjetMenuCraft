<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php
if (!isset($section)) $section = 'profile';
if (!isset($admin)) $admin = null;
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($options)) $options = [];
if (!isset($premiumFeatures)) $premiumFeatures = [];
$adminRole = ($admin->role ?? 'ADMIN');
?>

<style>
/* ─── Settings: Sidebar nav improvements ─── */
.settings-sidebar nav a {
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s, color 0.15s;
}
.settings-sidebar nav a:hover {
    background: var(--color-bg-alt) !important;
}

/* ─── Settings: Form sections ─── */
.settings-content .card {
    overflow: hidden;
}
.settings-content .form-group {
    margin-bottom: var(--spacing-md);
}
.settings-content .toggle-switch {
    cursor: pointer;
}

/* ─── Password toggle button ─── */
.pwd-toggle {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--color-text-muted);
    cursor: pointer;
    padding: 4px 6px;
    font-size: 0.9rem;
    border-radius: var(--radius-sm);
    transition: color 0.15s;
}
.pwd-toggle:hover {
    color: var(--color-primary);
}

/* ─── Settings: Premium grid ─── */
.settings-premium-card {
    padding: var(--spacing-lg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    transition: box-shadow 0.2s, transform 0.2s;
}
.settings-premium-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transform: translateY(-1px);
}

/* ─── Settings: Subscription stats ─── */
.settings-sub-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

/* ─── Responsive: Tablet ─── */
@media (max-width: 1024px) {
    .settings-sidebar {
        width: 200px;
    }
    .settings-sidebar nav a {
        padding: 8px 12px !important;
        font-size: 0.82rem !important;
    }
}

/* ─── Responsive: Tablet portrait / small ─── */
@media (max-width: 768px) {
    .settings-sidebar nav {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 4px;
    }
    .settings-sidebar nav::-webkit-scrollbar {
        display: none;
    }
    .settings-sidebar nav a {
        padding: 8px 14px !important;
        font-size: 0.8rem !important;
        border-radius: var(--radius-full) !important;
        border: 1px solid var(--color-border);
        flex-shrink: 0;
    }

    .settings-content .grid-2 {
        grid-template-columns: 1fr !important;
    }
    .settings-sub-grid {
        grid-template-columns: 1fr;
    }
    .settings-premium-card {
        padding: var(--spacing-md);
    }
}

/* ─── Responsive: Mobile ─── */
@media (max-width: 600px) {
    .settings-sidebar nav a {
        padding: 7px 12px !important;
        font-size: 0.75rem !important;
        gap: 6px !important;
    }
    .settings-sidebar nav a i {
        font-size: 0.7rem;
    }

    .settings-content .card {
        padding: var(--spacing-md);
    }
    .settings-content .card-header h2 {
        font-size: 1rem;
    }

    .settings-content .form-row {
        grid-template-columns: 1fr !important;
        gap: 0 !important;
    }

    .settings-content .form-control {
        font-size: 0.85rem;
    }

    .settings-content .toggle-switch {
        font-size: 0.84rem;
    }
    .settings-content .toggle-switch span:last-child {
        line-height: 1.3;
    }

    .settings-content .form-hint {
        font-size: 0.75rem;
    }

    .settings-premium-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: var(--spacing-md);
    }
    .settings-premium-card .badge {
        align-self: flex-end;
    }

    .settings-content .btn {
        width: 100%;
        justify-content: center;
    }

    #closureDatesList {
        gap: 6px !important;
    }
    #closureDatesList > span {
        font-size: 0.78rem !important;
        padding: 5px 10px !important;
    }
}

/* ─── Responsive: Small mobile ─── */
@media (max-width: 420px) {
    .settings-sidebar nav a {
        padding: 6px 10px !important;
        font-size: 0.72rem !important;
    }

    .settings-content .card {
        padding: 12px;
    }
    .settings-content .card-header {
        margin: -12px -12px 12px !important;
        padding: 12px !important;
    }
    .settings-content .card-header h2 {
        font-size: 0.92rem;
    }

    .settings-content .form-control {
        padding: 8px 10px;
        font-size: 0.82rem;
    }
    .settings-content textarea.form-control {
        min-height: 80px;
    }

    .settings-sub-grid .stat-card {
        padding: 12px;
    }
    .settings-sub-grid .stat-value {
        font-size: 1.1rem;
    }

    .settings-content .btn {
        padding: 10px 16px;
        font-size: 0.82rem;
    }
}

/* ─── Responsive: Very small ─── */
@media (max-width: 340px) {
    .settings-sidebar nav a span,
    .settings-sidebar nav a:not([class]) {
        font-size: 0.68rem !important;
    }
    .settings-sidebar nav a i {
        display: none !important;
    }

    .settings-content .card-header h2 {
        font-size: 0.85rem;
    }
    .settings-content .card-header h2 i {
        display: none;
    }
}

/* ─── Touch devices ─── */
@media (hover: none) {
    .settings-sidebar nav a {
        min-height: 44px;
        display: flex !important;
        align-items: center !important;
    }
    .settings-content .form-control {
        min-height: 44px;
    }
    .settings-content .btn {
        min-height: 44px;
    }
    .settings-content .toggle-switch {
        min-height: 44px;
        display: flex !important;
        align-items: center !important;
    }
}
</style>

<div class="settings-layout">
    <!-- Sidebar Settings -->
    <div class="settings-sidebar">
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
    <div class="settings-content">
        <?php if ($section === 'profile'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-user"></i> Profil</h2></div>
            <form method="POST" action="<?= APP_URL ?>?page=update-profile">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin->username ?? '') ?>" required minlength="3">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin->email ?? '') ?>" required>
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
                    <div style="position:relative;">
                        <input type="password" name="current_password" class="form-control" required style="padding-right:40px;">
                        <button type="button" class="pwd-toggle" onclick="togglePwdVisibility(this)" title="Afficher/Masquer"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password" class="form-control" required minlength="8" style="padding-right:40px;">
                            <button type="button" class="pwd-toggle" onclick="togglePwdVisibility(this)" title="Afficher/Masquer"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirmer</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password_confirmation" class="form-control" required style="padding-right:40px;">
                            <button type="button" class="pwd-toggle" onclick="togglePwdVisibility(this)" title="Afficher/Masquer"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>
                <p class="form-hint" style="margin-bottom:16px;">Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial.</p>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Changer le mot de passe</button>
            </form>
            <script>
            function togglePwdVisibility(btn) {
                const input = btn.parentElement.querySelector('input');
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            }
            </script>
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

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="hide_tour_button" value="0">
                        <input type="checkbox" name="hide_tour_button" value="1" <?= ($options['hide_tour_button'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Masquer le bouton du guide interactif</span>
                    </label>
                    <p class="form-hint">Masquer le bouton d'aide (Tour JS) sur toutes les pages de l'administration.</p>
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
                <div class="settings-premium-card">
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

                <hr style="margin:24px 0;border:none;border-top:1px solid var(--color-border);">

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="booking_daily_limit_enabled" value="0">
                        <input type="checkbox" name="booking_daily_limit_enabled" value="1" <?= ($options['booking_daily_limit_enabled'] ?? '0') === '1' ? 'checked' : '' ?> onchange="document.getElementById('dailyLimitCount').style.display=this.checked?'block':'none'">
                        <span class="toggle-slider"></span>
                        <span>Limiter le nombre de réservations par jour</span>
                    </label>
                </div>

                <div id="dailyLimitCount" style="display:<?= ($options['booking_daily_limit_enabled'] ?? '0') === '1' ? 'block' : 'none' ?>;margin-bottom:var(--spacing-md);">
                    <div class="form-group">
                        <label>Nombre max. de réservations / jour</label>
                        <input type="number" name="booking_daily_limit" class="form-control" value="<?= htmlspecialchars($options['booking_daily_limit'] ?? '20') ?>" min="1" max="500" style="max-width:200px;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="booking_require_phone" value="0">
                        <input type="checkbox" name="booking_require_phone" value="1" <?= ($options['booking_require_phone'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Téléphone obligatoire</span>
                    </label>
                    <p class="form-hint">Le client doit fournir un numéro de téléphone pour réserver.</p>
                </div>

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="booking_require_email" value="0">
                        <input type="checkbox" name="booking_require_email" value="1" <?= ($options['booking_require_email'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Email obligatoire</span>
                    </label>
                    <p class="form-hint">Le client doit fournir un email pour réserver.</p>
                </div>

                <div class="form-group">
                    <label>Délai minimum avant réservation (heures)</label>
                    <input type="number" name="booking_min_hours_before" class="form-control" value="<?= htmlspecialchars($options['booking_min_hours_before'] ?? '2') ?>" min="0" max="72" style="max-width:200px;">
                    <p class="form-hint">Empêche les réservations de dernière minute (ex: 2h = pas de réservation moins de 2h avant).</p>
                </div>

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="booking_confirmation_email" value="0">
                        <input type="checkbox" name="booking_confirmation_email" value="1" <?= ($options['booking_confirmation_email'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Email de confirmation au client</span>
                    </label>
                    <p class="form-hint">Envoyer un email de confirmation automatique au client après réservation.</p>
                </div>

                <hr style="margin:24px 0;border:none;border-top:1px solid var(--color-border);">

                <div class="form-group">
                    <label class="toggle-switch">
                        <input type="hidden" name="booking_auto_confirm" value="0">
                        <input type="checkbox" name="booking_auto_confirm" value="1" <?= ($options['booking_auto_confirm'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Confirmation automatique des réservations</span>
                    </label>
                    <p class="form-hint">Les réservations sont confirmées immédiatement. Le client reçoit directement l'email de confirmation (pas d'email "en attente").</p>
                </div>

                <hr style="margin:24px 0;border:none;border-top:1px solid var(--color-border);">

                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Créneaux horaires disponibles</label>
                    <p class="form-hint" style="margin-bottom:8px;">Définissez les heures auxquelles les clients peuvent réserver. Un créneau par ligne (format HH:MM).</p>
                    <textarea name="booking_time_slots" class="form-control" rows="6" placeholder="12:00&#10;12:30&#10;13:00&#10;19:00&#10;19:30&#10;20:00&#10;20:30&#10;21:00"><?= htmlspecialchars($options['booking_time_slots'] ?? '') ?></textarea>
                    <p class="form-hint" style="margin-top:6px;">Laissez vide pour permettre au client de choisir n'importe quelle heure.</p>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>

        <?php elseif ($section === 'subscriptions'): ?>
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-credit-card"></i> Mon abonnement</h2></div>
            <?php if ($subscription): ?>
            <div class="settings-sub-grid">
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
            <form method="POST" action="<?= APP_URL ?>?page=update-options" id="closureDatesForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="section" value="closure-dates">
                <input type="hidden" name="closure_dates" id="closureDatesHidden" value="<?= htmlspecialchars($options['closure_dates'] ?? '[]') ?>">

                <div class="form-group">
                    <label>Ajouter une date de fermeture</label>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="date" id="closureDateInput" class="form-control" style="max-width:200px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="addClosureDate()"><i class="fas fa-plus"></i> Ajouter</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dates enregistrées</label>
                    <div id="closureDatesList" style="display:flex;flex-wrap:wrap;gap:8px;min-height:36px;"></div>
                    <p class="form-hint" style="margin-top:8px;">Cliquez sur la croix pour retirer une date</p>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>

            <script>
            (function() {
                let dates = [];
                try { dates = JSON.parse(document.getElementById('closureDatesHidden').value); } catch(e) { dates = []; }
                if (!Array.isArray(dates)) dates = [];

                function toDisplay(isoDate) {
                    const parts = isoDate.split('-');
                    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
                    return isoDate;
                }

                function render() {
                    const list = document.getElementById('closureDatesList');
                    dates.sort();
                    list.innerHTML = dates.length === 0
                        ? '<span style="color:var(--color-text-muted);font-size:0.85rem;">Aucune date de fermeture</span>'
                        : dates.map((d, i) => `<span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:var(--color-bg-alt);border:1px solid var(--color-border);border-radius:var(--radius-full);font-size:0.85rem;font-weight:500;">
                            <i class="fas fa-calendar-day" style="color:var(--color-primary);font-size:0.75rem;"></i>
                            ${toDisplay(d)}
                            <button type="button" onclick="removeClosureDate(${i})" style="background:none;border:none;color:var(--color-danger);cursor:pointer;font-size:0.8rem;padding:0 2px;" title="Retirer"><i class="fas fa-times"></i></button>
                        </span>`).join('');
                    document.getElementById('closureDatesHidden').value = JSON.stringify(dates);
                }

                window.addClosureDate = function() {
                    const input = document.getElementById('closureDateInput');
                    const val = input.value;
                    if (!val) return;
                    if (dates.includes(val)) { alert('Cette date est déjà ajoutée.'); return; }
                    dates.push(val);
                    input.value = '';
                    render();
                };

                window.removeClosureDate = function(idx) {
                    dates.splice(idx, 1);
                    render();
                };

                render();
            })();
            </script>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
