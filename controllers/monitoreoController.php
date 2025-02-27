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

    public function aprobarDesafio($challengeId, $admin_id) {
        try {
            // Iniciar transacción
            $this->conexion->begin_transaction();
            
            // Obtener información del desafío
            $sql = "SELECT jugador_id FROM desafios WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("s", $challengeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $desafio = $result->fetch_assoc();
            
            if (!$desafio) {
                throw new Exception("Desafío no encontrado");
            }
            
            // Actualizar puntaje y juego actual del jugador
            $sql = "UPDATE jugadores 
                    SET puntaje = puntaje + 10, 
                        juego_actual = juego_actual + 1 
                    WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $desafio['jugador_id']);
            $stmt->execute();
            
            // Marcar desafío como aprobado y registrar calificador
            $sql = "UPDATE desafios 
                    SET estado = 'aprobado', 
                        calificado = TRUE,
                        calificador_id = ? 
                    WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("is", $admin_id, $challengeId);
            $stmt->execute();
            
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            error_log("Error en aprobarDesafio: " . $e->getMessage());
            return false;
        }
    }

    public function getJugadorPuntaje($jugadorId) {
        $sql = "SELECT puntaje FROM jugadores WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $jugadorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['puntaje'] ?? 0;
    }
}
?>
