<?php
if (!isset($csrf_token)) $csrf_token = '';
if (!isset($flash)) $flash = null;
if (!isset($pageTitle)) $pageTitle = 'MenuCraft';
if (!isset($currentAdmin)) $currentAdmin = null;
if (!isset($pendingReservationsCount)) $pendingReservationsCount = 0;
if (!isset($isDemo)) $isDemo = false;
$currentPage = $_GET['page'] ?? 'dashboard';
$adminRole = $currentAdmin->role ?? 'ADMIN';
?>
<!DOCTYPE html>
<html lang="fr" <?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true') || (isset($_SESSION['darkMode']) && $_SESSION['darkMode']) ? 'class="dark-mode"' : '' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'MenuCraft') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/tour.css">
    <script>if(localStorage.getItem('darkMode')==='true')document.documentElement.classList.add('dark-mode');</script>
</head>
<body>

<?php if ($isDemo): ?>
<div class="demo-banner">
    <i class="fas fa-flask"></i> Mode démonstration — Les modifications ne seront pas conservées
    <a href="<?= APP_URL ?>?page=demo-logout" style="color:#fff;margin-left:16px;text-decoration:underline;">Quitter la démo</a>
</div>
<?php endif; ?>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header-mobile">
            <a href="<?= APP_URL ?>?page=dashboard" class="sidebar-logo">
                <i class="fas fa-utensils"></i> MenuCraft
            </a>
            <button class="sidebar-close-btn" onclick="closeSidebar()" title="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <ul class="sidebar-nav">
            <li><a href="<?= APP_URL ?>?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a></li>

            <div class="nav-label">Restaurant</div>

            <li><a href="<?= APP_URL ?>?page=edit-card" class="<?= $currentPage === 'edit-card' ? 'active' : '' ?>">
                <i class="fas fa-utensils"></i> Carte
            </a></li>
            <li><a href="<?= APP_URL ?>?page=edit-contact" class="<?= $currentPage === 'edit-contact' ? 'active' : '' ?>">
                <i class="fas fa-address-book"></i> Contact
            </a></li>
            <li><a href="<?= APP_URL ?>?page=edit-logo-banner" class="<?= $currentPage === 'edit-logo-banner' ? 'active' : '' ?>">
                <i class="fas fa-image"></i> Logo & Bannière
            </a></li>
            <li><a href="<?= APP_URL ?>?page=edit-services" class="<?= $currentPage === 'edit-services' ? 'active' : '' ?>">
                <i class="fas fa-concierge-bell"></i> Services
            </a></li>
            <li><a href="<?= APP_URL ?>?page=edit-template" class="<?= $currentPage === 'edit-template' ? 'active' : '' ?>">
                <i class="fas fa-palette"></i> Template
            </a></li>

            <div class="nav-divider"></div>
            <div class="nav-label">Premium</div>

            <li><a href="<?= APP_URL ?>?page=reservations" class="<?= $currentPage === 'reservations' ? 'active' : '' ?>" id="navReservations">
                <i class="fas fa-calendar-check"></i> Réservations
                <span class="badge-count" id="pendingBadge" style="<?= ($pendingReservationsCount ?? 0) > 0 ? '' : 'display:none;' ?>"><?= $pendingReservationsCount ?></span>
            </a></li>
            <li><a href="<?= APP_URL ?>?page=stats" class="<?= $currentPage === 'stats' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Statistiques
            </a></li>
            <li><a href="<?= APP_URL ?>?page=floor-plan" class="<?= $currentPage === 'floor-plan' ? 'active' : '' ?>">
                <i class="fas fa-map"></i> Plan de salle
            </a></li>

            <?php if ($adminRole === 'SUPER_ADMIN'): ?>
                <div class="nav-divider"></div>
                <div class="nav-label">Administration</div>

                <li><a href="<?= APP_URL ?>?page=super-dashboard" class="<?= $currentPage === 'super-dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Vue globale
                </a></li>
                <li><a href="<?= APP_URL ?>?page=manage-clients" class="<?= $currentPage === 'manage-clients' ? 'active' : '' ?>">
                    <i class="fas fa-users-cog"></i> Clients
                </a></li>
                <li><a href="<?= APP_URL ?>?page=send-invitation" class="<?= $currentPage === 'send-invitation' ? 'active' : '' ?>">
                    <i class="fas fa-envelope-open-text"></i> Invitations
                </a></li>
                <li><a href="<?= APP_URL ?>?page=login-journal" class="<?= $currentPage === 'login-journal' ? 'active' : '' ?>">
                    <i class="fas fa-shield-alt"></i> Journal connexions
                </a></li>
                <li><a href="<?= APP_URL ?>?page=announcements" class="<?= $currentPage === 'announcements' ? 'active' : '' ?>">
                    <i class="fas fa-bullhorn"></i> Annonces
                </a></li>
                <li><a href="<?= APP_URL ?>?page=feedback-dashboard" class="<?= $currentPage === 'feedback-dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i> Feedbacks
                </a></li>
            <?php endif; ?>

            <div class="nav-divider"></div>

            <li><a href="<?= APP_URL ?>?page=settings" class="<?= $currentPage === 'settings' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Paramètres
            </a></li>
            <li><a href="<?= APP_URL ?>?page=feedback" class="<?= $currentPage === 'feedback' ? 'active' : '' ?>">
                <i class="fas fa-comment-dots"></i> Feedback
            </a></li>
            <li><a href="<?= APP_URL ?>?page=logout" style="color: var(--color-error);">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a></li>
        </ul>
    </aside>

    <!-- Overlay mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?= htmlspecialchars($pageTitle ?? 'MenuCraft') ?></h1>
            </div>
            <div class="topbar-actions">
                <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Mode sombre">
                    <i class="fas fa-moon" id="darkModeIcon"></i>
                </button>
                <span style="font-size:0.85rem;color:var(--color-text-muted);">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
                </span>
            </div>
        </div>

        <?php // Impersonation banner ?>
        <?php if (!empty($_SESSION['impersonating_from'])): ?>
        <div style="background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;padding:10px 20px;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:0.85rem;">
            <span><i class="fas fa-user-secret"></i> Connecté en tant que <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong></span>
            <a href="<?= APP_URL ?>?page=stop-impersonate" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);"><i class="fas fa-sign-out-alt"></i> Revenir à mon compte</a>
        </div>
        <?php endif; ?>

        <?php // Active announcements banner ?>
        <?php
        if (!isset($activeAnnouncements)) {
            try {
                $activeAnnouncements = (isset($pdo) ? $pdo : (isset($this) && isset($this->pdo) ? $this->pdo : null));
                if ($activeAnnouncements instanceof PDO) {
                    $activeAnnouncements = $activeAnnouncements->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();
                } else {
                    $activeAnnouncements = [];
                }
            } catch (Exception $e) {
                $activeAnnouncements = [];
            }
        }
        ?>
        <?php foreach ($activeAnnouncements as $ann): ?>
        <?php
            $annColors = ['info' => ['#3b82f6','rgba(59,130,246,0.08)'], 'warning' => ['#f59e0b','rgba(245,158,11,0.08)'], 'danger' => ['#dc2626','rgba(220,38,38,0.08)']];
            $ac = $annColors[$ann->type] ?? $annColors['info'];
            $annIcon = ['info' => 'info-circle', 'warning' => 'exclamation-triangle', 'danger' => 'exclamation-circle'][$ann->type] ?? 'info-circle';
        ?>
        <div class="announcement-bar" style="background:<?= $ac[1] ?>;border-left:4px solid <?= $ac[0] ?>;padding:10px 16px;border-radius:8px;margin-bottom:8px;display:flex;align-items:center;gap:10px;font-size:0.85rem;">
            <i class="fas fa-<?= $annIcon ?>" style="color:<?= $ac[0] ?>;"></i>
            <span style="flex:1;"><?= htmlspecialchars($ann->message) ?></span>
            <span style="font-size:0.7rem;color:var(--color-text-muted);"><?= date('d/m', strtotime($ann->created_at)) ?></span>
        </div>
        <?php endforeach; ?>

        <?php if (!empty($flash)): ?>
            <?php
                $flashIcons = ['success' => 'check-circle', 'warning' => 'exclamation-triangle', 'error' => 'exclamation-circle', 'info' => 'info-circle'];
                $flashIcon = $flashIcons[$flash['type']] ?? 'info-circle';
            ?>
            <div class="flash-message <?= $flash['type'] ?>">
                <i class="fas fa-<?= $flashIcon ?>"></i>
                <span><?= $flash['message'] ?></span>
            </div>
        <?php endif; ?>
