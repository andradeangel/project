<?php
class EventoModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function getTematica($evento_id) {
        $sql = "SELECT s.nombre 
                FROM eventos e 
                INNER JOIN sprint s ON e.idSprint = s.id 
                WHERE e.id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $evento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['nombre'];
    }
}
?>