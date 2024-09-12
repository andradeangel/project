<?php
    require_once("../database.php");
    require_once("../models/sprintsModel.php");

    class SprintController {
        private $model;

        public function __construct($db) {
            $this->model = new SprintModel($db);
        }

        public function getAllSprints() {
            return $this->model->getAllSprints();
        }

        public function getAllJuegos() {
            return $this->model->getAllJuegos();
        }

        public function createSprint($data) {
            return $this->model->createSprint($data);
        }

        public function getSprint($id) {
            return $this->model->getSprint($id);
        }

        public function updateSprint($id, $data) {
            return $this->model->updateSprint($id, $data);
        }

        public function deleteSprint($id) {
            return $this->model->deleteSprint($id);
        }
    }

    $model = new SprintModel($conexion);


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new SprintController($conexion);
        
        $accion = $_POST['accion'] ?? '';
        $resultado = ['success' => false, 'message' => 'Acción no válida'];
    
        error_log("Acción recibida: " . $accion);
        error_log("POST data: " . print_r($_POST, true));
    
        try {
            switch ($accion) {
                case 'eliminar':
                    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                    $resultado = $controller->deleteSprint($id);
                    break;
                case 'crear':
                    $resultado = $controller->createSprint($_POST);
                    break;
                case 'obtener':
                    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                    $sprint = $controller->getSprint($id);
                    if ($sprint) {
                        $resultado = ['success' => true, 'data' => $sprint];
                    } else {
                        $resultado = ['success' => false, 'message' => 'Sprint no encontrado'];
                    }
                    break;
                case 'editar':
                    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                    $data = [
                        'nombre' => $_POST['nombre'],
                        'juego1' => $_POST['juego1'],
                        'juego2' => $_POST['juego2'],
                        'juego3' => $_POST['juego3'],
                        'juego4' => $_POST['juego4'],
                        'juego5' => $_POST['juego5'],
                        'juego6' => $_POST['juego6'],
                    ];
                    $resultado = $controller->updateSprint($id, $data);
                    break;
            }
        } catch (Exception $e) {
            $resultado = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            error_log("Exception: " . $e->getMessage());
        }
    
        error_log("Resultado: " . print_r($resultado, true));
    
        header('Content-Type: application/json');
        echo json_encode($resultado);   
        exit;
    

        $sprints = $model->getAllSprints();
        $juegos = $model->getAllJuegos();
    }
?>