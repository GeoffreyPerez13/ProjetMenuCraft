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

        // Vérifier le paiement auprès de Stripe
        $session = $this->stripeRequest('checkout/sessions/' . urlencode($sessionId), [], 'GET');

        if (empty($session['id']) || ($session['payment_status'] ?? '') !== 'paid') {
            $this->flash('error', 'Le paiement n\'a pas pu être vérifié. Contactez le support si le problème persiste.');
            $this->redirect('settings', ['section' => 'premium']);
            return;
        }

        // Vérifier que la session appartient bien à cet admin
        $metaAdminId = (int)($session['metadata']['admin_id'] ?? 0);
        if ($metaAdminId !== $adminId) {
            $this->flash('error', 'Session de paiement invalide.');
            $this->redirect('dashboard');
            return;
        }

        // Vérifier que cette session n'a pas déjà été utilisée
        $subModel = new ClientSubscription($this->pdo);
        $existing = $subModel->findByAdmin($adminId);
        if ($existing && $existing->stripe_session_id === $sessionId) {
            $this->flash('info', 'Votre abonnement est déjà actif.');
            $this->redirect('dashboard');
            return;
        }

        // Activer l'abonnement
        $planType = $session['metadata']['type'] ?? 'premium';
        $subModel->activate($adminId, [
            'plan_type' => $planType,
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

        // Vérifier la signature Stripe
        if (empty($sigHeader) || !$this->verifyWebhookSignature($payload, $sigHeader)) {
            http_response_code(400);
            echo json_encode(['error' => 'Signature invalide']);
            return;
        }

        $event = json_decode($payload, true);
        if (!$event || empty($event['type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Payload invalide']);
            return;
        }

        // Traiter les événements pertinents
        switch ($event['type']) {
            case 'checkout.session.completed':
                $session = $event['data']['object'] ?? [];
                $adminId = (int)($session['metadata']['admin_id'] ?? 0);
                if ($adminId && ($session['payment_status'] ?? '') === 'paid') {
                    $subModel = new ClientSubscription($this->pdo);
                    $subModel->activate($adminId, [
                        'plan_type' => $session['metadata']['type'] ?? 'premium',
                        'price_per_month' => 11.99,
                        'stripe_session_id' => $session['id'] ?? '',
                    ]);
                    (new PremiumFeature($this->pdo))->activateAll($adminId);
                }
                break;
        }

        http_response_code(200);
        echo json_encode(['received' => true]);
    }

    private function verifyWebhookSignature(string $payload, string $sigHeader): bool
    {
        $secret = defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : '';
        if (empty($secret) || str_starts_with($secret, 'whsec_...')) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$key, $value] = explode('=', trim($part), 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';
        if (empty($timestamp) || empty($signature)) return false;

        // Tolérance de 5 minutes
        if (abs(time() - (int)$timestamp) > 300) return false;

        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signature);
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

    private function stripeRequest(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $url = 'https://api.stripe.com/v1/' . $endpoint;
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else {
            $opts[CURLOPT_HTTPGET] = true;
        }
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: [];
    }
}
