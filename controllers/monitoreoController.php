<?php
    require_once('../models/monitoreoModel.php');

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
            return $this->model->aprobarDesafio($challengeId);
        }
        
        public function getJugadorPuntaje($jugadorId) {
            return $this->model->getJugadorPuntaje($jugadorId);
        }
    }
?>
