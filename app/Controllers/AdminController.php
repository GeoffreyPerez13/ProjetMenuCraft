<?php
class AdminController extends BaseController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $loginAttempt = new LoginAttempt($this->pdo);

            // Check IP block
            if ($loginAttempt->isIpBlocked($ip)) {
                $remaining = $loginAttempt->getRemainingLockoutMinutes($ip);
                $this->flash('error', 'Trop de tentatives depuis cette adresse. Réessayez dans ' . $remaining . ' minute' . ($remaining > 1 ? 's' : '') . '.');
                $this->redirect('login');
                return;
            }

            // Check account lock
            if (!empty($username) && $loginAttempt->isAccountLocked($username)) {
                $remaining = $loginAttempt->getAccountRemainingMinutes($username);
                $this->flash('error', 'Ce compte est temporairement verrouillé suite à de nombreuses tentatives. Réessayez dans ' . $remaining . ' minute' . ($remaining > 1 ? 's' : '') . '.');
                $this->redirect('login');
                return;
            }

            $adminModel = new Admin($this->pdo);
            $admin = $adminModel->findByUsername($username);

            if (!$admin || !password_verify($password, $admin->password)) {
                $loginAttempt->record($ip, $username, false);

                // Check if account just got locked → send alert email
                if (!empty($username) && $loginAttempt->isAccountLocked($username)) {
                    $targetAdmin = $adminModel->findByUsername($username);
                    if ($targetAdmin && !empty($targetAdmin->email)) {
                        $mailer = new Mailer();
                        $mailer->send($targetAdmin->email, 'Alerte sécurité — Compte verrouillé — MenuCraft',
                            '<h2 style="color:#dc2626;">⚠️ Alerte de sécurité</h2>
                            <p>Votre compte <strong>' . htmlspecialchars($username) . '</strong> a été temporairement verrouillé suite à de multiples tentatives de connexion échouées.</p>
                            <p><strong>Adresse IP :</strong> ' . htmlspecialchars($ip) . '</p>
                            <p><strong>Date :</strong> ' . date('d/m/Y à H:i') . '</p>
                            <p>Le compte sera automatiquement déverrouillé dans 30 minutes.</p>
                            <p style="color:#a8a29e;font-size:13px;">Si vous êtes à l\'origine de ces tentatives, ignorez ce message. Sinon, nous vous recommandons de changer votre mot de passe dès que possible.</p>'
                        );
                    }
                }

                $this->flash('error', 'Identifiants incorrects.');
                $this->redirect('login');
                return;
            }

            if (!$admin->email_verified) {
                $this->flash('error', 'Veuillez vérifier votre email avant de vous connecter.');
                $this->redirect('login');
                return;
            }

            // Check if account is suspended
            if (!empty($admin->suspended)) {
                $reason = $admin->suspended_reason ? ' Raison : ' . htmlspecialchars($admin->suspended_reason) : '';
                $this->flash('error', 'Votre compte a été suspendu par l\'administrateur.' . $reason);
                $this->redirect('login');
                return;
            }

            // Successful login — clear attempts
            $loginAttempt->record($ip, $username, true);
            $loginAttempt->clearForIp($ip);
            $loginAttempt->clearForAccount($username);

            // Update last login timestamp
            $this->pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $admin->id]);

            session_regenerate_id(true);
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_id'] = $admin->id;
            $_SESSION['admin_name'] = $admin->restaurant_name;
            $_SESSION['username'] = $admin->username;

            $this->redirect('dashboard');
            return;
        }

        $this->render('admin/login', [
            'pageTitle' => 'Connexion — MenuCraft',
        ]);
    }

    public function logout(): void
    {
        if (!empty($_SESSION['demo_mode'])) {
            $this->redirect('demo-logout');
            return;
        }
        session_destroy();
        session_start();
        $this->flash('success', 'Vous avez été déconnecté avec succès.');
        $this->redirect('login');
    }

    public function autoRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $restaurantName = trim($_POST['restaurant_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirmation'] ?? '';

            // Validation
            $errors = [];
            if (mb_strlen($username) < 3) $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse email invalide.';
            if (empty($restaurantName)) $errors[] = 'Le nom du restaurant est requis.';
            if ($password !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas.';

            $pwdErrors = Validator::validatePassword($password);
            $errors = array_merge($errors, $pwdErrors);

            $adminModel = new Admin($this->pdo);
            if ($adminModel->usernameExists($username)) $errors[] = 'Ce nom d\'utilisateur est déjà pris.';
            if ($adminModel->emailExists($email)) $errors[] = 'Cette adresse email est déjà utilisée.';

            if (!empty($errors)) {
                $this->flash('error', implode('<br>', $errors));
                $this->render('admin/auto-register', [
                    'pageTitle' => 'Inscription — MenuCraft',
                    'old' => $_POST,
                ]);
                return;
            }

            // Créer le restaurant
            $restaurantModel = new Restaurant($this->pdo);
            $slug = Restaurant::slugify($restaurantName);
            $restaurantId = $restaurantModel->create($restaurantName, $slug);

            // Token de vérification email
            $verificationToken = bin2hex(random_bytes(32));

            // Créer le compte
            $adminId = $adminModel->createAccountDirect([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'restaurant_name' => $restaurantName,
                'restaurant_id' => $restaurantId,
                'email_verified' => 0,
                'verification_token' => $verificationToken,
            ]);

            // Créer abonnement basique inactif
            $subModel = new ClientSubscription($this->pdo);
            $subModel->create($adminId, 'basique', 'inactive');

            // En mode BETA, activer toutes les features
            if (defined('BETA_MODE') && BETA_MODE) {
                $subModel->activate($adminId, ['plan_type' => 'premium', 'price_per_month' => 0]);
                $premiumModel = new PremiumFeature($this->pdo);
                $premiumModel->activateAll($adminId);
            }

            // Options par défaut
            $optModel = new OptionModel($this->pdo);
            $optModel->set($adminId, 'site_online', '0');
            $optModel->set($adminId, 'site_palette', 'classic');
            $optModel->set($adminId, 'site_layout', 'standard');
            $optModel->set($adminId, 'email_notifications', '1');

            // Email de vérification
            $mailer = new Mailer();
            $verifyUrl = SITE_URL . '?page=verify-email&token=' . $verificationToken;
            $mailer->send($email, 'Vérifiez votre email — MenuCraft',
                '<h2>Bienvenue sur MenuCraft !</h2>
                <p>Cliquez sur le bouton ci-dessous pour vérifier votre adresse email :</p>
                <p><a href="' . $verifyUrl . '" style="background:#b45309;color:#fff;padding:14px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:600;">Vérifier mon email</a></p>
                <p style="color:#a8a29e;font-size:13px;">Ce lien expire dans 24 heures.</p>'
            );

            $this->flash('success', 'Compte créé avec succès ! Vérifiez votre email pour activer votre compte.');
            $this->redirect('login');
            return;
        }

        $this->render('admin/auto-register', [
            'pageTitle' => 'Inscription — MenuCraft',
            'old' => [],
        ]);
    }

    public function register(): void
    {
        $token = $_GET['token'] ?? '';

        // Vérifier l'invitation
        $stmt = $this->pdo->prepare(
            'SELECT * FROM invitations WHERE token = :t AND used = 0 AND expiry > NOW() LIMIT 1'
        );
        $stmt->execute([':t' => $token]);
        $invitation = $stmt->fetch();

        if (!$invitation) {
            $this->flash('error', 'Lien d\'invitation invalide ou expiré.');
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirmation'] ?? '';

            $errors = [];
            if (mb_strlen($username) < 3) $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            if ($password !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas.';
            $errors = array_merge($errors, Validator::validatePassword($password));

            $adminModel = new Admin($this->pdo);
            if ($adminModel->usernameExists($username)) $errors[] = 'Ce nom d\'utilisateur est déjà pris.';

            if (!empty($errors)) {
                $this->flash('error', implode('<br>', $errors));
                $this->render('admin/register', [
                    'pageTitle' => 'Inscription — MenuCraft',
                    'invitation' => $invitation,
                    'token' => $token,
                ]);
                return;
            }

            $restaurantModel = new Restaurant($this->pdo);
            $slug = Restaurant::slugify($invitation->restaurant_name);
            $restaurantId = $restaurantModel->create($invitation->restaurant_name, $slug);

            $adminId = $adminModel->createAccount([
                'username' => $username,
                'email' => $invitation->email,
                'password' => $password,
                'restaurant_name' => $invitation->restaurant_name,
                'restaurant_id' => $restaurantId,
            ]);

            // Marquer l'invitation comme utilisée
            $this->pdo->prepare('UPDATE invitations SET used = 1 WHERE id = :id')
                ->execute([':id' => $invitation->id]);

            // Créer abonnement
            $subModel = new ClientSubscription($this->pdo);
            if (defined('BETA_MODE') && BETA_MODE) {
                $subModel->create($adminId, 'premium', 'active');
                (new PremiumFeature($this->pdo))->activateAll($adminId);
            } else {
                $subModel->create($adminId);
            }

            // Options par défaut
            $optModel = new OptionModel($this->pdo);
            $optModel->set($adminId, 'site_online', '0');
            $optModel->set($adminId, 'site_palette', 'classic');
            $optModel->set($adminId, 'site_layout', 'standard');

            $this->flash('success', 'Compte créé avec succès ! Connectez-vous.');
            $this->redirect('login');
            return;
        }

        $this->render('admin/register', [
            'pageTitle' => 'Inscription — MenuCraft',
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        $adminModel = new Admin($this->pdo);

        if ($adminModel->verifyEmail($token)) {
            $mailer = new Mailer();
            $stmt = $this->pdo->prepare('SELECT email FROM admins WHERE verification_token IS NULL AND email_verified = 1 ORDER BY updated_at DESC LIMIT 1');
            $stmt->execute();

            $this->flash('success', 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.');
        } else {
            $this->flash('error', 'Lien de vérification invalide ou expiré.');
        }

        $this->redirect('login');
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $admin = $this->getCurrentAdmin();

        $restaurantModel = new Restaurant($this->pdo);
        $restaurant = $admin->restaurant_id ? $restaurantModel->findById($admin->restaurant_id) : null;

        $optModel = new OptionModel($this->pdo);
        $siteOnline = $optModel->get($adminId, 'site_online', '0');

        $subModel = new ClientSubscription($this->pdo);
        $subscription = $subModel->findByAdmin($adminId);

        $pendingReservations = 0;
        if (PremiumFeature::isEnabled($this->pdo, $adminId, 'online_booking')) {
            $resModel = new Reservation($this->pdo);
            $pendingReservations = $resModel->getPendingCount($adminId);
        }

        // Démo tokens pour SUPER_ADMIN
        $demoTokens = [];
        if ($admin->role === 'SUPER_ADMIN') {
            $demoModel = new DemoToken($this->pdo);
            $demoModel->cleanExpired();
            $demoTokens = $demoModel->getActiveTokens();
        }

        $this->render('admin/dashboard', [
            'pageTitle' => 'Tableau de bord — MenuCraft',
            'admin' => $admin,
            'restaurant' => $restaurant,
            'siteOnline' => $siteOnline,
            'subscription' => $subscription,
            'pendingReservations' => $pendingReservations,
            'demoTokens' => $demoTokens,
        ]);
    }

    public function sendInvitation(): void
    {
        $this->requireSuperAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();

            $email = trim($_POST['email'] ?? '');
            $restaurantName = trim($_POST['restaurant_name'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($restaurantName)) {
                $this->flash('error', 'Email et nom du restaurant requis.');
                $this->redirect('send-invitation');
                return;
            }

            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));

            $stmt = $this->pdo->prepare(
                'INSERT INTO invitations (email, restaurant_name, token, expiry) VALUES (:e, :rn, :t, :exp)'
            );
            $stmt->execute([':e' => $email, ':rn' => $restaurantName, ':t' => $token, ':exp' => $expiry]);

            $registerUrl = SITE_URL . '?page=register&token=' . $token;
            $mailer = new Mailer();
            $mailer->send($email, 'Invitation MenuCraft — ' . $restaurantName,
                '<h2>Vous êtes invité sur MenuCraft !</h2>
                <p>Vous avez été invité à créer le site vitrine de <strong>' . htmlspecialchars($restaurantName) . '</strong>.</p>
                <p><a href="' . $registerUrl . '" style="background:#b45309;color:#fff;padding:14px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:600;">Créer mon compte</a></p>
                <p style="color:#a8a29e;font-size:13px;">Ce lien expire dans 7 jours.</p>'
            );

            $this->flash('success', 'Invitation envoyée à ' . htmlspecialchars($email));
            $this->redirect('dashboard');
            return;
        }

        $this->render('admin/send-invitation', [
            'pageTitle' => 'Envoyer une invitation — MenuCraft',
        ]);
    }
}
