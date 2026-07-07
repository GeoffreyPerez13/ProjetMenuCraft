<?php
class Floor
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByAdmin(int $adminId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM floors WHERE admin_id = :aid ORDER BY display_order');
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM floors WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $adminId, string $name = 'Salle principale'): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO floors (admin_id, name) VALUES (:aid, :name)');
        $stmt->execute([':aid' => $adminId, ':name' => $name]);
        return (int)$this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM floors WHERE id = :id')->execute([':id' => $id]);
    }
}
