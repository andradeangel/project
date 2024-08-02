<?php
require_once("../models/consultasModel.php");

class ConsultasController {
    private $model;

    public function __construct() {
        $this->model = new ConsultasModel();
    }

    public function ejecutarConsulta($sql) {
        try {
            $resultado = $this->model->ejecutarConsulta($sql);
            return $resultado;
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function obtenerGeneros() {
        $sql = "SELECT id, genero FROM genero";
        return $this->model->ejecutarConsulta($sql);
    }

    public function obtenerEventos() {
        $sql = "SELECT id, nombre FROM eventos";
        return $this->model->ejecutarConsulta($sql);
    }
}
?>
