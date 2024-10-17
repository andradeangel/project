<?php
    class MonitoreoModel {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
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
            if (!isset($_SESSION['pending_challenges']) || empty($_SESSION['pending_challenges'])) {
                return [];
            }

            $challenges = [];
            foreach ($_SESSION['pending_challenges'] as $id => $challenge) {
                $challenges[] = [
                    'challengeId' => $id,
                    'challenge' => $challenge['challenge'],
                    'gameType' => $challenge['gameType'],
                    'eventoId' => $challenge['eventoId'],
                    'jugadorId' => $challenge['jugadorId'],
                    'jugadorNombre' => $challenge['jugadorNombre'],
                    'eventoNombre' => $challenge['eventoNombre'],
                    'estado' => $challenge['estado'],
                    'gameDescription' => $challenge['gameDescription']
                ];
            }

            return $challenges;
        }
    }
?>
