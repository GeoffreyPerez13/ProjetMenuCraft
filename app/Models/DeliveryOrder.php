<?php
class DeliveryOrder
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM delivery_orders WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getByAdmin(int $adminId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM delivery_orders WHERE admin_id = :aid';
        $params = [':aid' => $adminId];
        if ($status) {
            $sql .= ' AND status = :status';
            $params[':status'] = $status;
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO delivery_orders (admin_id, customer_name, customer_phone, customer_email,
             delivery_address, delivery_time_slot, delivery_notes, total_amount, delivery_fee, status)
             VALUES (:aid, :name, :phone, :email, :address, :timeslot, :notes, :total, :fee, :status)'
        );
        $stmt->execute([
            ':aid' => $data['admin_id'],
            ':name' => $data['customer_name'],
            ':phone' => $data['customer_phone'],
            ':email' => $data['customer_email'] ?? null,
            ':address' => $data['delivery_address'],
            ':timeslot' => $data['delivery_time_slot'] ?? null,
            ':notes' => $data['delivery_notes'] ?? null,
            ':total' => $data['total_amount'],
            ':fee' => $data['delivery_fee'] ?? 0,
            ':status' => $data['status'] ?? 'new',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function addItem(int $orderId, array $item): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO delivery_items (order_id, dish_id, dish_name, quantity, unit_price)
             VALUES (:oid, :did, :name, :qty, :price)'
        );
        $stmt->execute([
            ':oid' => $orderId,
            ':did' => $item['dish_id'] ?? null,
            ':name' => $item['dish_name'],
            ':qty' => $item['quantity'] ?? 1,
            ':price' => $item['unit_price'],
        ]);
    }

    public function getItems(int $orderId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM delivery_items WHERE order_id = :oid');
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE delivery_orders SET status = :s WHERE id = :id');
        return $stmt->execute([':s' => $status, ':id' => $id]);
    }

    public function updateNotes(int $id, ?string $notes): bool
    {
        $stmt = $this->pdo->prepare('UPDATE delivery_orders SET admin_notes = :n WHERE id = :id');
        return $stmt->execute([':n' => $notes, ':id' => $id]);
    }

    public function getNewCount(int $adminId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM delivery_orders WHERE admin_id = :aid AND status = "new"');
        $stmt->execute([':aid' => $adminId]);
        return (int)$stmt->fetchColumn();
    }

    public function getTodayCount(int $adminId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM delivery_orders WHERE admin_id = :aid AND DATE(created_at) = CURDATE()');
        $stmt->execute([':aid' => $adminId]);
        return (int)$stmt->fetchColumn();
    }

    public function getTodayRevenue(int $adminId): float
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(total_amount + delivery_fee), 0) FROM delivery_orders WHERE admin_id = :aid AND DATE(created_at) = CURDATE() AND status != "cancelled"');
        $stmt->execute([':aid' => $adminId]);
        return (float)$stmt->fetchColumn();
    }
}
