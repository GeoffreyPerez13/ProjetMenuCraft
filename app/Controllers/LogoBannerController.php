<?php
class LogoBannerController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        $logo = $this->pdo->prepare('SELECT * FROM logos WHERE admin_id = :aid LIMIT 1');
        $logo->execute([':aid' => $adminId]);
        $logo = $logo->fetch() ?: null;

        $banner = $this->pdo->prepare('SELECT * FROM banners WHERE admin_id = :aid LIMIT 1');
        $banner->execute([':aid' => $adminId]);
        $banner = $banner->fetch() ?: null;

        $this->render('admin/edit-logo-banner', [
            'pageTitle' => 'Logo & Bannière — MenuCraft',
            'logo' => $logo,
            'banner' => $banner,
        ]);
    }

    public function uploadLogo(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        if (empty($_FILES['logo']['tmp_name'])) {
            $this->flash('error', 'Aucun fichier sélectionné.');
            $this->redirect('edit-logo-banner');
            return;
        }

        $filename = $this->handleUpload($_FILES['logo'], 'logos');
        if (!$filename) {
            $this->flash('error', 'Format de fichier non supporté.');
            $this->redirect('edit-logo-banner');
            return;
        }

        // Supprimer l'ancien logo
        $this->deleteLogo(false);

        $stmt = $this->pdo->prepare(
            'INSERT INTO logos (admin_id, filename) VALUES (:aid, :fn)
             ON DUPLICATE KEY UPDATE filename = :fn2, uploaded_at = NOW()'
        );
        $stmt->execute([':aid' => $adminId, ':fn' => $filename, ':fn2' => $filename]);

        $this->flash('success', 'Logo mis à jour.');
        $this->redirect('edit-logo-banner');
    }

    public function deleteLogo(bool $redirect = true): void
    {
        if ($redirect) {
            $this->requireAuth();
            $this->verifyCsrfToken();
        }
        $adminId = $this->getAdminId();

        $stmt = $this->pdo->prepare('SELECT filename FROM logos WHERE admin_id = :aid');
        $stmt->execute([':aid' => $adminId]);
        $logo = $stmt->fetch();

        if ($logo) {
            $path = BASE_PATH . '/public/uploads/' . $logo->filename;
            if (file_exists($path)) unlink($path);
            $this->pdo->prepare('DELETE FROM logos WHERE admin_id = :aid')->execute([':aid' => $adminId]);
        }

        if ($redirect) {
            $this->flash('success', 'Logo supprimé.');
            $this->redirect('edit-logo-banner');
        }
    }

    public function uploadBanner(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        if (empty($_FILES['banner']['tmp_name'])) {
            $this->flash('error', 'Aucun fichier sélectionné.');
            $this->redirect('edit-logo-banner');
            return;
        }

        $filename = $this->handleUpload($_FILES['banner'], 'banners');
        if (!$filename) {
            $this->flash('error', 'Format de fichier non supporté.');
            $this->redirect('edit-logo-banner');
            return;
        }

        $this->deleteBanner(false);

        $stmt = $this->pdo->prepare(
            'INSERT INTO banners (admin_id, filename) VALUES (:aid, :fn)
             ON DUPLICATE KEY UPDATE filename = :fn2, uploaded_at = NOW()'
        );
        $stmt->execute([':aid' => $adminId, ':fn' => $filename, ':fn2' => $filename]);

        $this->flash('success', 'Bannière mise à jour.');
        $this->redirect('edit-logo-banner');
    }

    public function deleteBanner(bool $redirect = true): void
    {
        if ($redirect) {
            $this->requireAuth();
            $this->verifyCsrfToken();
        }
        $adminId = $this->getAdminId();

        $stmt = $this->pdo->prepare('SELECT filename FROM banners WHERE admin_id = :aid');
        $stmt->execute([':aid' => $adminId]);
        $banner = $stmt->fetch();

        if ($banner) {
            $path = BASE_PATH . '/public/uploads/' . $banner->filename;
            if (file_exists($path)) unlink($path);
            $this->pdo->prepare('DELETE FROM banners WHERE admin_id = :aid')->execute([':aid' => $adminId]);
        }

        if ($redirect) {
            $this->flash('success', 'Bannière supprimée.');
            $this->redirect('edit-logo-banner');
        }
    }

    public function saveBannerText(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        $text = trim($_POST['banner_text'] ?? '');
        $stmt = $this->pdo->prepare('UPDATE banners SET text = :text WHERE admin_id = :aid');
        $stmt->execute([':text' => $text, ':aid' => $adminId]);

        $this->flash('success', 'Texte de bannière mis à jour.');
        $this->redirect('edit-logo-banner');
    }

}
