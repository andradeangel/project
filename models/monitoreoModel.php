<?php
    class MonitoreoModel {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
            if (!$this->conexion) {
                error_log("Error: No se pudo establecer la conexión a la base de datos en MonitoreoModel");
            } else {
                error_log("Conexión a la base de datos establecida correctamente en MonitoreoModel");
            }
        }

        public function getEventosEnProceso() {
            $query = "SELECT nombre, codigo, fechaInicio, fechaFin, idEstado FROM eventos WHERE idEstado = 2";
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
            $sql = "SELECT d.*, j.nombres as jugador_nombre, 
                    e.nombre as evento_nombre, 
                    g.descripcion as game_description
                    FROM desafios d
                    JOIN jugadores j ON d.jugador_id = j.id
                    JOIN eventos e ON d.evento_id = e.id
                    JOIN juegos g ON d.juego_id = g.id
                    WHERE d.estado = 'pendiente'";
            
            $result = $this->conexion->query($sql);
            
            if (!$result) {
                error_log("Error en la consulta: " . $this->conexion->error);
                return [];
            }
            
            $challenges = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Desafíos encontrados: " . count($challenges));
            error_log("Datos: " . print_r($challenges, true));
            
            return $challenges;
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
    }
?>
