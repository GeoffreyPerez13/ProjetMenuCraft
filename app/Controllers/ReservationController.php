<?php
class ReservationController extends BaseController
{
    public function list(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        if (!PremiumFeature::isEnabled($this->pdo, $adminId, 'online_booking')) {
            $this->flash('error', 'Fonctionnalité premium requise.');
            $this->redirect('settings', ['section' => 'premium']);
            return;
        }

        $resModel = new Reservation($this->pdo);
        $status = $_GET['status'] ?? null;
        $date = $_GET['date'] ?? null;

        // Load floor plan tables for table assignment
        $floorModel = new Floor($this->pdo);
        $floors = $floorModel->getByAdmin($adminId);
        $allTables = [];
        $tableModel = new RestaurantTable($this->pdo);
        foreach ($floors as $floor) {
            $tables = $tableModel->getByFloor($floor->id);
            foreach ($tables as $t) {
                $allTables[] = (object)[
                    'id' => $t->id,
                    'table_number' => $t->table_number,
                    'name' => $t->name ?? '',
                    'seats' => $t->seats,
                    'floor_name' => $floor->name,
                ];
            }
        }

        $this->render('admin/reservations', [
            'pageTitle' => 'Réservations — MenuCraft',
            'reservations' => $resModel->getByAdmin($adminId, $status, $date),
            'pendingCount' => $resModel->getPendingCount($adminId),
            'todayCount' => $resModel->getTodayCount($adminId),
            'confirmedCount' => $resModel->getConfirmedCount($adminId),
            'filterStatus' => $status,
            'filterDate' => $date,
            'floorTables' => $allTables,
        ]);
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $id = (int)($_POST['reservation_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $validStatuses = ['confirmed', 'rejected', 'completed', 'cancelled', 'no_show'];

        if (!in_array($status, $validStatuses)) {
            $this->flash('error', 'Statut invalide.');
            $this->redirect('reservations');
            return;
        }

        $resModel = new Reservation($this->pdo);
        $reservation = $resModel->findById($id);

        if (!$reservation || $reservation->admin_id !== $adminId) {
            $this->flash('error', 'Réservation introuvable.');
            $this->redirect('reservations');
            return;
        }

        $tableId = !empty($_POST['table_id']) ? (int)$_POST['table_id'] : null;
        $resModel->updateStatus($id, $status, $tableId);

        // Notification email
        $notifService = new NotificationService($this->pdo);
        $notifService->notifyReservationStatus([
            'customer_email' => $reservation->customer_email,
            'reservation_date' => $reservation->reservation_date,
            'reservation_time' => $reservation->reservation_time,
            'party_size' => $reservation->party_size,
        ], $status);

        $this->flash('success', 'Réservation mise à jour.');
        $this->redirect('reservations');
    }

    public function publicBooking(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
            return;
        }

        $rateLimiter = new RateLimiter();
        if ($rateLimiter->isLimited('booking', 10, 3600)) {
            $this->json(['error' => 'Trop de demandes. Réessayez plus tard.'], 429);
            return;
        }

        $adminId = (int)($_POST['admin_id'] ?? 0);
        if (!$adminId || !PremiumFeature::isEnabled($this->pdo, $adminId, 'online_booking')) {
            $this->json(['error' => 'Réservations non disponibles.'], 400);
            return;
        }

        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $date = $_POST['reservation_date'] ?? '';
        $time = $_POST['reservation_time'] ?? '';
        $size = (int)($_POST['party_size'] ?? 2);

        if (empty($name) || empty($date) || empty($time)) {
            $this->json(['error' => 'Champs requis manquants.'], 400);
            return;
        }

        $optModel = new OptionModel($this->pdo);

        // Block closure dates
        $closureDates = json_decode($optModel->get($adminId, 'closure_dates', '[]'), true) ?: [];
        if (in_array($date, $closureDates)) {
            $this->json(['error' => 'Le restaurant est fermé à cette date. Veuillez choisir une autre date.'], 400);
            return;
        }

        $autoConfirm = ($optModel->get($adminId, 'booking_auto_confirm', '0') === '1');

        $resModel = new Reservation($this->pdo);
        $resModel->create([
            'admin_id' => $adminId,
            'customer_name' => $name,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'reservation_date' => $date,
            'reservation_time' => $time,
            'party_size' => $size,
            'special_requests' => trim($_POST['special_requests'] ?? ''),
            'status' => $autoConfirm ? 'confirmed' : 'pending',
        ]);

        $rateLimiter->hit('booking');

        // Notifications au restaurateur
        $notifService = new NotificationService($this->pdo);
        $notifService->notifyNewReservation($adminId, [
            'customer_name' => $name,
            'reservation_date' => $date,
            'reservation_time' => $time,
            'party_size' => $size,
            'special_requests' => $_POST['special_requests'] ?? '',
        ]);

        // Email au client
        if ($email) {
            $mailer = new Mailer();
            if ($autoConfirm) {
                $mailer->send($email, 'Réservation confirmée — MenuCraft',
                    '<h2>Votre réservation est confirmée !</h2>
                    <p>Nous avons le plaisir de vous confirmer votre réservation.</p>
                    <p><strong>Date :</strong> ' . $date . ' à ' . $time . '</p>
                    <p><strong>Personnes :</strong> ' . $size . '</p>
                    <p>À bientôt !</p>'
                );
            } else {
                $mailer->send($email, 'Demande de réservation reçue — MenuCraft',
                    '<h2>Réservation reçue !</h2>
                    <p>Votre demande de réservation a bien été enregistrée.</p>
                    <p><strong>Date :</strong> ' . $date . ' à ' . $time . '</p>
                    <p><strong>Personnes :</strong> ' . $size . '</p>
                    <p>Vous recevrez un email de confirmation sous peu.</p>'
                );
            }
        }

        $message = $autoConfirm ? 'Réservation confirmée !' : 'Réservation envoyée avec succès !';
        $this->json(['success' => true, 'message' => $message]);
    }

    public function pendingCount(): void
    {
        if (empty($_SESSION['admin_logged'])) {
            $this->json(['count' => 0]);
            return;
        }
        $adminId = $this->getAdminId();
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM reservations WHERE admin_id = :id AND status = "pending"');
        $stmt->execute([':id' => $adminId]);
        $count = (int)$stmt->fetchColumn();
        $this->json(['count' => $count]);
    }

    public function pendingList(): void
    {
        if (empty($_SESSION['admin_logged'])) {
            $this->json(['reservations' => [], 'tables' => []]);
            return;
        }
        $adminId = $this->getAdminId();

        $stmt = $this->pdo->prepare(
            'SELECT id, customer_name, customer_phone, customer_email, reservation_date, reservation_time, party_size, special_requests, created_at
             FROM reservations WHERE admin_id = :id AND status = "pending" ORDER BY created_at DESC'
        );
        $stmt->execute([':id' => $adminId]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $floorModel = new Floor($this->pdo);
        $floors = $floorModel->getByAdmin($adminId);
        $tables = [];
        $tableModel = new RestaurantTable($this->pdo);
        foreach ($floors as $floor) {
            $floorTables = $tableModel->getByFloor($floor->id);
            foreach ($floorTables as $t) {
                $tables[] = [
                    'id' => $t->id,
                    'table_number' => $t->table_number,
                    'name' => $t->name ?? '',
                    'seats' => $t->seats,
                    'floor_name' => $floor->name,
                ];
            }
        }

        $this->json(['reservations' => $reservations, 'tables' => $tables]);
    }
}
