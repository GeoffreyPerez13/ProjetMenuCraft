<?php
class StatsController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        if (!PremiumFeature::isEnabled($this->pdo, $adminId, 'advanced_analytics')) {
            $this->flash('error', 'Fonctionnalité premium requise.');
            $this->redirect('settings', ['section' => 'premium']);
            return;
        }

        $this->render('admin/stats', [
            'pageTitle' => 'Statistiques — MenuCraft',
        ]);
    }

    public function getData(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        if (!PremiumFeature::isEnabled($this->pdo, $adminId, 'advanced_analytics')) {
            $this->json(['error' => 'Non autorisé'], 403);
            return;
        }

        $days = (int)($_GET['days'] ?? 30);
        $stats = (new SiteVisit($this->pdo))->getStats($adminId, $days);
        $this->json($stats);
    }
}
