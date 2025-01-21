<?php
    class SprintModel {
        private $db;

        public function __construct($db) {
            $this->db = $db;
        }

        public function getAllSprints($orderBy = 'nombre', $orderDir = 'ASC') {
            $allowedColumns = ['nombre', 'juego1', 'juego2', 'juego3', 'juego4', 'juego5', 'juego6'];
            $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'nombre';
            $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
            $query = "SELECT s.id, s.nombre, 
                            j1.nombre as juego1, j2.nombre as juego2, j3.nombre as juego3, 
                            j4.nombre as juego4, j5.nombre as juego5, j6.nombre as juego6
                    FROM sprint s
                    LEFT JOIN juegos j1 ON s.idJuego1 = j1.id
                    LEFT JOIN juegos j2 ON s.idJuego2 = j2.id
                    LEFT JOIN juegos j3 ON s.idJuego3 = j3.id
                    LEFT JOIN juegos j4 ON s.idJuego4 = j4.id
                    LEFT JOIN juegos j5 ON s.idJuego5 = j5.id
                    LEFT JOIN juegos j6 ON s.idJuego6 = j6.id
                    ORDER BY s.nombre $orderDir";
            $result = $this->db->query($query);
            if (!$result) {
                throw new Exception("Error en la consulta: " . $this->db->error);
            }
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getSprint($id) {
            $query = "SELECT * FROM sprint WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        public function createSprint($data) {
            $query = "INSERT INTO sprint (nombre, idJuego1, idJuego2, idJuego3, idJuego4, idJuego5, idJuego6) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssssss", $data['nombre'], $data['juego1'], $data['juego2'], 
                            $data['juego3'], $data['juego4'], $data['juego5'], $data['juego6']);
            $success = $stmt->execute();
            if ($success) {
                return ["success" => true, "message" => "Sprint creado con éxito", "id" => $this->db->insert_id];
            } else {
                return ["success" => false, "message" => "Error al crear el sprint: " . $stmt->error];
            }
        }

        public function updateSprint($id, $data) {
            $query = "UPDATE sprint SET nombre = ?, idJuego1 = ?, idJuego2 = ?, 
                    idJuego3 = ?, idJuego4 = ?, idJuego5 = ?, idJuego6 = ? 
                    WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssssssi", $data['nombre'], $data['juego1'], $data['juego2'], 
                            $data['juego3'], $data['juego4'], $data['juego5'], $data['juego6'], $id);
            $success = $stmt->execute();
            if ($success) {
                return ["success" => true, "message" => "Sprint actualizado con éxito"];
            } else {
                return ["success" => false, "message" => "Error al actualizar el sprint: " . $stmt->error];
            }
        }

        public function deleteSprint($id) {
            // Verificar si el sprint está siendo utilizado
            $queryCheck = "SELECT COUNT(*) as count FROM eventos WHERE idSprint = ?";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bind_param("i", $id);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $rowCheck = $resultCheck->fetch_assoc();

            if ($rowCheck['count'] > 0) {
                return ["success" => false, "message" => "No se puede eliminar este Sprint porque ya fue utilizado en un evento."];
            }

            $query = "DELETE FROM sprint WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            if ($success) {
                return ["success" => true, "message" => "Sprint eliminado con éxito"];
            } else {
                return ["success" => false, "message" => "Error al eliminar el sprint: " . $this->db->error];
            }
        }

        public function getAllJuegos() {
            $query = "SELECT id, nombre FROM juegos";
            $result = $this->db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }
?>