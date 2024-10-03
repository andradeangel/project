<?php
    require_once("../database.php");

    class ConsultasModel {
        private $conexion;

        public function __construct() {
            global $conexion;
            $this->conexion = $conexion;
        }

        public function ejecutarConsulta($sql) {
            $resultado = $this->conexion->query($sql);
            if ($resultado === false) {
                throw new Exception("Error en la consulta: " . $this->conexion->error);
            }
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }
?>
