<?php
/**
 * CRON — Nettoyage des démos expirées
 * Exécuter : php cron/clean_demos.php
 * Planifier : 0 * * * * (toutes les heures)
 */

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/app/Models/DemoToken.php';

$logFile = __DIR__ . '/logs/cron_demos.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$start = microtime(true);

try {
    $demoModel = new DemoToken($pdo);
    $cleaned = $demoModel->cleanExpired();
    $duration = round(microtime(true) - $start, 3);
    $msg = date('Y-m-d H:i:s') . " | Cleaned $cleaned expired demo(s) in {$duration}s\n";
} catch (Exception $e) {
    $msg = date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n";
}

file_put_contents($logFile, $msg, FILE_APPEND);
echo $msg;
