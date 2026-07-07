<?php
class FeedbackController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();

        $this->render('admin/feedback', [
            'pageTitle' => 'Feedback — MenuCraft',
        ]);
    }

    public function submit(): void
    {
        $this->requireAuth();
        $this->verifyCsrfToken();
        $adminId = $this->getAdminId();

        // Vérifier limite de 3 par mois
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM feedbacks WHERE admin_id = :aid AND created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)'
        );
        $stmt->execute([':aid' => $adminId]);
        if ((int)$stmt->fetchColumn() >= 3) {
            $this->flash('error', 'Vous avez atteint la limite de 3 feedbacks par mois.');
            $this->redirect('feedback');
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO feedbacks (admin_id, name, email, rating, ease_of_use, favorite_feature, improvements, comments)
             VALUES (:aid, :name, :email, :rating, :ease, :fav, :imp, :com)'
        );
        $stmt->execute([
            ':aid' => $adminId,
            ':name' => trim($_POST['name'] ?? ''),
            ':email' => trim($_POST['email'] ?? ''),
            ':rating' => (int)($_POST['rating'] ?? 0),
            ':ease' => $_POST['ease_of_use'] ?? '',
            ':fav' => trim($_POST['favorite_feature'] ?? ''),
            ':imp' => trim($_POST['improvements'] ?? ''),
            ':com' => trim($_POST['comments'] ?? ''),
        ]);

        $this->flash('success', 'Merci pour votre retour !');
        $this->redirect('feedback');
    }
}
