<?php
class RestaurantElement
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByFloor(int $floorId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM restaurant_elements WHERE floor_id = :fid');
        $stmt->execute([':fid' => $floorId]);
        return $stmt->fetchAll();
    }

    public function save(int $floorId, array $elements): void
    {
        $this->pdo->prepare('DELETE FROM restaurant_elements WHERE floor_id = :fid')
            ->execute([':fid' => $floorId]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO restaurant_elements (floor_id, element_type, x, y, width, height, rotation)
             VALUES (:fid, :type, :x, :y, :w, :h, :rot)'
        );
        foreach ($elements as $e) {
            $stmt->execute([
                ':fid' => $floorId, ':type' => $e['element_type'] ?? 'bar',
                ':x' => $e['x'] ?? 0, ':y' => $e['y'] ?? 0,
                ':w' => $e['width'] ?? 100, ':h' => $e['height'] ?? 60, ':rot' => $e['rotation'] ?? 0,
            ]);
        }
    }
}
