<?php
class Reservation
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservations WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getByAdmin(int $adminId, ?string $status = null, ?string $date = null): array
    {
        $sql = 'SELECT * FROM reservations WHERE admin_id = :aid';
        $params = [':aid' => $adminId];

        if ($status) {
            $sql .= ' AND status = :status';
            $params[':status'] = $status;
        }
        if ($date) {
            $sql .= ' AND reservation_date = :date';
            $params[':date'] = $date;
        }
        $sql .= ' ORDER BY reservation_date DESC, reservation_time DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservations (admin_id, customer_name, customer_phone, customer_email,
             reservation_date, reservation_time, party_size, special_requests, status)
             VALUES (:aid, :name, :phone, :email, :date, :time, :size, :requests, "pending")'
        );
        $stmt->execute([
            ':aid' => $data['admin_id'],
            ':name' => $data['customer_name'],
            ':phone' => $data['customer_phone'] ?? null,
            ':email' => $data['customer_email'] ?? null,
            ':date' => $data['reservation_date'],
            ':time' => $data['reservation_time'],
            ':size' => $data['party_size'] ?? 2,
            ':requests' => $data['special_requests'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?int $tableId = null): bool
    {
        if ($tableId !== null) {
            $stmt = $this->pdo->prepare('UPDATE reservations SET status = :s, table_id = :tid WHERE id = :id');
            return $stmt->execute([':s' => $status, ':tid' => $tableId, ':id' => $id]);
        }
        $stmt = $this->pdo->prepare('UPDATE reservations SET status = :s WHERE id = :id');
        return $stmt->execute([':s' => $status, ':id' => $id]);
    }

    public function assignTable(int $id, ?int $tableId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE reservations SET table_id = :tid WHERE id = :id');
        return $stmt->execute([':tid' => $tableId, ':id' => $id]);
    }

    public function getPendingCount(int $adminId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE admin_id = :aid AND status = "pending"'
        );
        $stmt->execute([':aid' => $adminId]);
        return (int)$stmt->fetchColumn();
    }

    public function getTodayCount(int $adminId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE admin_id = :aid AND reservation_date = CURDATE()'
        );
        $stmt->execute([':aid' => $adminId]);
        return (int)$stmt->fetchColumn();
    }

    public function getConfirmedCount(int $adminId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM reservations WHERE admin_id = :aid AND status = "confirmed"'
        );
        $stmt->execute([':aid' => $adminId]);
        return (int)$stmt->fetchColumn();
    }
}
