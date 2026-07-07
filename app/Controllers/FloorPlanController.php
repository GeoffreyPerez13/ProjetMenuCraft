<?php
class FloorPlanController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();

        $floorModel = new Floor($this->pdo);
        $floors = $floorModel->getByAdmin($adminId);

        // Créer un étage par défaut si aucun n'existe
        if (empty($floors)) {
            $floorModel->create($adminId);
            $floors = $floorModel->getByAdmin($adminId);
        }

        $tableModel = new RestaurantTable($this->pdo);
        $elementModel = new RestaurantElement($this->pdo);

        $floorData = [];
        foreach ($floors as $floor) {
            $floorData[$floor->id] = [
                'floor' => $floor,
                'tables' => $tableModel->getByFloor($floor->id),
                'elements' => $elementModel->getByFloor($floor->id),
            ];
        }

        $this->render('admin/floor-plan', [
            'pageTitle' => 'Plan de salle — MenuCraft',
            'floors' => $floors,
            'floorData' => $floorData,
        ]);
    }

    public function save(): void
    {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $this->json(['error' => 'Données invalides'], 400);
            return;
        }

        $floorId = (int)($data['floor_id'] ?? 0);
        $tables = $data['tables'] ?? [];
        $elements = $data['elements'] ?? [];

        (new RestaurantTable($this->pdo))->save($floorId, $tables);
        (new RestaurantElement($this->pdo))->save($floorId, $elements);

        $this->json(['success' => true]);
    }
}
