<?php
    require_once("../database.php");
    require_once("../models/sprintsModel.php");

    $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'nombre';
    $orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'ASC';

    // Habilitar el registro de errores
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);

    // Registrar la solicitud recibida
    error_log("Método de la solicitud: " . $_SERVER['REQUEST_METHOD']);
    error_log("Datos GET: " . print_r($_GET, true));
    error_log("Datos POST: " . print_r($_POST, true));

    class SprintController {
        private $model;

        public function __construct($db) {
            $this->model = new SprintModel($db);
        }

        public function getAllSprints($orderBy = 'nombre', $orderDir = 'ASC') {
            return $this->model->getAllSprints($orderBy, $orderDir);
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

    $controller = new SprintController($conexion);
    $sprints = $controller->getAllSprints($orderBy, $orderDir);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        $resultado = ['success' => false, 'message' => 'Acción no válida'];
    
        error_log("Acción recibida: " . $accion);
        error_log("POST data: " . print_r($_POST, true));
    
        try {
            switch ($accion) {
                case 'listar':
                    $sortBy = $_GET['sortBy'] ?? 'nombre';
                    $sortOrder = $_GET['sortOrder'] ?? 'asc';
                    error_log("Ordenando por: $sortBy, Orden: $sortOrder");
                    $sprints = $controller->getAllSprints($sortBy, $sortOrder);
                    $resultado = ['success' => true, 'data' => $sprints];
                    break;
                case 'eliminar':
                    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                    $resultado = $controller->deleteSprint($id);
                    break;
                case 'crear':
                    $data = [
                        'nombre' => $_POST['nombre'],
                        'juego1' => $_POST['juego1'],
                        'juego2' => $_POST['juego2'],
                        'juego3' => $_POST['juego3'],
                        'juego4' => $_POST['juego4'],
                        'juego5' => $_POST['juego5'],
                        'juego6' => $_POST['juego6'],
                    ];
                    $resultado = $controller->createSprint($data);
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
    }
?>