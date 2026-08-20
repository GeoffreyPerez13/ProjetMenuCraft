<?php
/**
 * BaseController — Classe parent de tous les contrôleurs
 * Fournit : PDO, CSRF, flash messages, rendu, headers sécurité, mode démo
 */
class BaseController
{
    protected PDO $pdo;
    protected int $pendingReservationsCount = 0;
    private ?object $currentAdminCache = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->setSecurityHeaders();
    }

    // ─── CSRF ───────────────────────────────────────────
    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->flash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            $referer = $_SERVER['HTTP_REFERER'] ?? (rtrim(SITE_URL, '/') . '/');
            header('Location: ' . $referer);
            exit;
        }
    }

    protected function verifyCsrfAjax(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->json(['error' => 'Token de sécurité invalide.'], 403);
        }
    }

    // ─── Flash Messages ─────────────────────────────────
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    // ─── Rendu ──────────────────────────────────────────
    protected function render(string $view, array $data = []): void
    {
        $data['csrf_token'] = $this->getCsrfToken();
        $data['flash'] = $this->getFlash();
        $this->loadPendingReservations();
        $data['pendingReservationsCount'] = $this->pendingReservationsCount;
        $data['isDemo'] = $_SESSION['demo_mode'] ?? false;
        $data['currentAdmin'] = $this->getCurrentAdmin();
        $data['pdo'] = $this->pdo;

        // Load active announcements for the header banner
        if (!isset($data['activeAnnouncements'])) {
            try {
                $data['activeAnnouncements'] = $this->pdo->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();
            } catch (PDOException $e) {
                $data['activeAnnouncements'] = [];
            }
        }

        // Load hide_tour_button option for footer
        if (!isset($data['_hideTourButton'])) {
            $adminId = $_SESSION['admin_id'] ?? null;
            if ($adminId) {
                $optModel = new OptionModel($this->pdo);
                $data['_hideTourButton'] = ($optModel->get($adminId, 'hide_tour_button') ?? '0') === '1';
            } else {
                $data['_hideTourButton'] = true;
            }
        }

        extract($data);
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            http_response_code(404);
            echo "Vue introuvable : $view";
        }
    }

    // ─── Sécurité Headers ───────────────────────────────
    private function setSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    // ─── Mode Démo ──────────────────────────────────────
    protected function blockIfDemo(): void
    {
        if (!empty($_SESSION['demo_mode'])) {
            $this->flash('error', 'Cette action est désactivée en mode démonstration.');
            header('Location: ' . rtrim(SITE_URL, '/') . '/?page=dashboard');
            exit;
        }
    }

    // ─── Auth Helpers ───────────────────────────────────
    protected function requireAuth(): void
    {
        if (empty($_SESSION['admin_logged'])) {
            header('Location: ' . rtrim(SITE_URL, '/') . '/?page=login');
            exit;
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        $admin = $this->getCurrentAdmin();
        if (!$admin || $admin->role !== $role) {
            $this->flash('error', 'Accès non autorisé.');
            header('Location: ' . rtrim(SITE_URL, '/') . '/?page=dashboard');
            exit;
        }
    }

    protected function requireSuperAdmin(): void
    {
        $this->requireRole('SUPER_ADMIN');
    }

    protected function getCurrentAdmin(): ?object
    {
        if (empty($_SESSION['admin_id'])) return null;
        if ($this->currentAdminCache !== null) return $this->currentAdminCache;
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $this->currentAdminCache = $stmt->fetch() ?: null;
        return $this->currentAdminCache;
    }

    protected function getAdminId(): int
    {
        return (int)($_SESSION['admin_id'] ?? 0);
    }

    protected function isSuperAdmin(): bool
    {
        $admin = $this->getCurrentAdmin();
        return $admin && $admin->role === 'SUPER_ADMIN';
    }

    // ─── Réservations en attente ────────────────────────
    private function loadPendingReservations(): void
    {
        if (!empty($_SESSION['admin_id'])) {
            try {
                $stmt = $this->pdo->prepare(
                    'SELECT COUNT(*) FROM reservations WHERE admin_id = :id AND status = "pending"'
                );
                $stmt->execute([':id' => $_SESSION['admin_id']]);
                $this->pendingReservationsCount = (int)$stmt->fetchColumn();
            } catch (PDOException $e) {
                $this->pendingReservationsCount = 0;
            }
        }
    }

    // ─── Upload sécurisé ──────────────────────────────────
    protected function handleUpload(array $file, string $subfolder, int $maxWidth = 1200): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > 5 * 1024 * 1024) return null;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realType = $finfo->file($file['tmp_name']);

        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        if (!isset($extMap[$realType])) return null;

        $dir = BASE_PATH . '/public/uploads/' . $subfolder . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = $extMap[$realType];
        $filename = $subfolder . '/' . uniqid() . '_' . time() . '.' . $ext;
        $destPath = BASE_PATH . '/public/uploads/' . $filename;
        move_uploaded_file($file['tmp_name'], $destPath);

        // Resize si nécessaire (GD)
        if ($maxWidth > 0 && extension_loaded('gd')) {
            $this->resizeImage($destPath, $realType, $maxWidth);
        }

        return $filename;
    }

    private function resizeImage(string $path, string $mimeType, int $maxWidth): void
    {
        $info = @getimagesize($path);
        if (!$info || $info[0] <= $maxWidth) return;

        [$origW, $origH] = $info;
        $ratio = $maxWidth / $origW;
        $newW = $maxWidth;
        $newH = (int)round($origH * $ratio);

        $src = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif'  => @imagecreatefromgif($path),
            default      => null,
        };
        if (!$src) return;

        $dst = imagecreatetruecolor($newW, $newH);

        // Préserver la transparence pour PNG/WebP/GIF
        if (in_array($mimeType, ['image/png', 'image/webp', 'image/gif'])) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        match ($mimeType) {
            'image/jpeg' => imagejpeg($dst, $path, 82),
            'image/png'  => imagepng($dst, $path, 8),
            'image/webp' => imagewebp($dst, $path, 82),
            'image/gif'  => imagegif($dst, $path),
            default      => null,
        };

        imagedestroy($src);
        imagedestroy($dst);
    }

    // ─── JSON Response ──────────────────────────────────
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── Redirect ───────────────────────────────────────
    protected function redirect(string $page, array $params = []): void
    {
        $base = rtrim(SITE_URL, '/') . '/';
        $url = $base . '?page=' . $page;
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        header('Location: ' . $url);
        exit;
    }
}
