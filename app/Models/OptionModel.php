<?php
class OptionModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get(int $adminId, string $name, string $default = ''): string
    {
        $stmt = $this->pdo->prepare(
            'SELECT option_value FROM admin_options WHERE admin_id = :aid AND option_name = :name LIMIT 1'
        );
        $stmt->execute([':aid' => $adminId, ':name' => $name]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public function set(int $adminId, string $name, string $value): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admin_options (admin_id, option_name, option_value) VALUES (:aid, :name, :val)
             ON DUPLICATE KEY UPDATE option_value = :val2'
        );
        return $stmt->execute([':aid' => $adminId, ':name' => $name, ':val' => $value, ':val2' => $value]);
    }

    public function getAll(int $adminId): array
    {
        $stmt = $this->pdo->prepare('SELECT option_name, option_value FROM admin_options WHERE admin_id = :aid');
        $stmt->execute([':aid' => $adminId]);
        $options = [];
        while ($row = $stmt->fetch()) {
            $options[$row->option_name] = $row->option_value;
        }
        return $options;
    }

    public function delete(int $adminId, string $name): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM admin_options WHERE admin_id = :aid AND option_name = :name'
        );
        return $stmt->execute([':aid' => $adminId, ':name' => $name]);
    }

    public function deleteAll(int $adminId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM admin_options WHERE admin_id = :aid');
        return $stmt->execute([':aid' => $adminId]);
    }
}
