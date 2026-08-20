<?php
class SuperAdminController extends BaseController
{
    public function loginJournal(): void
    {
        $this->requireSuperAdmin();

        $page = max(1, (int)($_GET['p'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $filterIp = trim($_GET['filter_ip'] ?? '');
        $filterUser = trim($_GET['filter_user'] ?? '');
        $filterStatus = $_GET['filter_status'] ?? '';

        $where = '1=1';
        $params = [];

        if ($filterIp) {
            $where .= ' AND la.ip_address LIKE :ip';
            $params[':ip'] = '%' . $filterIp . '%';
        }
        if ($filterUser) {
            $where .= ' AND la.username LIKE :user';
            $params[':user'] = '%' . $filterUser . '%';
        }
        if ($filterStatus === 'success') {
            $where .= ' AND la.success = 1';
        } elseif ($filterStatus === 'failed') {
            $where .= ' AND la.success = 0';
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM login_attempts la WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->pdo->prepare(
            "SELECT la.*, a.restaurant_name 
             FROM login_attempts la 
             LEFT JOIN admins a ON a.username = la.username
             WHERE $where 
             ORDER BY la.attempted_at DESC 
             LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $attempts = $stmt->fetchAll();

        $totalPages = max(1, ceil($total / $perPage));

        $this->render('admin/login-journal', [
            'pageTitle' => 'Journal des connexions — MenuCraft',
            'attempts' => $attempts,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filterIp' => $filterIp,
            'filterUser' => $filterUser,
            'filterStatus' => $filterStatus,
        ]);
    }

    public function suspendClient(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrfToken();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($clientId) {
            $stmt = $this->pdo->prepare('UPDATE admins SET suspended = 1, suspended_reason = :reason WHERE id = :id AND role = "ADMIN"');
            $stmt->execute([':reason' => $reason ?: null, ':id' => $clientId]);

            // Send email notification
            $admin = (new Admin($this->pdo))->findById($clientId);
            if ($admin && $admin->email) {
                $mailer = new Mailer();
                $mailer->send($admin->email, 'Compte suspendu — MenuCraft',
                    '<h2 style="color:#dc2626;">Votre compte a été suspendu</h2>
                    <p>Votre compte MenuCraft a été temporairement suspendu par l\'administrateur.</p>' .
                    ($reason ? '<p><strong>Raison :</strong> ' . htmlspecialchars($reason) . '</p>' : '') .
                    '<p>Si vous pensez qu\'il s\'agit d\'une erreur, contactez-nous.</p>'
                );
            }

            $this->flash('success', 'Compte suspendu.');
        }
        $this->redirect('manage-clients');
    }

    public function unsuspendClient(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrfToken();

        $clientId = (int)($_POST['client_id'] ?? 0);
        if ($clientId) {
            $stmt = $this->pdo->prepare('UPDATE admins SET suspended = 0, suspended_reason = NULL WHERE id = :id');
            $stmt->execute([':id' => $clientId]);
            $this->flash('success', 'Compte réactivé.');
        }
        $this->redirect('manage-clients');
    }

    public function deleteClient(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrfToken();

        $clientId = (int)($_POST['client_id'] ?? 0);
        if ($clientId) {
            $admin = (new Admin($this->pdo))->findById($clientId);
            if ($admin && $admin->role === 'ADMIN') {
                $this->pdo->beginTransaction();
                try {
                    // Delete related data
                    $tables = ['categories', 'contact', 'daily_menus', 'reservations', 'admin_options',
                               'premium_features', 'client_subscriptions', 'site_visits', 'feedbacks'];
                    foreach ($tables as $table) {
                        $this->pdo->prepare("DELETE FROM `$table` WHERE admin_id = :id")->execute([':id' => $clientId]);
                    }
                    // Delete restaurant
                    if ($admin->restaurant_id) {
                        $this->pdo->prepare('DELETE FROM restaurants WHERE id = :id')->execute([':id' => $admin->restaurant_id]);
                    }
                    // Delete admin
                    $this->pdo->prepare('DELETE FROM admins WHERE id = :id')->execute([':id' => $clientId]);
                    $this->pdo->commit();
                    $this->flash('success', 'Compte et données supprimés.');
                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    $this->flash('error', 'Erreur lors de la suppression. Veuillez réessayer.');
                }
            }
        }
        $this->redirect('manage-clients');
    }

    public function impersonate(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrfToken();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $admin = (new Admin($this->pdo))->findById($clientId);

        if ($admin && $admin->role === 'ADMIN') {
            // Save super admin session to restore later
            $_SESSION['impersonating_from'] = $_SESSION['admin_id'];
            $_SESSION['impersonating_from_username'] = $_SESSION['username'];

            // Switch to client session
            $_SESSION['admin_id'] = $admin->id;
            $_SESSION['admin_name'] = $admin->restaurant_name;
            $_SESSION['username'] = $admin->username;

            $this->flash('info', 'Connecté en tant que ' . htmlspecialchars($admin->username) . '. <a href="' . APP_URL . '?page=stop-impersonate" style="color:inherit;text-decoration:underline;font-weight:600;">Revenir à votre compte</a>');
            $this->redirect('dashboard');
        } else {
            $this->flash('error', 'Client introuvable.');
            $this->redirect('manage-clients');
        }
    }

    public function stopImpersonate(): void
    {
        $this->requireAuth();

        if (!empty($_SESSION['impersonating_from'])) {
            $superAdminId = $_SESSION['impersonating_from'];
            $admin = (new Admin($this->pdo))->findById($superAdminId);

            if ($admin && $admin->role === 'SUPER_ADMIN') {
                $_SESSION['admin_id'] = $admin->id;
                $_SESSION['admin_name'] = $admin->restaurant_name;
                $_SESSION['username'] = $admin->username;
                unset($_SESSION['impersonating_from'], $_SESSION['impersonating_from_username']);

                $this->flash('success', 'Revenu à votre compte super admin.');
                $this->redirect('manage-clients');
                return;
            }
        }

        $this->redirect('dashboard');
    }

    public function globalDashboard(): void
    {
        $this->requireSuperAdmin();

        // Stats
        $totalClients = (int)$this->pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'ADMIN'")->fetchColumn();
        $activeClients = (int)$this->pdo->query("SELECT COUNT(*) FROM client_subscriptions WHERE status = 'active'")->fetchColumn();
        $totalReservationsToday = (int)$this->pdo->query("SELECT COUNT(*) FROM reservations WHERE DATE(reservation_date) = CURDATE()")->fetchColumn();
        $newClientsThisWeek = (int)$this->pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'ADMIN' AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $totalReservationsPending = (int)$this->pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
        $onlineSites = (int)$this->pdo->query("SELECT COUNT(*) FROM admin_options WHERE option_name = 'site_online' AND option_value = '1'")->fetchColumn();

        // Recent activity
        $recentClients = $this->pdo->query(
            "SELECT a.id, a.username, a.restaurant_name, a.last_login_at, a.created_at, a.suspended,
                    cs.status as sub_status, cs.plan_type
             FROM admins a
             LEFT JOIN client_subscriptions cs ON cs.admin_id = a.id
             WHERE a.role = 'ADMIN'
             ORDER BY a.last_login_at DESC, a.created_at DESC
             LIMIT 15"
        )->fetchAll();

        // Recent failed logins
        $recentFailures = (int)$this->pdo->query("SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();

        $this->render('admin/super-dashboard', [
            'pageTitle' => 'Administration globale — MenuCraft',
            'totalClients' => $totalClients,
            'activeClients' => $activeClients,
            'totalReservationsToday' => $totalReservationsToday,
            'newClientsThisWeek' => $newClientsThisWeek,
            'totalReservationsPending' => $totalReservationsPending,
            'onlineSites' => $onlineSites,
            'recentClients' => $recentClients,
            'recentFailures' => $recentFailures,
        ]);
    }

    public function announcements(): void
    {
        $this->requireSuperAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $message = trim($_POST['message'] ?? '');
                $type = $_POST['type'] ?? 'info';
                if ($message) {
                    $stmt = $this->pdo->prepare('INSERT INTO announcements (message, type) VALUES (:msg, :type)');
                    $stmt->execute([':msg' => $message, ':type' => $type]);
                    $this->flash('success', 'Annonce publiée.');
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['announcement_id'] ?? 0);
                $this->pdo->prepare('DELETE FROM announcements WHERE id = :id')->execute([':id' => $id]);
                $this->flash('success', 'Annonce supprimée.');
            } elseif ($action === 'toggle') {
                $id = (int)($_POST['announcement_id'] ?? 0);
                $this->pdo->prepare('UPDATE announcements SET is_active = NOT is_active WHERE id = :id')->execute([':id' => $id]);
                $this->flash('success', 'Statut modifié.');
            }

            $this->redirect('announcements');
            return;
        }

        $announcements = $this->pdo->query('SELECT * FROM announcements ORDER BY created_at DESC')->fetchAll();

        $this->render('admin/announcements', [
            'pageTitle' => 'Annonces globales — MenuCraft',
            'announcements' => $announcements,
        ]);
    }
}
