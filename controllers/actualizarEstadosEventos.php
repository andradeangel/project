<?php
    require_once("C:/xampp/htdocs/database.php");
    require_once("C:/xampp/htdocs/utils/logger.php");
    date_default_timezone_set('America/La_Paz');
    function actualizarEstadosEventos($conexion) {
        $fechaActual = new DateTime();
        $fechaActualStr = $fechaActual->format("Y-m-d H:i:s");
        
        log_activity("Iniciando actualización de estados de eventos. Fecha actual: " . $fechaActualStr);

        // Actualizar eventos que están en espera y su fecha de inicio es menor o igual a la fecha actual
        $sql = "UPDATE eventos SET idEstado = 2 WHERE idEstado = 1 AND fechaInicio <= ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $fechaActualStr);
        $stmt->execute();
        
        $filasActualizadasInicio = $stmt->affected_rows;
        log_activity("Eventos actualizados de 'en espera' a 'activos': " . $filasActualizadasInicio);
        
        if ($stmt->error) {
            log_activity("Error en la actualización de eventos en espera: " . $stmt->error, 'error');
        }

        // Actualizar eventos que están activos y su fecha de finalización es menor o igual a la fecha actual
        $sql = "UPDATE eventos SET idEstado = 3 WHERE idEstado = 2 AND fechaFin <= ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $fechaActualStr);
        $stmt->execute();
        
        $filasActualizadasFin = $stmt->affected_rows;
        log_activity("Eventos actualizados de 'activos' a 'finalizados': " . $filasActualizadasFin);
        
        if ($stmt->error) {
            log_activity("Error en la actualización de eventos activos: " . $stmt->error, 'error');
        }

        // Log de resumen
        log_activity("Resumen de actualización:");
        log_activity("Total de eventos actualizados: " . ($filasActualizadasInicio + $filasActualizadasFin));

        // Opcional: Mostrar eventos actuales para verificación
        $sql = "SELECT id, nombre, fechaInicio, fechaFin, idEstado FROM eventos ORDER BY fechaInicio";
        $result = $conexion->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                log_activity("Evento ID: " . $row['id'] . 
                            ", Nombre: " . $row['nombre'] . 
                            ", Inicio: " . $row['fechaInicio'] . 
                            ", Fin: " . $row['fechaFin'] . 
                            ", Estado: " . $row['idEstado']);
            }
        } else {
            log_activity("Error al obtener eventos para verificación: " . $conexion->error, 'error');
        }
    }
    actualizarEstadosEventos($conexion);
?>