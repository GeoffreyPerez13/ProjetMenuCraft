<?php
class SettingsController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $admin = $this->getCurrentAdmin();

        $section = $_GET['section'] ?? 'profile';

        // SUPER_ADMIN ne peut pas accéder aux sections premium
        $restrictedSections = ['premium', 'google-reviews', 'stats', 'online-booking', 'delivery', 'subscriptions'];
        if ($admin->role === 'SUPER_ADMIN' && in_array($section, $restrictedSections)) {
            $section = 'profile';
        }

        $optModel = new OptionModel($this->pdo);
        $options = $optModel->getAll($adminId);

        $subscription = (new ClientSubscription($this->pdo))->findByAdmin($adminId);
        $premiumFeatures = (new PremiumFeature($this->pdo))->getByAdmin($adminId);

        $this->render('admin/settings', [
            'pageTitle' => 'Paramètres — MenuCraft',
            'admin' => $admin,
            'section' => $section,
            'options' => $options,
            'subscription' => $subscription,
            'premiumFeatures' => $premiumFeatures,
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $this->blockIfDemo();
        $adminId = $this->getAdminId();

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $restaurantName = trim($_POST['restaurant_name'] ?? '');

        $adminModel = new Admin($this->pdo);
        $current = $adminModel->findById($adminId);

        $errors = [];
        if (mb_strlen($username) < 3) $errors[] = 'Nom d\'utilisateur trop court.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';

        if ($username !== $current->username && $adminModel->usernameExists($username)) {
            $errors[] = 'Ce nom d\'utilisateur est déjà pris.';
        }

        if (!empty($errors)) {
            $this->flash('error', implode('<br>', $errors));
            $this->redirect('settings', ['section' => 'profile']);
            return;
        }

        $adminModel->updateProfile($adminId, [
            'username' => $username,
            'email' => $email,
            'restaurant_name' => $restaurantName,
        ]);

        // Mettre à jour le nom du restaurant dans la table restaurants
        if ($current->restaurant_id) {
            $restModel = new Restaurant($this->pdo);
            $restModel->update($current->restaurant_id, [
                'name' => $restaurantName,
                'slug' => Restaurant::slugify($restaurantName),
            ]);
        }

        $_SESSION['admin_name'] = $restaurantName;
        $_SESSION['username'] = $username;

        $this->flash('success', 'Profil mis à jour.');
        $this->redirect('settings', ['section' => 'profile']);
    }

    public function updatePassword(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $this->blockIfDemo();
        $adminId = $this->getAdminId();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirmation'] ?? '';

        $admin = (new Admin($this->pdo))->findById($adminId);

        if (!password_verify($current, $admin->password)) {
            $this->flash('error', 'Mot de passe actuel incorrect.');
            $this->redirect('settings', ['section' => 'password']);
            return;
        }

        if ($new !== $confirm) {
            $this->flash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('settings', ['section' => 'password']);
            return;
        }

        $pwdErrors = Validator::validatePassword($new);
        if (!empty($pwdErrors)) {
            $this->flash('error', implode('<br>', $pwdErrors));
            $this->redirect('settings', ['section' => 'password']);
            return;
        }

        (new Admin($this->pdo))->updatePassword($adminId, $new);
        $this->flash('success', 'Mot de passe mis à jour.');
        $this->redirect('settings', ['section' => 'password']);
    }

    public function updateOptions(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();
        $optModel = new OptionModel($this->pdo);

        $booleanOptions = ['site_online', 'email_notifications', 'mail_reminder', 'hide_tour_button',
            'booking_enabled', 'booking_auto_complete', 'booking_daily_limit_enabled',
            'booking_require_phone', 'booking_require_email', 'booking_confirmation_email',
            'booking_auto_confirm'];
        foreach ($booleanOptions as $key) {
            if (isset($_POST[$key])) {
                $optModel->set($adminId, $key, $_POST[$key]);
            }
        }

        $textOptions = ['google_place_id', 'google_api_key', 'booking_message',
            'booking_min_party', 'booking_max_party', 'booking_advance_days',
            'booking_daily_limit', 'booking_min_hours_before', 'booking_time_slots'];
        foreach ($textOptions as $key) {
            if (isset($_POST[$key])) {
                $optModel->set($adminId, $key, trim($_POST[$key]));
            }
        }

        // Dates de fermeture
        if (isset($_POST['closure_dates'])) {
            $optModel->set($adminId, 'closure_dates', $_POST['closure_dates']);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json(['success' => true]);
            return;
        }

        $this->flash('success', 'Options mises à jour.');
        $section = $_POST['section'] ?? 'general';
        $this->redirect('settings', ['section' => $section]);
    }

    public function updateTemplate(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();
        $optModel = new OptionModel($this->pdo);

        $palette = $_POST['site_palette'] ?? 'classic';
        $layout = $_POST['site_layout'] ?? 'standard';

        $validPalettes = ['classic', 'modern', 'elegant', 'nature', 'rose', 'bistro', 'ocean', 'custom'];
        $validLayouts = ['standard', 'bistro', 'ocean', 'elegant', 'magazine'];
        $validFonts = ['Inter', 'Playfair Display', 'Roboto', 'Lora', 'Montserrat', 'Open Sans', 'Raleway', 'Poppins', 'Merriweather', 'Oswald', 'Nunito', 'Cormorant Garamond'];

        if (in_array($palette, $validPalettes)) $optModel->set($adminId, 'site_palette', $palette);
        if (in_array($layout, $validLayouts)) $optModel->set($adminId, 'site_layout', $layout);

        if ($palette === 'custom') {
            $customPrimary = $_POST['custom_primary'] ?? '#b45309';
            $customBg = $_POST['custom_bg'] ?? '#ffffff';
            $customFont = $_POST['custom_font'] ?? 'Inter';
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $customPrimary)) $optModel->set($adminId, 'custom_primary', $customPrimary);
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $customBg)) $optModel->set($adminId, 'custom_bg', $customBg);
            if (in_array($customFont, $validFonts)) $optModel->set($adminId, 'custom_font', $customFont);
        }

        $this->flash('success', 'Template mis à jour.');
        $this->redirect('edit-template');
    }
}
