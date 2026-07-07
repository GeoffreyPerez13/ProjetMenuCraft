<?php
class DisplayController extends BaseController
{
    public function show(): void
    {
        $slug = $_GET['slug'] ?? '';
        if (empty($slug)) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $restaurantModel = new Restaurant($this->pdo);
        $restaurant = $restaurantModel->findBySlug($slug);
        if (!$restaurant) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        // Trouver l'admin associé
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE restaurant_id = :rid LIMIT 1');
        $stmt->execute([':rid' => $restaurant->id]);
        $admin = $stmt->fetch();
        if (!$admin) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $adminId = $admin->id;
        $optModel = new OptionModel($this->pdo);
        $options = $optModel->getAll($adminId);

        $isSuperAdmin = !empty($_SESSION['admin_id']) && $this->isSuperAdmin();
        $isOwner = !empty($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] === $adminId;
        $isDemo = !empty($_SESSION['demo_mode']);

        // Vérifier abonnement
        $subModel = new ClientSubscription($this->pdo);
        $hasActiveSub = $subModel->isActive($adminId);

        if (!$hasActiveSub && !$isSuperAdmin && !$isDemo) {
            $this->render('errors/offline', ['pageTitle' => 'Site hors ligne']);
            return;
        }

        // Mode maintenance
        $siteOnline = $options['site_online'] ?? '0';
        if ($siteOnline === '0' && !$isOwner && !$isSuperAdmin && !$isDemo) {
            $this->render('errors/maintenance', [
                'pageTitle' => 'Site en maintenance',
                'restaurantName' => $admin->restaurant_name,
            ]);
            return;
        }

        $isPreview = $isOwner;

        // Charger les données
        $logo = $this->pdo->prepare('SELECT * FROM logos WHERE admin_id = :aid');
        $logo->execute([':aid' => $adminId]);
        $logo = $logo->fetch() ?: null;

        $banner = $this->pdo->prepare('SELECT * FROM banners WHERE admin_id = :aid');
        $banner->execute([':aid' => $adminId]);
        $banner = $banner->fetch() ?: null;

        $contactModel = new Contact($this->pdo);
        $contact = $contactModel->findByAdmin($adminId);

        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);
        $categories = $categoryModel->getByAdmin($adminId);
        $dishesByCategory = [];
        $allergenesByDish = [];
        foreach ($categories as $cat) {
            $dishes = $dishModel->getByCategory($cat->id);
            $dishesByCategory[$cat->id] = $dishes;
            foreach ($dishes as $dish) {
                $allergenesByDish[$dish->id] = $dishModel->getAllergenes($dish->id);
            }
        }

        $cardImages = (new CardImage($this->pdo))->getByAdmin($adminId);
        $dailyMenus = (new DailyMenu($this->pdo))->getByAdmin($adminId, true);

        // Avis Google
        $googleReviewsData = null;
        $googleEnabled = ($options['google_reviews_enabled'] ?? '0') === '1';
        if ($googleEnabled && PremiumFeature::isEnabled($this->pdo, $adminId, 'google_reviews')) {
            $placeId = $options['google_place_id'] ?? '';
            $apiKey = $options['google_api_key'] ?? '';
            if ($placeId && $apiKey) {
                $googleReviewsData = (new GoogleReviews($this->pdo))->getReviews($placeId, $apiKey);
            }
        }

        // Réservations
        $bookingEnabled = ($options['booking_enabled'] ?? '0') === '1'
            && PremiumFeature::isEnabled($this->pdo, $adminId, 'online_booking');

        $palette = $options['site_palette'] ?? 'classic';
        $layout = $options['site_layout'] ?? 'standard';

        // Preview mode
        if ($isPreview) {
            $palette = $_GET['preview_palette'] ?? $palette;
            $layout = $_GET['preview_layout'] ?? $layout;
        }

        // Tracking
        if (!$isPreview) {
            (new SiteVisit($this->pdo))->track($adminId, '/display/' . $slug);
        }

        // Dates de fermeture
        $closureDates = json_decode($options['closure_dates'] ?? '[]', true) ?: [];

        $this->render('display', [
            'pageTitle' => $admin->restaurant_name,
            'admin' => $admin,
            'restaurant' => $restaurant,
            'logo' => $logo,
            'banner' => $banner,
            'contact' => $contact,
            'categories' => $categories,
            'dishesByCategory' => $dishesByCategory,
            'allergenesByDish' => $allergenesByDish,
            'cardImages' => $cardImages,
            'dailyMenus' => $dailyMenus,
            'options' => $options,
            'carteMode' => $admin->carte_mode,
            'palette' => $palette,
            'layout' => $layout,
            'isPreview' => $isPreview,
            'googleReviewsData' => $googleReviewsData,
            'bookingEnabled' => $bookingEnabled,
            'closureDates' => $closureDates,
        ]);
    }
}
