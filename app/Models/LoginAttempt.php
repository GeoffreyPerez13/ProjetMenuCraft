<?php
class LoginAttempt
{
    private PDO $pdo;

    private const IP_MAX_ATTEMPTS = 5;
    private const IP_WINDOW_MINUTES = 15;
    private const ACCOUNT_MAX_ATTEMPTS = 10;
    private const ACCOUNT_WINDOW_MINUTES = 30;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function record(string $ip, ?string $username, bool $success): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_attempts (ip_address, username, success, attempted_at) VALUES (:ip, :username, :success, NOW())'
        );
        $stmt->execute([
            ':ip' => $ip,
            ':username' => $username,
            ':success' => $success ? 1 : 0,
        ]);
    }

    public function isIpBlocked(string $ip): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts 
             WHERE ip_address = :ip AND success = 0 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)'
        );
        $stmt->execute([':ip' => $ip, ':minutes' => self::IP_WINDOW_MINUTES]);
        return (int)$stmt->fetchColumn() >= self::IP_MAX_ATTEMPTS;
    }

    public function isAccountLocked(string $username): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts 
             WHERE username = :username AND success = 0 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)'
        );
        $stmt->execute([':username' => $username, ':minutes' => self::ACCOUNT_WINDOW_MINUTES]);
        return (int)$stmt->fetchColumn() >= self::ACCOUNT_MAX_ATTEMPTS;
    }

    public function getFailedCountForAccount(string $username): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts 
             WHERE username = :username AND success = 0 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)'
        );
        $stmt->execute([':username' => $username, ':minutes' => self::ACCOUNT_WINDOW_MINUTES]);
        return (int)$stmt->fetchColumn();
    }

    public function clearForIp(string $ip): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM login_attempts WHERE ip_address = :ip');
        $stmt->execute([':ip' => $ip]);
    }

    public function clearForAccount(string $username): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM login_attempts WHERE username = :username');
        $stmt->execute([':username' => $username]);
    }

    public function cleanup(int $daysOld = 7): void
    {
        $this->pdo->exec(
            'DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ' . (int)$daysOld . ' DAY)'
        );
    }

    public function getRemainingLockoutMinutes(string $ip): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT attempted_at FROM login_attempts 
             WHERE ip_address = :ip AND success = 0 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
             ORDER BY attempted_at ASC LIMIT 1'
        );
        $stmt->execute([':ip' => $ip, ':minutes' => self::IP_WINDOW_MINUTES]);
        $oldest = $stmt->fetchColumn();
        if (!$oldest) return 0;

        $unlockTime = strtotime($oldest) + (self::IP_WINDOW_MINUTES * 60);
        $remaining = ceil(($unlockTime - time()) / 60);
        return max(0, (int)$remaining);
    }

    public function getAccountRemainingMinutes(string $username): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT attempted_at FROM login_attempts 
             WHERE username = :username AND success = 0 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
             ORDER BY attempted_at ASC LIMIT 1'
        );
        $stmt->execute([':username' => $username, ':minutes' => self::ACCOUNT_WINDOW_MINUTES]);
        $oldest = $stmt->fetchColumn();
        if (!$oldest) return 0;

        $unlockTime = strtotime($oldest) + (self::ACCOUNT_WINDOW_MINUTES * 60);
        $remaining = ceil(($unlockTime - time()) / 60);
        return max(0, (int)$remaining);
    }
}
