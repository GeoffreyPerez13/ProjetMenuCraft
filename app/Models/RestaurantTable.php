<?php
class RestaurantTable
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByFloor(int $floorId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM restaurant_tables WHERE floor_id = :fid');
        $stmt->execute([':fid' => $floorId]);
        return $stmt->fetchAll();
    }

    public function save(int $floorId, array $tables): void
    {
        $this->pdo->prepare('DELETE FROM restaurant_tables WHERE floor_id = :fid')
            ->execute([':fid' => $floorId]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO restaurant_tables (floor_id, table_number, seats, x, y, width, height, shape, rotation)
             VALUES (:fid, :num, :seats, :x, :y, :w, :h, :shape, :rot)'
        );
        foreach ($tables as $t) {
            $stmt->execute([
                ':fid' => $floorId, ':num' => $t['table_number'] ?? '', ':seats' => $t['seats'] ?? 4,
                ':x' => $t['x'] ?? 0, ':y' => $t['y'] ?? 0, ':w' => $t['width'] ?? 80,
                ':h' => $t['height'] ?? 80, ':shape' => $t['shape'] ?? 'square', ':rot' => $t['rotation'] ?? 0,
            ]);
        }
    }
}
