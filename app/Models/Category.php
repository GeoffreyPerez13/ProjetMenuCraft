<?php
class Category
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getByAdmin(int $adminId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM categories WHERE admin_id = :aid ORDER BY display_order ASC, id ASC'
        );
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO categories (admin_id, name, description, image, display_order)
             VALUES (:admin_id, :name, :description, :image, :display_order)'
        );
        $stmt->execute([
            ':admin_id' => $data['admin_id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':image' => $data['image'] ?? null,
            ':display_order' => $data['display_order'] ?? 0,
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
        $sql = 'UPDATE categories SET ' . implode(', ', $sets) . ' WHERE id = :id';
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function reorder(array $orderedIds): void
    {
        $stmt = $this->pdo->prepare('UPDATE categories SET display_order = :order WHERE id = :id');
        foreach ($orderedIds as $order => $id) {
            $stmt->execute([':order' => $order, ':id' => $id]);
        }
    }

    public function getNextOrder(int $adminId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(MAX(display_order), -1) + 1 FROM categories WHERE admin_id = :aid'
        );
        $stmt->execute([':aid' => $adminId]);
        return (int)$stmt->fetchColumn();
    }
}
