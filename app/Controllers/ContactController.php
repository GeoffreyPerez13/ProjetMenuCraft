<?php
class ContactController extends BaseController
{
    public function edit(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        $contactModel = new Contact($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();

            $data = [
                'telephone' => trim($_POST['telephone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'adresse' => trim($_POST['adresse'] ?? ''),
                'horaires' => trim($_POST['horaires'] ?? ''),
            ];

            $contactModel->createOrUpdate($adminId, $data);

            // Support AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                $this->json(['success' => true, 'message' => 'Contact mis à jour.']);
                return;
            }

            $this->flash('success', 'Informations de contact mises à jour.');
            $this->redirect('edit-contact');
            return;
        }

        $contact = $contactModel->findByAdmin($adminId);

        $this->render('admin/edit-contact', [
            'pageTitle' => 'Contact — MenuCraft',
            'contact' => $contact,
        ]);
    }
}
