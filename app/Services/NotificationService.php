<?php
class NotificationService
{
    private PDO $pdo;
    private Mailer $mailer;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->mailer = new Mailer();
    }

    public function notifyNewReservation(int $adminId, array $reservationData): void
    {
        $optModel = new OptionModel($this->pdo);
        $notifEnabled = $optModel->get($adminId, 'email_notifications', '1');
        if ($notifEnabled !== '1') return;

        $admin = (new Admin($this->pdo))->findById($adminId);
        if (!$admin) return;

        $html = '<h2>Nouvelle réservation</h2>';
        $html .= '<p><strong>Client :</strong> ' . htmlspecialchars($reservationData['customer_name']) . '</p>';
        $html .= '<p><strong>Date :</strong> ' . $reservationData['reservation_date'] . ' à ' . $reservationData['reservation_time'] . '</p>';
        $html .= '<p><strong>Personnes :</strong> ' . $reservationData['party_size'] . '</p>';
        if (!empty($reservationData['special_requests'])) {
            $html .= '<p><strong>Demandes :</strong> ' . htmlspecialchars($reservationData['special_requests']) . '</p>';
        }
        $html .= '<p><a href="' . SITE_URL . '?page=reservations" style="background:#b45309;color:#fff;padding:12px 24px;text-decoration:none;border-radius:8px;display:inline-block;">Voir les réservations</a></p>';

        $this->mailer->send($admin->email, 'Nouvelle réservation - ' . $admin->restaurant_name, $html);
    }

    public function notifyReservationStatus(array $reservation, string $status): void
    {
        if (empty($reservation['customer_email'])) return;

        $statusLabels = [
            'confirmed' => 'confirmée',
            'rejected' => 'refusée',
            'cancelled' => 'annulée',
        ];
        $label = $statusLabels[$status] ?? $status;

        $html = '<h2>Votre réservation a été ' . $label . '</h2>';
        $html .= '<p><strong>Date :</strong> ' . $reservation['reservation_date'] . ' à ' . $reservation['reservation_time'] . '</p>';
        $html .= '<p><strong>Personnes :</strong> ' . $reservation['party_size'] . '</p>';

        $this->mailer->send(
            $reservation['customer_email'],
            'Réservation ' . $label . ' - MenuCraft',
            $html
        );
    }
}
