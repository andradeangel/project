<?php
    custom_session_start('admin_session');
    $user_id = $_SESSION['admin_id'] ?? null;

    if ($user_id) {
        $sql = "SELECT usuarios.nombres, usuarios.idRol, rol.rol AS nombre_rol 
                FROM usuarios 
                JOIN rol ON usuarios.idRol = rol.id 
                WHERE usuarios.id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $nombre_usuario = $user['nombres'] ?? 'Usuario desconocido';
        $rol_usuario = $user['nombre_rol'] ?? 'Rol no definido';
    } else {
        $nombre_usuario = 'Usuario desconocido';
        $rol_usuario = 'Rol no definido';
    }

    function obtenerSprints($conexion) {
        $sql = "SELECT id, nombre FROM sprint ORDER BY nombre";
        $result = $conexion->query($sql);
        if (!$result) {
            die("Error en la consulta: " . $conexion->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function obtenerUltimoCodigo($conexion) {
        $sql = "SELECT codigo FROM eventos ORDER BY id DESC LIMIT 1";
        $result = $conexion->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc()['codigo'];
        }
        return 'AA0000';  // Código inicial si no hay eventos
    }
    
    function generarSiguienteCodigo($ultimoCodigo) {
        $letras = substr($ultimoCodigo, 0, 2);
        $numero = intval(substr($ultimoCodigo, 2));
        
        $numero++;
        if ($numero > 9999) {
            $numero = 1;
            $letras = incrementarLetras($letras);
        }
        return $letras . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
    
    function incrementarLetras($letras) {
        if ($letras == 'ZZ') {
            return 'AA';
        }
        if ($letras[1] == 'Z') {
            return chr(ord($letras[0]) + 1) . 'A';
        }
        return $letras[0] . chr(ord($letras[1]) + 1);
    }
            
    function crearEvento($conexion, $nombre, $fechaInicio, $fechaFin, $sprint, $descripcion, $personas) {
        if (strtotime($fechaFin) < strtotime($fechaInicio)) {
            return ["success" => false, "message" => "La fecha de finalización no puede ser anterior a la fecha de inicio."];
        }
    
        $ultimoCodigo = obtenerUltimoCodigo($conexion);
        $nuevoCodigo = generarSiguienteCodigo($ultimoCodigo);
    
        $sql = "INSERT INTO eventos (nombre, codigo, fechaInicio, fechaFin, idSprint, descripcion, personas) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssi", $nombre, $nuevoCodigo, $fechaInicio, $fechaFin, $sprint, $descripcion, $personas);
        $success = $stmt->execute();
        
        if ($personas < 2) {
            return ["success" => false, "message" => "La capacidad de personas debe ser al menos 2"];
        }

        if ($success) {
            return ["success" => true, "message" => "Evento creado con éxito"];
        } else {
            return ["success" => false, "message" => "Error al crear el evento: " . $stmt->error];
        }
    }
            
    function obtenerEvento($conexion, $id) {
        $sql = "SELECT * FROM eventos WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    function editarEvento($conexion, $id, $nombre, $fechaInicio, $fechaFin, $sprint, $descripcion, $personas) {
        if (strtotime($fechaFin) < strtotime($fechaInicio)) {
            return ["success" => false, "message" => "La fecha de finalización no puede ser anterior a la fecha de inicio."];
        }
        
        $sql = "UPDATE eventos SET nombre = ?, fechaInicio = ?, fechaFin = ?, idSprint = ?, descripcion = ?, personas = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssii", $nombre, $fechaInicio, $fechaFin, $sprint, $descripcion, $personas, $id);
        $success = $stmt->execute();
    
        if ($personas < 2) {
            return ["success" => false, "message" => "La capacidad de personas debe ser al menos 2"];
        }
        
        if ($success) {
            return ["success" => true, "message" => "Evento actualizado con éxito"];
        } else {
            return ["success" => false, "message" => "Error al actualizar el evento: " . $stmt->error];
        }
    }
    
    function eliminarEvento($conexion, $id) {
        // Primero, verificar si el evento ha sido jugado
        $sqlCheck = "SELECT COUNT(*) as count FROM jugadores WHERE idEvento = ?";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $rowCheck = $resultCheck->fetch_assoc();

        if ($rowCheck['count'] > 0) {
            return ["success" => false, "message" => "No se puede eliminar este evento porque ya fue jugado"];
        }

        try {
            // Si no ha sido jugado, proceder a eliminar el evento
            $sql = "DELETE FROM eventos WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();

            if ($success) {
                return ["success" => true, "message" => "Evento eliminado con éxito"];
            } else {
                return ["success" => false, "message" => "No se puede eliminar este evento porque está siendo utilizado"];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "No se puede eliminar este evento porque está siendo utilizado"];
        }
    }

    function obtenerUsuario($conexion, $user_id) {
        if ($user_id) {
            $sql = "SELECT usuarios.nombres, usuarios.idRol, rol.rol AS nombre_rol 
                    FROM usuarios 
                    JOIN rol ON usuarios.idRol = rol.id 
                    WHERE usuarios.id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return null;
    }
    
    function obtenerEventos($conexion, $orderBy = 'e.fechaInicio', $orderDir = 'DESC') {
        $allowedColumns = ['e.nombre', 'e.codigo', 'es.estado', 's.nombre', 'e.fechaInicio', 'e.fechaFin', 'e.personas'];
        $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'e.fechaInicio';
        $orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';
    
        $sql = "SELECT e.id, e.nombre, e.codigo, e.fechaInicio, e.fechaFin, 
                es.estado AS estado_nombre,
                s.nombre AS sprint_nombre, e.descripcion, e.idSprint, e.personas
                FROM eventos e
                LEFT JOIN estado es ON e.idEstado = es.id
                LEFT JOIN sprint s ON e.idSprint = s.id
                ORDER BY {$orderBy} {$orderDir}";
        
        $result = $conexion->query($sql);
        if (!$result) {
            die("Error en la consulta: " . $conexion->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
?>
