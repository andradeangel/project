<?php
    require_once('../models/monitoreoModel.php');
    require_once("../controllers/actualizarEstadosEventos.php");

    class MonitoreoController {
        private $model;

        public function __construct($conexion) {
            $this->model = new MonitoreoModel($conexion);
        }

        public function getEventosEnProceso() {
            return $this->model->getEventosEnProceso();
        }

        public function getPendingChallenges() {
            return $this->model->getPendingChallenges();
        }
        public function aprobarDesafio($challengeId) {
            // Obtener el ID del administrador de la sesión
            if (!isset($_SESSION['admin_id'])) {
                return false;
            }
            $admin_id = $_SESSION['admin_id'];
            return $this->model->aprobarDesafio($challengeId, $admin_id);
        }
        
        public function getJugadorPuntaje($jugadorId) {
            return $this->model->getJugadorPuntaje($jugadorId);
        }

        public function agregarDesafio($jugadorId, $eventoId, $juegoId, $challenge, $gameType, $descripcion) {
            error_log("Agregando nuevo desafío:");
            error_log("JugadorId: $jugadorId");
            error_log("EventoId: $eventoId");
            error_log("JuegoId: $juegoId");
            error_log("GameType: $gameType");
            error_log("Descripción: $descripcion");
        }
    }
?>
