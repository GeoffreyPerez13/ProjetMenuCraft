<?php
class Restaurant
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM restaurants WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM restaurants WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $slug): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO restaurants (name, slug) VALUES (:name, :slug)');
        $stmt->execute([':name' => $name, ':slug' => $slug]);
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
        $sql = 'UPDATE restaurants SET ' . implode(', ', $sets) . ' WHERE id = :id';
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM restaurants WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getAllOnline(): array
    {
        $sql = 'SELECT r.*, a.id as admin_id FROM restaurants r
                JOIN admins a ON a.restaurant_id = r.id
                JOIN admin_options ao ON ao.admin_id = a.id AND ao.option_name = "site_online" AND ao.option_value = "1"
                ORDER BY r.name';
        return $this->pdo->query($sql)->fetchAll();
    }

    public static function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[àáâãäå]/u', 'a', $text);
        $text = preg_replace('/[èéêë]/u', 'e', $text);
        $text = preg_replace('/[ìíîï]/u', 'i', $text);
        $text = preg_replace('/[òóôõö]/u', 'o', $text);
        $text = preg_replace('/[ùúûü]/u', 'u', $text);
        $text = preg_replace('/[ç]/u', 'c', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
