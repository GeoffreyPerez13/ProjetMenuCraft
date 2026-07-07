<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$adminRole = $currentAdmin->role ?? 'ADMIN';
$isDemo = $isDemo ?? false;
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
        <a href="<?= APP_URL ?>?page=dashboard" class="sidebar-logo">
            <i class="fas fa-utensils"></i> MenuCraft
        </a>

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

            <li><a href="<?= APP_URL ?>?page=reservations" class="<?= $currentPage === 'reservations' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i> Réservations
                <?php if (($pendingReservationsCount ?? 0) > 0): ?>
                    <span class="badge-count"><?= $pendingReservationsCount ?></span>
                <?php endif; ?>
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

                <li><a href="<?= APP_URL ?>?page=manage-clients" class="<?= $currentPage === 'manage-clients' ? 'active' : '' ?>">
                    <i class="fas fa-users-cog"></i> Clients
                </a></li>
                <li><a href="<?= APP_URL ?>?page=send-invitation" class="<?= $currentPage === 'send-invitation' ? 'active' : '' ?>">
                    <i class="fas fa-envelope-open-text"></i> Invitations
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

        <?php if (!empty($flash)): ?>
            <div class="flash-message <?= $flash['type'] ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'warning' ? 'exclamation-triangle' : 'exclamation-circle') ?>"></i>
                <span><?= $flash['message'] ?></span>
            </div>
        <?php endif; ?>
