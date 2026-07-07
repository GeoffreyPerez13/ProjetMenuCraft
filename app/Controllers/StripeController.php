<?php
class StripeController extends BaseController
{
    public function createCheckout(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $this->blockIfDemo();
        $adminId = $this->getAdminId();

        $type = $_POST['checkout_type'] ?? 'basique';
        $period = $_POST['period'] ?? 'monthly';

        $prices = $this->calculatePrice($type, $period, $_POST);

        $lineItems = [];
        foreach ($prices['items'] as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => $item['name']],
                    'unit_amount' => (int)($item['price'] * 100),
                ],
                'quantity' => 1,
            ];
        }

        $sessionData = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => SITE_URL . '?page=stripe-success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => SITE_URL . '?page=settings&section=premium',
            'metadata' => [
                'admin_id' => $adminId,
                'type' => $type,
                'period' => $period,
            ],
        ];

        $response = $this->stripeRequest('checkout/sessions', $sessionData);

        if (isset($response['url'])) {
            header('Location: ' . $response['url']);
            exit;
        }

        $this->flash('error', 'Erreur lors de la création du paiement.');
        $this->redirect('settings', ['section' => 'premium']);
    }

    public function handleSuccess(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $sessionId = $_GET['session_id'] ?? '';

        if (empty($sessionId)) {
            $this->redirect('dashboard');
            return;
        }

        // Activer l'abonnement
        $subModel = new ClientSubscription($this->pdo);
        $subModel->activate($adminId, [
            'plan_type' => 'premium',
            'price_per_month' => 11.99,
            'stripe_session_id' => $sessionId,
        ]);

        // Activer toutes les features
        (new PremiumFeature($this->pdo))->activateAll($adminId);

        $this->flash('success', 'Paiement réussi ! Votre abonnement est maintenant actif.');
        $this->redirect('dashboard');
    }

    public function handleWebhook(): void
    {
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        http_response_code(200);
        echo json_encode(['received' => true]);
    }

    public function cancelSubscription(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $this->blockIfDemo();
        $adminId = $this->getAdminId();

        $subModel = new ClientSubscription($this->pdo);
        $this->pdo->prepare(
            'UPDATE client_subscriptions SET status = "cancelled" WHERE admin_id = :aid'
        )->execute([':aid' => $adminId]);

        $this->flash('success', 'Abonnement annulé.');
        $this->redirect('settings', ['section' => 'subscriptions']);
    }

    public function reactivateSubscription(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $this->blockIfDemo();
        $adminId = $this->getAdminId();

        $this->pdo->prepare(
            'UPDATE client_subscriptions SET status = "active" WHERE admin_id = :aid'
        )->execute([':aid' => $adminId]);

        $this->flash('success', 'Abonnement réactivé.');
        $this->redirect('settings', ['section' => 'subscriptions']);
    }

    private function calculatePrice(string $type, string $period, array $post): array
    {
        $items = [];
        $monthly = $period === 'monthly';

        switch ($type) {
            case 'basique':
                $items[] = ['name' => 'Abonnement Basique', 'price' => $monthly ? 11.99 : 9.99];
                break;
            case 'pack_full':
                $items[] = ['name' => 'Pack Full', 'price' => $monthly ? 29.99 : 22.99];
                break;
            default:
                $items[] = ['name' => 'Abonnement Basique', 'price' => $monthly ? 11.99 : 9.99];
        }

        return ['items' => $items, 'total' => array_sum(array_column($items, 'price'))];
    }

    private function stripeRequest(string $endpoint, array $data): array
    {
        $ch = curl_init('https://api.stripe.com/v1/' . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: [];
    }
}
