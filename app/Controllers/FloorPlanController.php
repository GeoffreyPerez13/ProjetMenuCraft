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

    public function createFloor(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? 'Nouvelle salle');
        if ($name === '') $name = 'Nouvelle salle';

        $floorModel = new Floor($this->pdo);
        $id = $floorModel->create($adminId, $name);

        $this->json(['success' => true, 'id' => $id, 'name' => $name]);
    }

    public function renameFloor(): void
    {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        if ($id <= 0 || $name === '') {
            $this->json(['error' => 'Données invalides'], 400);
            return;
        }

        (new Floor($this->pdo))->rename($id, $name);
        $this->json(['success' => true]);
    }

    public function deleteFloor(): void
    {
        $this->requireAuth();
        $adminId = $this->getAdminId();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID invalide'], 400);
            return;
        }

        $floorModel = new Floor($this->pdo);
        $floors = $floorModel->getByAdmin($adminId);
        if (count($floors) <= 1) {
            $this->json(['error' => 'Impossible de supprimer la dernière salle'], 400);
            return;
        }

        $floorModel->delete($id);
        $this->json(['success' => true]);
    }
}
