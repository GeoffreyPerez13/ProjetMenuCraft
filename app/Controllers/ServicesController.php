<?php
class ServicesController extends BaseController
{
    private array $serviceKeys = [
        'service_sur_place', 'service_a_emporter', 'service_livraison_ubereats',
        'service_livraison_etablissement', 'service_wifi', 'service_climatisation',
        'service_pmr', 'service_animaux',
    ];

    private array $paymentKeys = [
        'payment_visa', 'payment_mastercard', 'payment_cb',
        'payment_especes', 'payment_cheques', 'payment_tickets_restaurant',
    ];

    private array $socialKeys = [
        'social_instagram', 'social_facebook', 'social_x',
        'social_tiktok', 'social_snapchat',
    ];

    public function show(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $optModel = new OptionModel($this->pdo);
        $options = $optModel->getAll($adminId);

        $this->render('admin/edit-services', [
            'pageTitle' => 'Services — MenuCraft',
            'options' => $options,
            'serviceKeys' => $this->serviceKeys,
            'paymentKeys' => $this->paymentKeys,
            'socialKeys' => $this->socialKeys,
        ]);
    }

    public function save(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();
        $optModel = new OptionModel($this->pdo);

        // Services et paiements (checkboxes)
        foreach (array_merge($this->serviceKeys, $this->paymentKeys) as $key) {
            $optModel->set($adminId, $key, isset($_POST[$key]) ? '1' : '0');
        }

        // Réseaux sociaux (URLs)
        foreach ($this->socialKeys as $key) {
            $optModel->set($adminId, $key, trim($_POST[$key] ?? ''));
        }

        $this->flash('success', 'Services mis à jour.');
        $this->redirect('edit-services');
    }
}
