<?php
class DeliveryController extends BaseController
{
    public function list(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        $model = new DeliveryOrder($this->pdo);
        $optModel = new OptionModel($this->pdo);
        $status = $_GET['status'] ?? null;

        $this->render('admin/delivery-orders', [
            'pageTitle' => 'Livraisons — MenuCraft',
            'orders' => $model->getByAdmin($adminId, $status),
            'newCount' => $model->getNewCount($adminId),
            'todayCount' => $model->getTodayCount($adminId),
            'todayRevenue' => $model->getTodayRevenue($adminId),
            'filterStatus' => $status,
            'deliveryModel' => $model,
            'deliveryEnabled' => ($optModel->get($adminId, 'delivery_enabled') ?? '0') === '1',
        ]);
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $id = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $validStatuses = ['preparing', 'delivering', 'delivered', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            $this->flash('error', 'Statut invalide.');
            $this->redirect('delivery-orders');
            return;
        }

        $model = new DeliveryOrder($this->pdo);
        $order = $model->findById($id);

        if (!$order || $order->admin_id !== $adminId) {
            $this->flash('error', 'Commande introuvable.');
            $this->redirect('delivery-orders');
            return;
        }

        $model->updateStatus($id, $status);
        $this->flash('success', 'Commande mise à jour.');
        $this->redirect('delivery-orders');
    }

    public function updateNotes(): void
    {
        $this->requireAuth();
        $this->verifyCsrfAjax();
        $adminId = $this->getAdminId();

        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['order_id'] ?? 0);
        $notes = trim($input['admin_notes'] ?? '');

        $model = new DeliveryOrder($this->pdo);
        $order = $model->findById($id);

        if (!$order || $order->admin_id !== $adminId) {
            $this->json(['error' => 'Commande introuvable.'], 404);
            return;
        }

