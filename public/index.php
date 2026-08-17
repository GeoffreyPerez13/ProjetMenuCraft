<?php
/**
 * MenuCraft — Routeur principal
 * Point d'entrée unique de l'application
 */

// Erreurs : logger mais ne pas afficher
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Session sécurisée
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Configuration
require_once __DIR__ . '/../config.php';

// Normaliser SITE_URL avec trailing slash
$siteUrl = rtrim(SITE_URL, '/') . '/';
if (!defined('APP_URL')) {
    define('APP_URL', $siteUrl);
}

// Autoload des classes
$classDirectories = [
    BASE_PATH . '/app/Controllers/',
    BASE_PATH . '/app/Models/',
    BASE_PATH . '/app/Helpers/',
    BASE_PATH . '/app/Services/',
];

spl_autoload_register(function ($class) use ($classDirectories) {
    foreach ($classDirectories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ─── Routage ────────────────────────────────────────────
$page = $_GET['page'] ?? 'landing';

// Instancier les contrôleurs
$adminCtrl = new AdminController($pdo);
$cardCtrl = new CardController($pdo);
$contactCtrl = new ContactController($pdo);
$logoBannerCtrl = new LogoBannerController($pdo);
$servicesCtrl = new ServicesController($pdo);
$settingsCtrl = new SettingsController($pdo);
$displayCtrl = new DisplayController($pdo);
$statsCtrl = new StatsController($pdo);
$reservationCtrl = new ReservationController($pdo);
$floorPlanCtrl = new FloorPlanController($pdo);
$legalCtrl = new LegalController($pdo);
$stripeCtrl = new StripeController($pdo);
$feedbackCtrl = new FeedbackController($pdo);
$clientMgmtCtrl = new ClientManagementController($pdo);
$sitemapCtrl = new SitemapController($pdo);
$notifCtrl = new NotificationStreamController($pdo);

switch ($page) {

    // ─── Public ─────────────────────────────────────────
    case 'landing':
    case '':
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $csrf_token = $_SESSION['csrf_token'] ?? '';
        if (empty($csrf_token)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $csrf_token = $_SESSION['csrf_token'];
        }
        require BASE_PATH . '/app/Views/landing.php';
        break;

    case 'login':
        $adminCtrl->login();
        break;

    case 'auto-register':
        // Registration disabled
        header('Location: ' . $siteUrl . '?page=login');
        exit;

    case 'register':
        $adminCtrl->register();
        break;

    case 'verify-email':
        $adminCtrl->verifyEmail();
        break;

    case 'reset-password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmt = $pdo->prepare(
                    'INSERT INTO password_resets (email, token, expires_at) VALUES (:e, :t, :exp)'
                );
                $stmt->execute([':e' => $email, ':t' => $token, ':exp' => $expires]);

                $resetUrl = SITE_URL . '?page=reset-password-admin&token=' . $token;
                $mailer = new Mailer();
                $mailer->send($email, 'Réinitialisation de mot de passe — MenuCraft',
                    '<h2>Réinitialisation de mot de passe</h2>
                    <p>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe :</p>
                    <p><a href="' . $resetUrl . '" style="background:#b45309;color:#fff;padding:14px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:600;">Réinitialiser mon mot de passe</a></p>
                    <p style="color:#a8a29e;font-size:13px;">Ce lien expire dans 1 heure.</p>'
                );
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.'];
            header('Location: ' . $siteUrl . '?page=login');
            exit;
        }
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $csrf_token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;
        require BASE_PATH . '/app/Views/admin/reset-password.php';
        break;

    case 'reset-password-admin':
        $token = $_GET['token'] ?? '';
        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = :t AND used = 0 AND expires_at > NOW() LIMIT 1');
        $stmt->execute([':t' => $token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Lien invalide ou expiré.'];
            header('Location: ' . $siteUrl . '?page=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirmation'] ?? '';

            if ($password !== $confirm) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Les mots de passe ne correspondent pas.'];
                header('Location: ' . $siteUrl . '?page=reset-password-admin&token=' . $token);
                exit;
            }

            $pwdErrors = Validator::validatePassword($password);
            if (!empty($pwdErrors)) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => implode('<br>', $pwdErrors)];
                header('Location: ' . $siteUrl . '?page=reset-password-admin&token=' . $token);
                exit;
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare('UPDATE admins SET password = :p WHERE email = :e')
                ->execute([':p' => $hash, ':e' => $reset->email]);
            $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = :id')
                ->execute([':id' => $reset->id]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Mot de passe réinitialisé avec succès !'];
            header('Location: ' . $siteUrl . '?page=login');
            exit;
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $csrf_token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;
        require BASE_PATH . '/app/Views/admin/reset-password-admin.php';
        break;

    case 'display':
        $displayCtrl->show();
        break;

    case 'public-booking':
        $reservationCtrl->publicBooking();
        break;

    case 'legal':
        $legalCtrl->show();
        break;

    case 'sitemap.xml':
        $sitemapCtrl->generate();
        break;

    case 'stripe-webhook':
        $stripeCtrl->handleWebhook();
        break;

    // ─── Démo ───────────────────────────────────────────
    case 'demo':
        $token = $_GET['token'] ?? '';
        $demoModel = new DemoToken($pdo);
        $demoModel->cleanExpired();

        if (!$demoModel->isValid($token)) {
            $flash = ['type' => 'error', 'message' => 'Lien de démonstration invalide ou expiré.'];
            require BASE_PATH . '/app/Views/errors/demo-expired.php';
            break;
        }

        $demo = $demoModel->findByToken($token);
        session_regenerate_id(true);
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = $demo->admin_id;
        $_SESSION['demo_mode'] = true;
        $_SESSION['demo_token'] = $token;
        $_SESSION['demo_expires_at'] = $demo->expires_at;

        $admin = (new Admin($pdo))->findById($demo->admin_id);
        if ($admin) {
            $_SESSION['admin_name'] = $admin->restaurant_name;
            $_SESSION['username'] = $admin->username;
            $rest = (new Restaurant($pdo))->findById($admin->restaurant_id);
            if ($rest) $_SESSION['demo_slug'] = $rest->slug;
        }

        header('Location: ' . $siteUrl . '?page=dashboard');
        exit;

    case 'demo-logout':
        if (!empty($_SESSION['demo_token'])) {
            $demoModel = new DemoToken($pdo);
            $demo = $demoModel->findByToken($_SESSION['demo_token']);
            if ($demo) $demoModel->cleanClone($demo);
        }
        session_destroy();
        session_start();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Session de démonstration terminée.'];
        header('Location: ' . $siteUrl . '?page=landing');
        exit;

    // ─── Auth requise ───────────────────────────────────
    case 'logout':
        $adminCtrl->logout();
        break;

    case 'dashboard':
        $adminCtrl->dashboard();
        break;

    case 'edit-card':
        $cardCtrl->show();
        break;

    case 'save-category':
        $cardCtrl->saveCategory();
        break;

    case 'batch-categories':
        $cardCtrl->batchCategories();
        break;

    case 'batch-dishes':
        $cardCtrl->batchDishes();
        break;

    case 'delete-category':
        $cardCtrl->deleteCategory();
        break;

    case 'save-dish':
        $cardCtrl->saveDish();
        break;

    case 'delete-dish':
        $cardCtrl->deleteDish();
        break;

    case 'upload-card-image':
        $cardCtrl->uploadImage();
        break;

    case 'delete-card-image':
        $cardCtrl->deleteImage();
        break;

    case 'reorder-categories':
        $cardCtrl->reorderCategories();
        break;

    case 'reorder-dishes':
        $cardCtrl->reorderDishes();
        break;

    case 'view-card':
        $cardCtrl->viewCard();
        break;

    case 'save-daily-menu':
        $cardCtrl->saveDailyMenu();
        break;

    case 'delete-daily-menu':
        $cardCtrl->deleteDailyMenu();
        break;

    case 'toggle-daily-menu':
        $cardCtrl->toggleDailyMenu();
        break;

    case 'reorder-daily-menus':
        $cardCtrl->reorderDailyMenus();
        break;

    case 'edit-contact':
        $contactCtrl->edit();
        break;

    case 'edit-logo-banner':
        $logoBannerCtrl->show();
        break;

    case 'upload-logo':
        $logoBannerCtrl->uploadLogo();
        break;

    case 'delete-logo':
        $logoBannerCtrl->deleteLogo();
        break;

    case 'upload-banner':
        $logoBannerCtrl->uploadBanner();
        break;

    case 'delete-banner':
        $logoBannerCtrl->deleteBanner();
        break;

    case 'save-banner-text':
        $logoBannerCtrl->saveBannerText();
        break;

    case 'edit-services':
        $servicesCtrl->show();
        break;

    case 'save-services':
        $servicesCtrl->save();
        break;

    case 'settings':
        $settingsCtrl->show();
        break;

    case 'update-profile':
        $settingsCtrl->updateProfile();
        break;

    case 'update-password':
        $settingsCtrl->updatePassword();
        break;

    case 'update-options':
        $settingsCtrl->updateOptions();
        break;

    case 'update-template':
        $settingsCtrl->updateTemplate();
        break;

    case 'edit-template':
        if (empty($_SESSION['admin_logged'])) {
            header('Location: ' . $siteUrl . '?page=login');
            exit;
        }
        $adminId = $_SESSION['admin_id'];
        $optModel = new OptionModel($pdo);
        $admin = (new Admin($pdo))->findById($adminId);
        $restaurant = $admin->restaurant_id ? (new Restaurant($pdo))->findById($admin->restaurant_id) : null;
        $options = $optModel->getAll($adminId);
        $currentPalette = $options['site_palette'] ?? 'classic';
        $currentLayout = $options['site_layout'] ?? 'standard';
        $csrf_token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $pendingReservationsCount = 0;
        $isDemo = $_SESSION['demo_mode'] ?? false;
        $currentAdmin = $admin;
        require BASE_PATH . '/app/Views/admin/edit-template.php';
        break;

    case 'floor-plan':
        $floorPlanCtrl->show();
        break;

    case 'floor-plan-save':
        $floorPlanCtrl->save();
        break;

    case 'floor-plan-create-room':
        $floorPlanCtrl->createFloor();
        break;

    case 'floor-plan-rename-room':
        $floorPlanCtrl->renameFloor();
        break;

    case 'floor-plan-delete-room':
        $floorPlanCtrl->deleteFloor();
        break;

    case 'stats':
        $statsCtrl->show();
        break;

    case 'stats-data':
        $statsCtrl->getData();
        break;

    case 'reservations':
        $reservationCtrl->list();
        break;

    case 'reservation-update-status':
        $reservationCtrl->updateStatus();
        break;

    case 'reservation-pending-count':
        $reservationCtrl->pendingCount();
        break;

    case 'reservation-pending-list':
        $reservationCtrl->pendingList();
        break;

    case 'feedback':
        $feedbackCtrl->show();
        break;

    case 'submit-feedback':
        $feedbackCtrl->submit();
        break;

    case 'feedback-dashboard':
        if (empty($_SESSION['admin_logged'])) {
            header('Location: ' . $siteUrl . '?page=login');
            exit;
        }
        $admin = (new Admin($pdo))->findById($_SESSION['admin_id']);
        if (!$admin || $admin->role !== 'SUPER_ADMIN') {
            header('Location: ' . $siteUrl . '?page=dashboard');
            exit;
        }
        $feedbacks = $pdo->query('SELECT f.*, a.restaurant_name FROM feedbacks f LEFT JOIN admins a ON a.id = f.admin_id ORDER BY f.created_at DESC')->fetchAll();
        $csrf_token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrf_token;
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $pendingReservationsCount = 0;
        $isDemo = false;
        $currentAdmin = $admin;
        require BASE_PATH . '/app/Views/admin/feedback-dashboard.php';
        break;

    case 'stripe-checkout':
        $stripeCtrl->createCheckout();
        break;

    case 'stripe-success':
        $stripeCtrl->handleSuccess();
        break;

    case 'stripe-cancel':
        $stripeCtrl->cancelSubscription();
        break;

    case 'stripe-reactivate':
        $stripeCtrl->reactivateSubscription();
        break;

    case 'send-invitation':
        $adminCtrl->sendInvitation();
        break;

    case 'manage-clients':
        $clientMgmtCtrl->show();
        break;

    case 'activate-subscription':
        $clientMgmtCtrl->activateSubscription();
        break;

    case 'deactivate-subscription':
        $clientMgmtCtrl->deactivateSubscription();
        break;

    case 'notification-stream':
        $notifCtrl->stream();
        break;

    default:
        http_response_code(404);
        require BASE_PATH . '/app/Views/errors/404.php';
        break;
}
