<?php
class Admin
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?object
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function createAccountDirect(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admins (username, email, password, restaurant_name, restaurant_id, role, email_verified, verification_token)
             VALUES (:username, :email, :password, :restaurant_name, :restaurant_id, :role, :email_verified, :verification_token)'
        );
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':restaurant_name' => $data['restaurant_name'],
            ':restaurant_id' => $data['restaurant_id'],
            ':role' => $data['role'] ?? 'ADMIN',
            ':email_verified' => $data['email_verified'] ?? 0,
            ':verification_token' => $data['verification_token'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function createAccount(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admins (username, email, password, restaurant_name, restaurant_id, role, email_verified)
             VALUES (:username, :email, :password, :restaurant_name, :restaurant_id, "ADMIN", 1)'
        );
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':restaurant_name' => $data['restaurant_name'],
            ':restaurant_id' => $data['restaurant_id'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function verifyEmail(string $token): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM admins WHERE verification_token = :t LIMIT 1');
        $stmt->execute([':t' => $token]);
        $admin = $stmt->fetch();
        if (!$admin) return false;

        $update = $this->pdo->prepare(
            'UPDATE admins SET email_verified = 1, verification_token = NULL WHERE id = :id'
        );
        return $update->execute([':id' => $admin->id]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        $sets = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }
        $sql = 'UPDATE admins SET ' . implode(', ', $sets) . ' WHERE id = :id';
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function updatePassword(int $id, string $password): bool
    {
        $stmt = $this->pdo->prepare('UPDATE admins SET password = :p WHERE id = :id');
        return $stmt->execute([':p' => password_hash($password, PASSWORD_BCRYPT), ':id' => $id]);
    }

    public function getAllClients(): array
    {
        $sql = 'SELECT a.*, r.slug, r.name as r_name,
                       cs.status as sub_status, cs.plan_type
                FROM admins a
                LEFT JOIN restaurants r ON r.id = a.restaurant_id
                LEFT JOIN client_subscriptions cs ON cs.admin_id = a.id
                WHERE a.role = "ADMIN"
                ORDER BY a.created_at DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM admins WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM admins WHERE username = :u');
        $stmt->execute([':u' => $username]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM admins WHERE email = :e');
        $stmt->execute([':e' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
