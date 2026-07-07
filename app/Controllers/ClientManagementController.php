<?php
class ClientManagementController extends BaseController
{
    public function show(): void
    {
        $this->requireSuperAdmin();

        $adminModel = new Admin($this->pdo);
        $clients = $adminModel->getAllClients();

        $premiumModel = new PremiumFeature($this->pdo);
        $clientFeatures = [];
        foreach ($clients as $client) {
            $clientFeatures[$client->id] = $premiumModel->getByAdmin($client->id);
        }

        $this->render('admin/manage-clients', [
            'pageTitle' => 'Gestion des clients — MenuCraft',
            'clients' => $clients,
            'clientFeatures' => $clientFeatures,
        ]);
    }

    public function activateSubscription(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrfToken();
        $clientId = (int)($_POST['client_id'] ?? 0);

        $subModel = new ClientSubscription($this->pdo);
        $subModel->activate($clientId, ['plan_type' => 'premium', 'price_per_month' => 0]);
        (new PremiumFeature($this->pdo))->activateAll($clientId);

        $this->flash('success', 'Abonnement activé.');
        $this->redirect('manage-clients');
    }

    public function deactivateSubscription(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrfToken();
        $clientId = (int)($_POST['client_id'] ?? 0);

        (new ClientSubscription($this->pdo))->deactivate($clientId);

        $this->flash('success', 'Abonnement désactivé.');
        $this->redirect('manage-clients');
    }
}
