<?php
class ClientSubscription
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByAdmin(int $adminId): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM client_subscriptions WHERE admin_id = :aid LIMIT 1');
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $adminId, string $planType = 'basique', string $status = 'inactive'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO client_subscriptions (admin_id, plan_type, status) VALUES (:aid, :plan, :status)
             ON DUPLICATE KEY UPDATE plan_type = :plan2, status = :status2'
        );
        $stmt->execute([
            ':aid' => $adminId, ':plan' => $planType, ':status' => $status,
            ':plan2' => $planType, ':status2' => $status,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function activate(int $adminId, array $data = []): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE client_subscriptions SET status = "active", started_at = NOW(),
             plan_type = :plan, price_per_month = :price, stripe_session_id = :sid
             WHERE admin_id = :aid'
        );
        return $stmt->execute([
            ':aid' => $adminId,
            ':plan' => $data['plan_type'] ?? 'basique',
            ':price' => $data['price_per_month'] ?? 11.99,
            ':sid' => $data['stripe_session_id'] ?? null,
        ]);
    }

    public function deactivate(int $adminId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE client_subscriptions SET status = "inactive" WHERE admin_id = :aid'
        );
        return $stmt->execute([':aid' => $adminId]);
    }

    public function isActive(int $adminId): bool
    {
        $sub = $this->findByAdmin($adminId);
        if (!$sub) return false;
        if (defined('BETA_MODE') && BETA_MODE) return true;
        return $sub->status === 'active';
    }
}
