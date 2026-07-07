<?php
class GoogleReviews
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getReviews(string $placeId, string $apiKey): ?array
    {
        // Vérifier le cache (1h)
        $cached = $this->getCache($placeId);
        if ($cached) return $cached;

        // Appel API Google Places (New)
        $url = "https://places.googleapis.com/v1/places/$placeId?fields=displayName,rating,userRatingCount,reviews&key=$apiKey";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) return null;

        $data = json_decode($response, true);
        if (!$data) return null;

        $this->setCache($placeId, $response);
        return $data;
    }

    private function getCache(string $placeId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT data FROM google_reviews_cache WHERE place_id = :pid AND cached_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $stmt->execute([':pid' => $placeId]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return json_decode($row->data, true);
    }

    private function setCache(string $placeId, string $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO google_reviews_cache (place_id, data, cached_at) VALUES (:pid, :data, NOW())
             ON DUPLICATE KEY UPDATE data = :data2, cached_at = NOW()'
        );
        $stmt->execute([':pid' => $placeId, ':data' => $data, ':data2' => $data]);
    }
}
