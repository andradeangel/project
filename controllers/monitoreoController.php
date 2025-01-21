<?php
require_once('../database.php');
require_once('../models/monitoreoModel.php');

class MonitoreoController {
    private $model;
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->model = new MonitoreoModel($conexion);
    }

    public function getPendingChallenges() {
        return $this->model->getPendingChallenges();
    }

    public function terminarEvento($eventoId) {
        try {
            $eventoId = intval($eventoId);
            if ($eventoId <= 0) {
                error_log("ID de evento inválido: " . $eventoId);
                return false;
            }

            // Debug para verificar la conexión
            if (!$this->conexion) {
                error_log("Error: No hay conexión a la base de datos");
                return false;
            }

            $resultado = $this->model->terminarEvento($eventoId);
            error_log("Resultado de terminarEvento para ID {$eventoId}: " . ($resultado ? 'true' : 'false'));
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en MonitoreoController->terminarEvento: " . $e->getMessage());
            return false;
        }
    }

    public function getEventosEnProceso() {
        return $this->model->getEventosEnProceso();
    }
}
?>
