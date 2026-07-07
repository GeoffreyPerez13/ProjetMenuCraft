<?php
class NotificationStreamController extends BaseController
{
    public function stream(): void
    {
        if (empty($_SESSION['admin_id'])) {
            http_response_code(403);
            exit;
        }

        $adminId = $_SESSION['admin_id'];
        session_write_close();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $lastCheck = time();

        while (true) {
            if (connection_aborted()) break;

            $now = time();

            // Vérifier les nouvelles réservations toutes les 3 secondes
            if ($now - $lastCheck >= 3) {
                try {
                    $stmt = $this->pdo->prepare(
                        'SELECT COUNT(*) FROM reservations WHERE admin_id = :aid AND status = "pending"'
                    );
                    $stmt->execute([':aid' => $adminId]);
                    $count = (int)$stmt->fetchColumn();

                    echo "data: " . json_encode(['type' => 'reservations', 'count' => $count]) . "\n\n";
                    ob_flush();
                    flush();
                } catch (Exception $e) {
                    break;
                }
                $lastCheck = $now;
            }

            // Heartbeat toutes les 15 secondes
            echo ": heartbeat\n\n";
            ob_flush();
            flush();

            sleep(3);
        }
    }
}
