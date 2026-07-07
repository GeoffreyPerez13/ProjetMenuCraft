<?php
class Contact
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByAdmin(int $adminId): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contact WHERE admin_id = :aid LIMIT 1');
        $stmt->execute([':aid' => $adminId]);
        return $stmt->fetch() ?: null;
    }

    public function createOrUpdate(int $adminId, array $data): bool
    {
        $existing = $this->findByAdmin($adminId);
        if ($existing) {
            $stmt = $this->pdo->prepare(
                'UPDATE contact SET telephone = :tel, email = :email, adresse = :addr, horaires = :hor WHERE admin_id = :aid'
            );
        } else {
            $stmt = $this->pdo->prepare(
                'INSERT INTO contact (admin_id, telephone, email, adresse, horaires) VALUES (:aid, :tel, :email, :addr, :hor)'
            );
        }
        return $stmt->execute([
            ':aid' => $adminId,
            ':tel' => $data['telephone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':addr' => $data['adresse'] ?? null,
            ':hor' => $data['horaires'] ?? null,
        ]);
    }
}