        $model->updateNotes($id, $notes ?: null);
        $this->json(['success' => true]);
    }

    public function orderDetail(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        $id = (int)($_GET['id'] ?? 0);
        $model = new DeliveryOrder($this->pdo);
        $order = $model->findById($id);

        if (!$order || $order->admin_id !== $adminId) {
            $this->json(['error' => 'Commande introuvable.'], 404);
            return;
        }

        $items = $model->getItems($id);
        $this->json(['order' => $order, 'items' => $items]);
    }

    public function publicOrder(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
            return;
        }

        $rateLimiter = new RateLimiter();
        if ($rateLimiter->isLimited('delivery', 5, 3600)) {
            $this->json(['error' => 'Trop de commandes. Réessayez plus tard.'], 429);
            return;
        }

        $adminId = (int)($_POST['admin_id'] ?? 0);
        if (!$adminId) {
            $this->json(['error' => 'Restaurant non trouvé.'], 400);
            return;
        }

        // Check if delivery is enabled
        $optModel = new OptionModel($this->pdo);
        if (($optModel->get($adminId, 'delivery_enabled') ?? '0') !== '1') {
            $this->json(['error' => 'La livraison n\'est pas disponible.'], 400);
            return;
        }

        // Block if no time slots configured
        $deliverySlots = array_filter(array_map('trim', explode("\n", $optModel->get($adminId, 'delivery_time_slots'))));
        if (empty($deliverySlots)) {
            $this->json(['error' => 'La livraison n\'est pas disponible pour le moment.'], 400);
            return;
        }

        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $address = trim($_POST['delivery_address'] ?? '');
        $timeSlot = trim($_POST['delivery_time_slot'] ?? '');
        $notes = trim($_POST['delivery_notes'] ?? '');
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true) ?: [];
        $menuItemsJson = $_POST['menu_items'] ?? '[]';
        $menuItems = json_decode($menuItemsJson, true) ?: [];

        if (empty($name) || empty($phone) || empty($address) || (empty($items) && empty($menuItems))) {
            $this->json(['error' => 'Champs requis manquants.'], 400);
            return;
        }

        if (empty($timeSlot) || !in_array($timeSlot, $deliverySlots)) {
            $this->json(['error' => 'Créneau horaire invalide.'], 400);
            return;
        }

        // Minimum order check
        $minOrder = (float)($optModel->get($adminId, 'delivery_min_order') ?? 0);
        $deliveryFee = (float)($optModel->get($adminId, 'delivery_fee') ?? 0);

        // Calculate total from DB prices (not from client) to prevent tampering
        $totalAmount = 0;
        $validatedItems = [];

        // Validate dish items
        foreach ($items as $item) {
            $dishId = (int)($item['dish_id'] ?? 0);
            $qty = max(1, (int)($item['quantity'] ?? 1));
            if (!$dishId) continue;

            $stmt = $this->pdo->prepare('SELECT id, name, price FROM plats WHERE id = :id');
            $stmt->execute([':id' => $dishId]);
            $dish = $stmt->fetch();
            if (!$dish) continue;

            $validatedItems[] = [
                'type' => 'dish',
                'dish_id' => $dish->id,
                'dish_name' => $dish->name,
                'quantity' => $qty,
                'unit_price' => $dish->price,
            ];
            $totalAmount += $dish->price * $qty;
        }

        // Validate menu items
        $menuModel = new DailyMenu($this->pdo);
        foreach ($menuItems as $mi) {
            $menuId = (int)($mi['menu_id'] ?? 0);
            $qty = max(1, (int)($mi['quantity'] ?? 1));
            $selectedChoices = $mi['selected_choices'] ?? [];
            if (!$menuId) continue;

            $menu = $menuModel->findById($menuId);
            if (!$menu || $menu->admin_id !== $adminId || !$menu->is_active || !$menu->price) continue;

            $choicesStr = [];
            foreach ($selectedChoices as $cat => $choice) {
                $choicesStr[] = $cat . ': ' . $choice;
            }

            $validatedItems[] = [
                'type' => 'menu',
                'menu_id' => $menu->id,
                'dish_name' => $menu->title . (!empty($choicesStr) ? ' (' . implode(', ', $choicesStr) . ')' : ''),
                'quantity' => $qty,
                'unit_price' => (float)$menu->price,
                'selected_choices' => $selectedChoices,
            ];
            $totalAmount += (float)$menu->price * $qty;
        }

        if (empty($validatedItems)) {
            $this->json(['error' => 'Aucun article valide dans la commande.'], 400);
            return;
        }

        if ($minOrder > 0 && $totalAmount < $minOrder) {
            $this->json(['error' => 'Montant minimum de commande : ' . number_format($minOrder, 2, ',', '') . ' €.'], 400);
            return;
        }

        $model = new DeliveryOrder($this->pdo);
        $orderId = $model->create([
            'admin_id' => $adminId,
            'customer_name' => $name,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'delivery_address' => $address,
            'delivery_time_slot' => $timeSlot ?: null,
            'delivery_notes' => $notes,
            'total_amount' => $totalAmount,
            'delivery_fee' => $deliveryFee,
        ]);

        foreach ($validatedItems as $vi) {
            $model->addItem($orderId, $vi);
        }

        $rateLimiter->hit('delivery');

        // Email confirmation
        if ($email) {
            $itemsHtml = '';
            foreach ($validatedItems as $vi) {
                $label = htmlspecialchars($vi['dish_name']);
                if (($vi['type'] ?? 'dish') === 'menu') $label = '🍽️ ' . $label;
                $itemsHtml .= '<li>' . $label . ' x' . $vi['quantity'] . ' — ' . number_format($vi['unit_price'] * $vi['quantity'], 2, ',', '') . ' €</li>';
            }
            $mailer = new Mailer();
            $mailer->send($email, 'Commande reçue — MenuCraft',
                '<h2>Votre commande a bien été enregistrée !</h2>
                <ul>' . $itemsHtml . '</ul>
                <p><strong>Sous-total :</strong> ' . number_format($totalAmount, 2, ',', '') . ' €</p>
                <p><strong>Frais de livraison :</strong> ' . number_format($deliveryFee, 2, ',', '') . ' €</p>
                <p><strong>Total :</strong> ' . number_format($totalAmount + $deliveryFee, 2, ',', '') . ' €</p>
                <p><strong>Adresse :</strong> ' . htmlspecialchars($address) . '</p>
                ' . ($timeSlot ? '<p><strong>Créneau :</strong> ' . htmlspecialchars($timeSlot) . '</p>' : '') . '
                <p>Vous serez contacté pour la livraison. Merci !</p>'
            );
        }

        $this->json([
            'success' => true,
            'message' => 'Commande envoyée avec succès !',
            'order_id' => $orderId,
            'total' => $totalAmount + $deliveryFee,
        ]);
    }
}
