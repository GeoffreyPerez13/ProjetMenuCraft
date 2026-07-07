<?php
/**
 * RateLimiter — Anti-brute-force basé sur fichiers JSON par IP
 */
class RateLimiter
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = BASE_PATH . '/storage/rate_limits/';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function isLimited(string $action, int $maxAttempts, int $windowSeconds): bool
    {
        $file = $this->getFilePath($action);
        $data = $this->loadData($file);
        $now = time();

        // Nettoyer les anciennes entrées
        $data = array_filter($data, fn($ts) => ($now - $ts) < $windowSeconds);

        return count($data) >= $maxAttempts;
    }

    public function hit(string $action): void
    {
        $file = $this->getFilePath($action);
        $data = $this->loadData($file);
        $data[] = time();
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public function clear(string $action): void
    {
        $file = $this->getFilePath($action);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    private function getFilePath(string $action): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $hash = hash('sha256', $ip);
        return $this->storagePath . $action . '_' . $hash . '.json';
    }

    private function loadData(string $file): array
    {
        if (!file_exists($file)) return [];
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
}
