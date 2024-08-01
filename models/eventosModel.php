<?php
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT usuarios.nombres, usuarios.rol, rol.rol AS nombre_rol 
                FROM usuarios 
                JOIN rol ON usuarios.rol = rol.id 
                WHERE usuarios.id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $nombre_usuario = $user['nombres'];
        $rol_usuario = $user['nombre_rol'];

        function obtenerSprints($conexion) {
                $sql = "SELECT id, nombre FROM sprint ORDER BY nombre";
                $result = $conexion->query($sql);
                if (!$result) {
                    die("Error en la consulta: " . $conexion->error);
                }
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            
        function crearEvento($conexion, $nombre, $codigo, $fechaInicio, $fechaFin, $sprint, $descripcion) {
                $sql = "INSERT INTO eventos (nombre, codigo, fechaInicio, fechaFin, sprint, descripcion) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssssss", $nombre, $codigo, $fechaInicio, $fechaFin, $sprint, $descripcion);
                return $stmt->execute();
        }
        
        function obtenerEventos($conexion, $orderBy = 'e.fechaInicio', $orderDir = 'DESC') {
                $allowedColumns = ['e.nombre', 'e.codigo', 'es.estado', 's.nombre', 'e.fechaInicio', 'e.fechaFin'];
                $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'e.fechaInicio';
                $orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';
        
                $sql = "SELECT e.nombre, e.codigo, e.fechaInicio, e.fechaFin, 
                        es.estado AS estado_nombre,
                        s.nombre AS sprint_nombre, e.descripcion
                        FROM eventos e
                        LEFT JOIN estado es ON e.estado = es.id
                        LEFT JOIN sprint s ON e.sprint = s.id
                        ORDER BY {$orderBy} {$orderDir}";
                
                $result = $conexion->query($sql);
                if (!$result) {
                die("Error en la consulta: " . $conexion->error);
                }
                return $result->fetch_all(MYSQLI_ASSOC);
        }
?>