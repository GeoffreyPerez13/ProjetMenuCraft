<?php
class PremiumFeature
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function isEnabled(PDO $pdo, int $adminId, string $featureName): bool
    {
        if (defined('BETA_MODE') && BETA_MODE) return true;

        $admin = $pdo->prepare('SELECT role FROM admins WHERE id = :id');
        $admin->execute([':id' => $adminId]);
        $a = $admin->fetch();
        if ($a && $a->role === 'SUPER_ADMIN') return true;

        $stmt = $pdo->prepare(
            'SELECT * FROM premium_features WHERE admin_id = :aid AND feature_name = :fn LIMIT 1'
        );
        $stmt->execute([':aid' => $adminId, ':fn' => $featureName]);
        $feature = $stmt->fetch();
        if (!$feature || !$feature->is_active) return false;
        if ($feature->expires_at && strtotime($feature->expires_at) < time()) return false;
        return true;
    }

    public function getByAdmin(int $adminId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM premium_features WHERE admin_id = :aid');
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetchAll();
    }

    public function activate(int $adminId, string $featureName): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO premium_features (admin_id, feature_name, is_active, activated_at)
             VALUES (:aid, :fn, 1, NOW())
             ON DUPLICATE KEY UPDATE is_active = 1, activated_at = NOW(), cancelled_at = NULL'
        );
        return $stmt->execute([':aid' => $adminId, ':fn' => $featureName]);
    }

    public function deactivate(int $adminId, string $featureName): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE premium_features SET is_active = 0, cancelled_at = NOW()
             WHERE admin_id = :aid AND feature_name = :fn'
        );
        return $stmt->execute([':aid' => $adminId, ':fn' => $featureName]);
    }

    public function activateAll(int $adminId): void
    {
        $features = ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'];
        foreach ($features as $f) {
            $this->activate($adminId, $f);
        }
    }
}
