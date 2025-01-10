<?php
    class MonitoreoModel {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
            if (!$this->conexion) {
                error_log("Error: No se pudo establecer la conexión a la base de datos en MonitoreoModel");
            }
        }

        public function getEventosEnProceso() {
            $query = "SELECT e.id, e.nombre, e.codigo, e.fechaInicio, e.fechaFin, e.idEstado, e.personas,
                     (SELECT COUNT(*) FROM jugadores j WHERE j.idEvento = e.id) as jugadores_actuales
              FROM eventos e 
              WHERE e.idEstado = 2";
            $resultado = $this->conexion->query($query);
            
            if ($resultado) {
                return $resultado->fetch_all(MYSQLI_ASSOC);
            } else {
                return [];
            }
        }

        public function addPhotoForReview($eventoId, $jugadorId, $fileName) {
            $sql = "INSERT INTO fotos_revision (idEvento, idJugador, nombreArchivo, estado) VALUES (?, ?, ?, 'pendiente')";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("iis", $eventoId, $jugadorId, $fileName);
            return $stmt->execute();
        }
    
        public function getPendingPhotos() {
            $sql = "SELECT fr.id, fr.idEvento, fr.idJugador, fr.nombreArchivo, j.nombres AS nombreJugador, e.nombre AS nombreEvento 
                    FROM fotos_revision fr 
                    JOIN jugadores j ON fr.idJugador = j.id 
                    JOIN eventos e ON fr.idEvento = e.id 
                    WHERE fr.estado = 'pendiente'";
            $result = $this->conexion->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    
        public function updatePhotoStatus($photoId, $status) {
            $sql = "UPDATE fotos_revision SET estado = ? WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("si", $status, $photoId);
            return $stmt->execute();
        }

        public function getPendingChallenges() {
            try {
                $sql = "SELECT d.id, d.jugador_id, d.evento_id, d.tipo, d.archivo_ruta, 
                               j.nombres as jugador_nombre, e.nombre as evento_nombre,
                               g.descripcion as game_description
                    FROM desafios d
                    JOIN jugadores j ON d.jugador_id = j.id
                    JOIN eventos e ON d.evento_id = e.id
                    LEFT JOIN juegos g ON d.juego_id = g.id
                    WHERE d.calificado = 0 AND d.estado = 'pendiente'
                    ORDER BY d.created_at DESC";
                    
                $resultado = $this->conexion->query($sql);
                
                if ($resultado) {
                    return $resultado->fetch_all(MYSQLI_ASSOC);
                }
                return [];
            } catch (Exception $e) {
                error_log("Error en getPendingChallenges: " . $e->getMessage());
                return [];
            }
        }
        
        public function aprobarDesafio($challengeId, $admin_id) {
            $this->conexion->begin_transaction();
            
            try {
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
                        SET puntaje = puntaje + 1, 
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

        public function terminarEvento($eventoId) {
            try {
                // Obtener la fecha y hora actual en formato MySQL
                $fechaActual = date('Y-m-d H:i:s');
                
                // Actualizar tanto el estado como la fecha de finalización
                $sqlUpdate = "UPDATE eventos 
                             SET idEstado = 3, 
                                 fechaFin = ? 
                             WHERE id = ? AND idEstado = 2";
                
                $stmtUpdate = $this->conexion->prepare($sqlUpdate);
                if (!$stmtUpdate) {
                    error_log("Error preparando consulta de actualización: " . $this->conexion->error);
                    return false;
                }
                
                $stmtUpdate->bind_param("si", $fechaActual, $eventoId);
                $success = $stmtUpdate->execute();
                
                if (!$success) {
                    error_log("Error ejecutando actualización: " . $stmtUpdate->error);
                    return false;
                }
                
                $rowsAffected = $stmtUpdate->affected_rows;
                error_log("Evento $eventoId terminado. Fecha fin actualizada a: $fechaActual. Filas afectadas: $rowsAffected");
                
                return $rowsAffected > 0;
            } catch (Exception $e) {
                error_log("Error en terminarEvento: " . $e->getMessage());
                return false;
            }
        }
    }
?>
