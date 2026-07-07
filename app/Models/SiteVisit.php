<?php
class SiteVisit
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function track(int $adminId, string $pagePath = ''): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $hash = hash('sha256', $ip . $ua);

        // Anti-spam : max 1 visite par visiteur par minute
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM site_visits WHERE admin_id = :aid AND visitor_hash = :hash
             AND visited_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)'
        );
        $stmt->execute([':aid' => $adminId, ':hash' => $hash]);
        if ((int)$stmt->fetchColumn() > 0) return;

        $device = $this->detectDevice($ua);
        $browser = $this->detectBrowser($ua);
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        $stmt = $this->pdo->prepare(
            'INSERT INTO site_visits (admin_id, visitor_hash, user_agent, referrer, device_type, browser, page_path)
             VALUES (:aid, :hash, :ua, :ref, :device, :browser, :path)'
        );
        $stmt->execute([
            ':aid' => $adminId, ':hash' => $hash, ':ua' => substr($ua, 0, 512),
            ':ref' => substr($referrer, 0, 1024), ':device' => $device,
            ':browser' => $browser, ':path' => $pagePath,
        ]);
    }

    public function getStats(int $adminId, int $days = 30): array
    {
        $from = date('Y-m-d', strtotime("-{$days} days"));

        $total = $this->pdo->prepare(
            'SELECT COUNT(*) FROM site_visits WHERE admin_id = :aid AND visited_at >= :from'
        );
        $total->execute([':aid' => $adminId, ':from' => $from]);

        $unique = $this->pdo->prepare(
            'SELECT COUNT(DISTINCT visitor_hash) FROM site_visits WHERE admin_id = :aid AND visited_at >= :from'
        );
        $unique->execute([':aid' => $adminId, ':from' => $from]);

        $byDay = $this->pdo->prepare(
            'SELECT DATE(visited_at) as day, COUNT(*) as count FROM site_visits
             WHERE admin_id = :aid AND visited_at >= :from GROUP BY DATE(visited_at) ORDER BY day'
        );
        $byDay->execute([':aid' => $adminId, ':from' => $from]);

        $byDevice = $this->pdo->prepare(
            'SELECT device_type, COUNT(*) as count FROM site_visits
             WHERE admin_id = :aid AND visited_at >= :from GROUP BY device_type'
        );
        $byDevice->execute([':aid' => $adminId, ':from' => $from]);

        $byBrowser = $this->pdo->prepare(
            'SELECT browser, COUNT(*) as count FROM site_visits
             WHERE admin_id = :aid AND visited_at >= :from GROUP BY browser ORDER BY count DESC LIMIT 10'
        );
        $byBrowser->execute([':aid' => $adminId, ':from' => $from]);

        // Transformer byDay en format [{date, count}]
        $daily = [];
        foreach ($byDay->fetchAll() as $row) {
            $daily[] = ['date' => $row->day, 'count' => (int)$row->count];
        }

        // Transformer byDevice en map {type: count}
        $devices = [];
        foreach ($byDevice->fetchAll() as $row) {
            $devices[$row->device_type ?: 'unknown'] = (int)$row->count;
        }

        // Transformer byBrowser en map {name: count}
        $browsers = [];
        foreach ($byBrowser->fetchAll() as $row) {
            $browsers[$row->browser ?: 'unknown'] = (int)$row->count;
        }

        return [
            'total_visits' => (int)$total->fetchColumn(),
            'unique_visitors' => (int)$unique->fetchColumn(),
            'daily' => $daily,
            'devices' => $devices,
            'browsers' => $browsers,
        ];
    }

    private function detectDevice(string $ua): string
    {
        if (preg_match('/mobile|android|iphone|ipod/i', $ua)) return 'mobile';
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Edg')) return 'Edge';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Opera') || str_contains($ua, 'OPR')) return 'Opera';
        return 'Autre';
    }
}
