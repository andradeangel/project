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

        public function getJuegos($evento_id) {
            $sql = "SELECT j1.nombre AS juego1, j1.juego AS direccion1, 
                        j2.nombre AS juego2, j2.juego AS direccion2, 
                        j3.nombre AS juego3, j3.juego AS direccion3, 
                        j4.nombre AS juego4, j4.juego AS direccion4, 
                        j5.nombre AS juego5, j5.juego AS direccion5, 
                        j6.nombre AS juego6, j6.juego AS direccion6
                    FROM eventos e 
                    INNER JOIN sprint s ON e.idSprint = s.id 
                    INNER JOIN juegos j1 ON s.idJuego1 = j1.id 
                    INNER JOIN juegos j2 ON s.idJuego2 = j2.id 
                    INNER JOIN juegos j3 ON s.idJuego3 = j3.id 
                    INNER JOIN juegos j4 ON s.idJuego4 = j4.id 
                    INNER JOIN juegos j5 ON s.idJuego5 = j5.id 
                    INNER JOIN juegos j6 ON s.idJuego6 = j6.id 
                    WHERE e.id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $evento_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $juegos = [];
            if ($row = $result->fetch_assoc()) {
                for ($i = 1; $i <= 6; $i++) {
                    $juegos[] = [
                        'nombre' => $row["juego$i"],
                        'direccion' => $row["direccion$i"]
                    ];
                }
            }
            return $juegos;
        }
        public function getJugadores($evento_id) {
            $sql = "SELECT j.nombres, j.puntaje 
                    FROM eventos e 
                    INNER JOIN jugadores j ON e.id = j.idEvento 
                    WHERE e.id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $evento_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $jugadores = array();
            while ($fila = $result->fetch_assoc()) {
                $jugadores[] = $fila;
            }
            return $jugadores;
        }   
    }
?>