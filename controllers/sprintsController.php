<?php
require_once("../models/sprintsModel.php");

class SprintController {
    private $model;

    public function __construct($db) {
        $this->model = new SprintModel($db);
    }

    public function getAllSprints() {
        return $this->model->getAllSprints();
    }

    public function getSprint($id) {
        return $this->model->getSprint($id);
    }

    public function createSprint($data) {
        return $this->model->createSprint($data);
    }

    public function updateSprint($id, $data) {
        return $this->model->updateSprint($id, $data);
    }

    public function deleteSprint($id) {
        return $this->model->deleteSprint($id);
    }

    public function getAllJuegos() {
        return $this->model->getAllJuegos();
    }
}

// Manejo de solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conexion;
    $controller = new SprintController($conexion);
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $controller->createSprint($_POST);
                echo json_encode(['success' => $result !== false, 'id' => $result]);
                break;
            case 'update':
                $id = $_POST['id'];
                unset($_POST['id'], $_POST['action']);
                $result = $controller->updateSprint($id, $_POST);
                echo json_encode(['success' => $result]);
                break;
            case 'delete':
                $result = $controller->deleteSprint($_POST['id']);
                echo json_encode(['success' => $result]);
                break;
            case 'get':
                $sprint = $controller->getSprint($_POST['id']);
                echo json_encode($sprint);
                break;
        }
    }
}