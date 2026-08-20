<?php
class CardController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $admin = $this->getCurrentAdmin();

        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);
        $allergeneModel = new Allergene($this->pdo);
        $cardImageModel = new CardImage($this->pdo);
        $dailyMenuModel = new DailyMenu($this->pdo);

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

        $this->render('admin/edit-card', [
            'pageTitle' => 'Gérer la carte — MenuCraft',
            'admin' => $admin,
            'categories' => $categories,
            'dishesByCategory' => $dishesByCategory,
            'allergenesByDish' => $allergenesByDish,
            'allergenes' => $allergeneModel->getAll(),
            'cardImages' => $cardImageModel->getByAdmin($adminId),
            'dailyMenus' => $dailyMenuModel->getByAdmin($adminId),
            'carteMode' => $admin->carte_mode,
        ]);
    }

    public function saveCategory(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $categoryModel = new Category($this->pdo);
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $this->flash('error', 'Le nom de la catégorie est requis.');
            $this->redirect('edit-card');
            return;
        }

        $image = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $image = $this->handleUpload($_FILES['image'], 'categories');
        }

        if ($id > 0) {
            $cat = $categoryModel->findById($id);
            if (!$cat || $cat->admin_id !== $adminId) {
                $this->flash('error', 'Catégorie introuvable.');
                $this->redirect('edit-card');
                return;
            }
            $data = ['name' => $name, 'description' => $description];
            if ($image) $data['image'] = $image;
            $categoryModel->update($id, $data);
            $this->flash('success', 'Catégorie mise à jour.');
        } else {
            $data = [
                'admin_id' => $adminId,
                'name' => $name,
                'description' => $description,
                'image' => $image,
                'display_order' => $categoryModel->getNextOrder($adminId),
            ];
            $categoryModel->create($data);
            $this->flash('success', 'Catégorie créée.');
        }

        $this->redirect('edit-card');
    }

    public function batchCategories(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $lines = array_filter(array_map('trim', explode("\n", $_POST['names'] ?? '')));
        if (empty($lines)) {
            $this->flash('error', 'Aucune catégorie saisie.');
            $this->redirect('edit-card');
            return;
        }

        $categoryModel = new Category($this->pdo);
        $count = 0;
        foreach ($lines as $name) {
            if (empty($name)) continue;
            $categoryModel->create([
                'admin_id' => $adminId,
                'name' => $name,
                'description' => '',
                'image' => null,
                'display_order' => $categoryModel->getNextOrder($adminId),
            ]);
            $count++;
        }

        $this->flash('success', $count . ' catégorie(s) créée(s).');
        $this->redirect('edit-card');
    }

    public function batchDishes(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $categoryId = (int)($_POST['category_id'] ?? 0);
        if ($categoryId > 0) {
            $cat = (new Category($this->pdo))->findById($categoryId);
            if (!$cat || $cat->admin_id !== $adminId) {
                $this->flash('error', 'Catégorie introuvable.');
                $this->redirect('edit-card');
                return;
            }
        }
        $lines = array_filter(array_map('trim', explode("\n", $_POST['dishes'] ?? '')));

        if (empty($lines) || $categoryId <= 0) {
            $this->flash('error', 'Données invalides.');
            $this->redirect('edit-card');
            return;
        }

        $dishModel = new Dish($this->pdo);
        $count = 0;
        foreach ($lines as $line) {
            // Format: "Nom ; Prix ; Description" ou juste "Nom ; Prix" ou "Nom"
            $parts = preg_split('/[;|]/', $line, 3);
            $name = trim($parts[0]);
            $price = isset($parts[1]) ? (float)trim($parts[1]) : 0;
            $description = isset($parts[2]) ? trim($parts[2]) : '';
            if (empty($name)) continue;

            $dishModel->create([
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'image' => null,
                'is_active' => 1,
            ]);
            $count++;
        }

        $this->flash('success', $count . ' plat(s) créé(s).');
        $this->redirect('edit-card');
    }

    public function deleteCategory(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();
        $id = (int)($_POST['category_id'] ?? 0);
        $categoryModel = new Category($this->pdo);
        $cat = $categoryModel->findById($id);
        if (!$cat || $cat->admin_id !== $adminId) {
            $this->flash('error', 'Catégorie introuvable.');
            $this->redirect('edit-card');
            return;
        }
        $categoryModel->delete($id);
        $this->flash('success', 'Catégorie supprimée.');
        $this->redirect('edit-card');
    }

    public function saveDish(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $dishModel = new Dish($this->pdo);
        $categoryModel = new Category($this->pdo);
        $id = (int)($_POST['dish_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $allergeneIds = $_POST['allergenes'] ?? [];

        if (empty($name) || $price < 0) {
            $this->flash('error', 'Nom et prix requis.');
            $this->redirect('edit-card');
            return;
        }

        $image = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $image = $this->handleUpload($_FILES['image'], 'plats');
        }

        if ($id > 0) {
            $dish = $dishModel->findById($id);
            if (!$dish) {
                $this->flash('error', 'Plat introuvable.');
                $this->redirect('edit-card');
                return;
            }
            $cat = $categoryModel->findById($dish->category_id);
            if (!$cat || $cat->admin_id !== $adminId) {
                $this->flash('error', 'Accès non autorisé.');
                $this->redirect('edit-card');
                return;
            }
            $data = ['name' => $name, 'description' => $description, 'price' => $price, 'is_active' => $isActive];
            if ($image) $data['image'] = $image;
            $dishModel->update($id, $data);
            $dishModel->syncAllergenes($id, $allergeneIds);
            $this->flash('success', 'Plat mis à jour.');
        } else {
            $data = [
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'image' => $image,
                'is_active' => $isActive,
            ];
            $dishId = $dishModel->create($data);
            $dishModel->syncAllergenes($dishId, $allergeneIds);
            $this->flash('success', 'Plat créé.');
        }

        $this->redirect('edit-card');
    }

    public function deleteDish(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();
        $dishModel = new Dish($this->pdo);
        $id = (int)($_POST['dish_id'] ?? 0);
        $dish = $dishModel->findById($id);
        if ($dish) {
            $cat = (new Category($this->pdo))->findById($dish->category_id);
            if (!$cat || $cat->admin_id !== $adminId) {
                $this->flash('error', 'Accès non autorisé.');
                $this->redirect('edit-card');
                return;
            }
        }
        $dishModel->delete($id);
        $this->flash('success', 'Plat supprimé.');
        $this->redirect('edit-card');
    }

    public function reorderCategories(): void
    {
        $this->requireAuth();
        $this->verifyCsrfAjax();
        $adminId = $this->getAdminId();
        $ids = json_decode(file_get_contents('php://input'), true)['ids'] ?? [];
        if (!empty($ids)) {
            $categoryModel = new Category($this->pdo);
            foreach ($ids as $id) {
                $cat = $categoryModel->findById((int)$id);
                if (!$cat || $cat->admin_id !== $adminId) {
                    $this->json(['error' => 'Accès non autorisé'], 403);
                    return;
                }
            }
            $categoryModel->reorder($ids);
        }
        $this->json(['success' => true]);
    }

    public function reorderDishes(): void
    {
        $this->requireAuth();
        $this->verifyCsrfAjax();
        $ids = json_decode(file_get_contents('php://input'), true)['ids'] ?? [];
        if (!empty($ids)) {
            (new Dish($this->pdo))->reorder($ids);
        }
        $this->json(['success' => true]);
    }

    public function uploadImage(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        if (!empty($_FILES['image']['tmp_name'])) {
            $filename = $this->handleUpload($_FILES['image'], 'card_images');
            if ($filename) {
                (new CardImage($this->pdo))->create($adminId, $filename);
                $this->flash('success', 'Image ajoutée.');
            }
        }
        $this->redirect('edit-card');
    }

    public function deleteImage(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();
        $id = (int)($_POST['image_id'] ?? 0);
        $cardImageModel = new CardImage($this->pdo);
        $image = $cardImageModel->findById($id);
        if ($image) {
            if ($image->admin_id !== $adminId) {
                $this->flash('error', 'Accès non autorisé.');
                $this->redirect('edit-card');
                return;
            }
            $filePath = BASE_PATH . '/public/uploads/' . $image->filename;
            if (file_exists($filePath)) unlink($filePath);
            $cardImageModel->delete($id);
        }
        $this->flash('success', 'Image supprimée.');
        $this->redirect('edit-card');
    }

    public function viewCard(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $admin = $this->getCurrentAdmin();

        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);

        $categories = $categoryModel->getByAdmin($adminId);
        $dishesByCategory = [];
        foreach ($categories as $cat) {
            $dishesByCategory[$cat->id] = $dishModel->getByCategory($cat->id);
        }

        $this->render('admin/view-card', [
            'pageTitle' => 'Prévisualisation carte — MenuCraft',
            'admin' => $admin,
            'categories' => $categories,
            'dishesByCategory' => $dishesByCategory,
            'cardImages' => (new CardImage($this->pdo))->getByAdmin($adminId),
            'dailyMenus' => (new DailyMenu($this->pdo))->getByAdmin($adminId, true),
            'carteMode' => $admin->carte_mode,
        ]);
    }

    public function saveDailyMenu(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $id = (int)($_POST['menu_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = !empty($_POST['price']) ? (float)$_POST['price'] : null;

        $items = [];
        $labels = $_POST['item_label'] ?? [];
        $values = $_POST['item_value'] ?? [];
        for ($i = 0; $i < count($labels); $i++) {
            if (!empty(trim($labels[$i]))) {
                $items[] = ['label' => trim($labels[$i]), 'value' => trim($values[$i] ?? '')];
            }
        }

        $menuModel = new DailyMenu($this->pdo);

        if ($id > 0) {
            $menu = $menuModel->findById($id);
            if (!$menu || $menu->admin_id !== $adminId) {
                $this->flash('error', 'Menu introuvable.');
                $this->redirect('edit-card');
                return;
            }
            $menuModel->update($id, [
                'title' => $title, 'description' => $description,
                'price' => $price, 'items' => json_encode($items),
            ]);
            $this->flash('success', 'Menu mis à jour.');
        } else {
            $menuModel->create([
                'admin_id' => $adminId, 'title' => $title,
                'description' => $description, 'price' => $price,
                'items' => json_encode($items),
            ]);
            $this->flash('success', 'Menu créé.');
        }

        $this->redirect('edit-card');
    }

    public function deleteDailyMenu(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();
        $menuModel = new DailyMenu($this->pdo);
        $id = (int)($_POST['menu_id'] ?? 0);
        $menu = $menuModel->findById($id);
        if (!$menu || $menu->admin_id !== $adminId) {
            $this->flash('error', 'Menu introuvable.');
            $this->redirect('edit-card');
            return;
        }
        $menuModel->delete($id);
        $this->flash('success', 'Menu supprimé.');
        $this->redirect('edit-card');
    }

    public function toggleDailyMenu(): void
    {
        $this->requireAuth();
        $this->verifyCsrfAjax();
        $adminId = $this->getAdminId();
        $menuModel = new DailyMenu($this->pdo);
        $id = (int)($_POST['menu_id'] ?? $_GET['id'] ?? 0);
        $menu = $menuModel->findById($id);
        if (!$menu || $menu->admin_id !== $adminId) {
            $this->json(['error' => 'Accès non autorisé'], 403);
            return;
        }
        $menuModel->toggle($id);
        $this->json(['success' => true]);
    }

    public function reorderDailyMenus(): void
    {
        $this->requireAuth();
        $this->verifyCsrfAjax();
        $ids = json_decode(file_get_contents('php://input'), true)['ids'] ?? [];
        if (!empty($ids)) {
            (new DailyMenu($this->pdo))->reorder($ids);
        }
        $this->json(['success' => true]);
    }

}
