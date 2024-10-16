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
    }
?>
