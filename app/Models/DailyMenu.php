<?php
class DailyMenu
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByAdmin(int $adminId, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM daily_menus WHERE admin_id = :aid';
        if ($activeOnly) $sql .= ' AND is_active = 1';
        $sql .= ' ORDER BY display_order ASC, id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM daily_menus WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO daily_menus (admin_id, title, description, price, items, display_order, is_active)
             VALUES (:aid, :title, :desc, :price, :items, :display_order, :active)'
        );
        $stmt->execute([
            ':aid' => $data['admin_id'],
            ':title' => $data['title'],
            ':desc' => $data['description'] ?? null,
            ':price' => $data['price'] ?? null,
            ':items' => $data['items'] ?? '[]',
            ':display_order' => $data['display_order'] ?? 0,
            ':active' => $data['is_active'] ?? 1,
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
        $sql = 'UPDATE daily_menus SET ' . implode(', ', $sets) . ' WHERE id = :id';
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM daily_menus WHERE id = :id')->execute([':id' => $id]);
    }

    public function toggle(int $id): bool
    {
        return $this->pdo->prepare('UPDATE daily_menus SET is_active = NOT is_active WHERE id = :id')
            ->execute([':id' => $id]);
    }

    public function reorder(array $orderedIds): void
    {
        $stmt = $this->pdo->prepare('UPDATE daily_menus SET display_order = :order WHERE id = :id');
        foreach ($orderedIds as $order => $id) {
            $stmt->execute([':order' => $order, ':id' => $id]);
        }
    }
}
