<?php
/**
 * CRON — Vérification des abonnements expirés
 * Exécuter : php cron/check_subscriptions.php
 * Planifier : 0 2 * * * (tous les jours à 2h)
 */

require_once __DIR__ . '/../config.php';

$logFile = __DIR__ . '/logs/cron_subscriptions.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$start = microtime(true);

try {
    // Expirer les abonnements dépassés
    $stmt = $pdo->prepare(
        'UPDATE client_subscriptions SET status = "expired"
         WHERE status = "active" AND expires_at IS NOT NULL AND expires_at < NOW()'
    );
    $stmt->execute();
    $expired = $stmt->rowCount();

    // Marquer les réservations passées comme terminées (auto-complete)
    $autoComplete = $pdo->prepare(
        'UPDATE reservations r
         JOIN admins a ON a.id = r.admin_id
         JOIN admin_options ao ON ao.admin_id = a.id AND ao.option_name = "booking_auto_complete" AND ao.option_value = "1"
         SET r.status = "completed"
         WHERE r.status = "confirmed"
         AND CONCAT(r.reservation_date, " ", r.reservation_time) < DATE_SUB(NOW(), INTERVAL 2 HOUR)'
    );
    $autoComplete->execute();
    $completed = $autoComplete->rowCount();

    $duration = round(microtime(true) - $start, 3);
    $msg = date('Y-m-d H:i:s') . " | Expired: $expired subscription(s), Auto-completed: $completed reservation(s) in {$duration}s\n";
} catch (Exception $e) {
    $msg = date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n";
}

file_put_contents($logFile, $msg, FILE_APPEND);
echo $msg;
