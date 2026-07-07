<?php
class Dish
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM plats WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getByCategory(int $categoryId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM plats WHERE category_id = :cid ORDER BY display_order ASC, id ASC'
        );
        $stmt->execute([':cid' => $categoryId]);
        return $stmt->fetchAll();
    }

    public function getByAdmin(int $adminId): array
    {
        $sql = 'SELECT p.*, c.name as category_name FROM plats p
                JOIN categories c ON c.id = p.category_id
                WHERE c.admin_id = :aid
                ORDER BY c.display_order ASC, p.display_order ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO plats (category_id, name, description, price, image, display_order, is_active)
             VALUES (:category_id, :name, :description, :price, :image, :display_order, :is_active)'
        );
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':image' => $data['image'] ?? null,
            ':display_order' => $data['display_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }
        $sql = 'UPDATE plats SET ' . implode(', ', $sets) . ' WHERE id = :id';
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM plats WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function reorder(array $orderedIds): void
    {
        $stmt = $this->pdo->prepare('UPDATE plats SET display_order = :order WHERE id = :id');
        foreach ($orderedIds as $order => $id) {
            $stmt->execute([':order' => $order, ':id' => $id]);
        }
    }

    public function getAllergenes(int $platId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.* FROM allergenes a
             JOIN plat_allergenes pa ON pa.allergene_id = a.id
             WHERE pa.plat_id = :pid'
        );
        $stmt->execute([':pid' => $platId]);
        return $stmt->fetchAll();
    }

    public function syncAllergenes(int $platId, array $allergeneIds): void
    {
        $this->pdo->prepare('DELETE FROM plat_allergenes WHERE plat_id = :pid')
            ->execute([':pid' => $platId]);

        if (!empty($allergeneIds)) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO plat_allergenes (plat_id, allergene_id) VALUES (:pid, :aid)'
            );
            foreach ($allergeneIds as $aid) {
                $stmt->execute([':pid' => $platId, ':aid' => (int)$aid]);
            }
        }
    }
}
