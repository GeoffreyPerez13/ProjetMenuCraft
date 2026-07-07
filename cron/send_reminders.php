<?php
/**
 * CRON — Rappels mensuels de mise à jour de la carte
 * Exécuter : php cron/send_reminders.php
 * Planifier : 0 10 1 * * (1er de chaque mois à 10h)
 */

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/app/Helpers/Mailer.php';

$logFile = __DIR__ . '/logs/cron_reminders.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$start = microtime(true);

try {
    // Trouver les admins avec le rappel activé
    $stmt = $pdo->query(
        'SELECT a.email, a.restaurant_name, a.username
         FROM admins a
         JOIN admin_options ao ON ao.admin_id = a.id
         WHERE ao.option_name = "mail_reminder" AND ao.option_value = "1"
         AND a.email_verified = 1'
    );
    $admins = $stmt->fetchAll();

    $mailer = new Mailer();
    $sent = 0;

    foreach ($admins as $admin) {
        $html = '<h2>Rappel mensuel — MenuCraft</h2>
                <p>Bonjour ' . htmlspecialchars($admin->username) . ',</p>
                <p>N\'oubliez pas de mettre à jour la carte de <strong>' . htmlspecialchars($admin->restaurant_name) . '</strong> !</p>
                <p>Ajoutez vos nouveaux plats, mettez à jour les prix et les horaires pour garder votre site à jour.</p>
                <p><a href="' . SITE_URL . '?page=edit-card" style="background:#b45309;color:#fff;padding:14px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:600;">Mettre à jour ma carte</a></p>';

        if ($mailer->send($admin->email, 'Rappel : mettez à jour votre carte — MenuCraft', $html)) {
            $sent++;
        }
    }

    $duration = round(microtime(true) - $start, 3);
    $msg = date('Y-m-d H:i:s') . " | Sent $sent reminder(s) to " . count($admins) . " admin(s) in {$duration}s\n";
} catch (Exception $e) {
    $msg = date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n";
}

file_put_contents($logFile, $msg, FILE_APPEND);
echo $msg;
