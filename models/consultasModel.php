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

        public function ejecutarConsultaEspecifica($tipoConsulta, $eventoId = null) {
            try {
                $params = [];
                $sql = "";
                
                switch ($tipoConsulta) {
                    case 'ranking_jugadores':
                        $sql = "SELECT j.nombres as 'Jugador', 
                               j.puntaje as 'Puntaje',
                               e.nombre as 'Evento'
                               FROM jugadores j
                               JOIN eventos e ON j.idEvento = e.id
                               " . ($eventoId ? "WHERE e.id = ?" : "") . "
                               ORDER BY j.puntaje DESC";
                        if ($eventoId) $params[] = $eventoId;
                        break;
                    
                    case 'estadisticas_genero':
                        $sql = "SELECT 
                               g.genero as 'Género',
                               COUNT(*) as 'Total Jugadores',
                               ROUND(AVG(j.puntaje), 2) as 'Puntaje Promedio'
                               FROM jugadores j
                               JOIN genero g ON j.idGenero = g.id
                               " . ($eventoId ? "WHERE j.idEvento = ?" : "") . "
                               GROUP BY g.genero";
                        if ($eventoId) $params[] = $eventoId;
                        break;

                    case 'juegos_populares':
                        $sql = "SELECT 
                               j.nombre as 'Juego',
                               COUNT(d.id) as 'Total Intentos',
                               SUM(CASE WHEN d.estado = 'aprobado' THEN 1 ELSE 0 END) as 'Completados'
                               FROM juegos j
                               LEFT JOIN desafios d ON j.id = d.juego_id
                               " . ($eventoId ? "WHERE d.evento_id = ?" : "") . "
                               GROUP BY j.id, j.nombre
                               ORDER BY COUNT(d.id) DESC";
                        if ($eventoId) $params[] = $eventoId;
                        break;

                    case 'progreso_eventos':
                        $sql = "SELECT 
                               e.nombre as 'Evento',
                               e.fechaInicio as 'Fecha Inicio',
                               e.fechaFin as 'Fecha Fin',
                               COUNT(j.id) as 'Total Jugadores',
                               SUM(CASE WHEN j.idEstado = 3 THEN 1 ELSE 0 END) as 'Terminados',
                               ROUND(AVG(j.puntaje), 2) as 'Puntaje Promedio'
                               FROM eventos e
                               LEFT JOIN jugadores j ON e.id = j.idEvento
                               " . ($eventoId ? "WHERE e.id = ?" : "") . "
                               GROUP BY e.id, e.nombre, e.fechaInicio, e.fechaFin";
                        if ($eventoId) $params[] = $eventoId;
                        break;

                    case 'analisis_edad':
                        $sql = "SELECT 
                               CASE 
                                   WHEN edad < 18 THEN 'Menor de 18'
                                   WHEN edad BETWEEN 18 AND 25 THEN '18-25'
                                   WHEN edad BETWEEN 26 AND 35 THEN '26-35'
                                   ELSE 'Mayor de 35'
                               END as 'Grupo de Edad',
                               COUNT(*) as 'Total Jugadores',
                               ROUND(AVG(puntaje), 2) as 'Puntaje Promedio'
                               FROM jugadores
                               " . ($eventoId ? "WHERE idEvento = ?" : "") . "
                               GROUP BY 
                               CASE 
                                   WHEN edad < 18 THEN 'Menor de 18'
                                   WHEN edad BETWEEN 18 AND 25 THEN '18-25'
                                   WHEN edad BETWEEN 26 AND 35 THEN '26-35'
                                   ELSE 'Mayor de 35'
                               END";
                        if ($eventoId) $params[] = $eventoId;
                        break;

                    case 'feedback_analisis':
                        $sql = "SELECT 
                               j.nombres as 'Jugador',
                               e.nombre as 'Evento',
                               f.comentarios as 'Comentarios',
                               f.fecha as 'Fecha'
                               FROM feedback f
                               JOIN jugadores j ON f.idJugador = j.id
                               JOIN eventos e ON f.idEvento = e.id
                               " . ($eventoId ? "WHERE f.idEvento = ?" : "") . "
                               ORDER BY f.fecha DESC";
                        if ($eventoId) $params[] = $eventoId;
                        break;
                }
                
                if (empty($sql)) {
                    throw new Exception("Tipo de consulta no válido");
                }
                
                $stmt = $this->conexion->prepare($sql);
                if (!empty($params)) {
                    $types = str_repeat('s', count($params));
                    $stmt->bind_param($types, ...$params);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
                }
                
                $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                if (empty($resultado)) {
                    return []; // Retornar array vacío en lugar de null
                }
                return $resultado;
                
            } catch (Exception $e) {
                error_log("Error en ConsultasModel: " . $e->getMessage());
                throw $e;
            }
        }
    }
?>
