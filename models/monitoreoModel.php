<?php
class MonitoreoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function getEventosEnProceso() {
        $query = "SELECT nombre, codigo, fechaInicio, fechaFin, estado FROM eventos WHERE estado = 2";
        $resultado = $this->conexion->query($query);
        
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }
}
?>