<?php
class CardImage
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByAdmin(int $adminId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM card_images WHERE admin_id = :aid ORDER BY display_order ASC'
        );
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetchAll();
    }

    public function create(int $adminId, string $filename): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO card_images (admin_id, filename, display_order)
             VALUES (:aid, :fn, (SELECT COALESCE(MAX(ci.display_order), -1) + 1 FROM card_images ci WHERE ci.admin_id = :aid2))'
        );
        $stmt->execute([':aid' => $adminId, ':fn' => $filename, ':aid2' => $adminId]);
        return (int)$this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM card_images WHERE id = :id')->execute([':id' => $id]);
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM card_images WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
