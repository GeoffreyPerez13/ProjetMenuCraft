<?php
class DemoToken
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByToken(string $token): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM demo_tokens WHERE token = :t LIMIT 1');
        $stmt->execute([':t' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function isValid(string $token): bool
    {
        $demo = $this->findByToken($token);
        if (!$demo) return false;
        return strtotime($demo->expires_at) > time();
    }

    public function create(array $data): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->pdo->prepare(
            'INSERT INTO demo_tokens (token, admin_id, clone_admin_id, clone_restaurant_id, label, expires_at, created_by)
             VALUES (:token, :aid, :caid, :crid, :label, :exp, :by)'
        );
        $stmt->execute([
            ':token' => $token,
            ':aid' => $data['admin_id'],
            ':caid' => $data['clone_admin_id'] ?? null,
            ':crid' => $data['clone_restaurant_id'] ?? null,
            ':label' => $data['label'] ?? 'Démo',
            ':exp' => $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+3 days')),
            ':by' => $data['created_by'] ?? null,
        ]);
        return $token;
    }

    public function getActiveTokens(): array
    {
        return $this->pdo->query(
            'SELECT * FROM demo_tokens WHERE expires_at > NOW() ORDER BY created_at DESC'
        )->fetchAll();
    }

    public function cleanExpired(): int
    {
        $expired = $this->pdo->query(
            'SELECT * FROM demo_tokens WHERE expires_at <= NOW()'
        )->fetchAll();

        $count = 0;
        foreach ($expired as $demo) {
            if ($demo->clone_admin_id) {
                $this->cleanClone($demo);
            }
            $this->pdo->prepare('DELETE FROM demo_tokens WHERE id = :id')->execute([':id' => $demo->id]);
            $count++;
        }
        return $count;
    }

    public function cleanClone(object $demo): void
    {
        if ($demo->clone_restaurant_id) {
            $stmt = $this->pdo->prepare('SELECT slug FROM restaurants WHERE id = :id');
            $stmt->execute([':id' => $demo->clone_restaurant_id]);
            $rest = $stmt->fetch();
            // Ne jamais supprimer le restaurant template original
            if ($rest && $rest->slug === 'demo-menucraft') return;
            $this->pdo->prepare('DELETE FROM restaurants WHERE id = :id')
                ->execute([':id' => $demo->clone_restaurant_id]);
        }
        if ($demo->clone_admin_id) {
            $this->pdo->prepare('DELETE FROM admins WHERE id = :id')
                ->execute([':id' => $demo->clone_admin_id]);
        }
    }
}
