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
        $maxOrder = $this->pdo->prepare('SELECT COALESCE(MAX(display_order),0) FROM floors WHERE admin_id = :aid');
        $maxOrder->execute([':aid' => $adminId]);
        $order = (int)$maxOrder->fetchColumn() + 1;

        $stmt = $this->pdo->prepare('INSERT INTO floors (admin_id, name, display_order) VALUES (:aid, :name, :ord)');
        $stmt->execute([':aid' => $adminId, ':name' => $name, ':ord' => $order]);
        return (int)$this->pdo->lastInsertId();
    }

    public function rename(int $id, string $name): bool
    {
        return $this->pdo->prepare('UPDATE floors SET name = :name WHERE id = :id')
            ->execute([':name' => $name, ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $this->pdo->prepare('DELETE FROM restaurant_tables WHERE floor_id = :fid')->execute([':fid' => $id]);
        $this->pdo->prepare('DELETE FROM restaurant_elements WHERE floor_id = :fid')->execute([':fid' => $id]);
        return $this->pdo->prepare('DELETE FROM floors WHERE id = :id')->execute([':id' => $id]);
    }
}
