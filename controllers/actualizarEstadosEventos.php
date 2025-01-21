<?php
require_once("../database.php");
require_once("../utils/logger.php");

date_default_timezone_set('America/La_Paz');

function actualizarEstadosEventos($conexion) {
    try {
        $fechaActual = new DateTime();
        $fechaActualStr = $fechaActual->format("Y-m-d H:i:s");
        
        log_activity("Iniciando actualización de estados de eventos. Fecha actual: " . $fechaActualStr);
        // Eventos En Proceso que deben volver a Pendiente
        $sqlRetrocesoPendiente = "UPDATE eventos 
                                 SET idEstado = 1 
                                 WHERE idEstado = 2 
                                 AND fechaInicio > ?";
        $stmtRetroceso = $conexion->prepare($sqlRetrocesoPendiente);
        $stmtRetroceso->bind_param("s", $fechaActualStr);
        $stmtRetroceso->execute();
        
        $eventosRetrocedidosPendiente = $stmtRetroceso->affected_rows;
        log_activity("Eventos retrocedidos a Pendiente: " . $eventosRetrocedidosPendiente);

        // Eventos Finalizados que deben volver a En Proceso o Pendiente
        $sqlRetrocesoFinalizados = "UPDATE eventos 
                                   SET idEstado = CASE 
                                       WHEN fechaInicio > ? THEN 1
                                       WHEN fechaFin > ? THEN 2
                                       ELSE idEstado
                                   END
                                   WHERE idEstado = 3 
                                   AND (fechaFin > ? OR fechaInicio > ?)";
        $stmtRetrocesoFin = $conexion->prepare($sqlRetrocesoFinalizados);
        $stmtRetrocesoFin->bind_param("ssss", $fechaActualStr, $fechaActualStr, $fechaActualStr, $fechaActualStr);
        $stmtRetrocesoFin->execute();
        
        $eventosRetrocedidosFinalizados = $stmtRetrocesoFin->affected_rows;
        log_activity("Eventos retrocedidos desde Finalizado: " . $eventosRetrocedidosFinalizados);

        // Actualizar eventos que han finalizado
        $sqlEventosFinalizados = "UPDATE eventos SET idEstado = 3 
                                WHERE idEstado = 2 
                                AND fechaFin <= ?";
        $stmtEventos = $conexion->prepare($sqlEventosFinalizados);
        $stmtEventos->bind_param("s", $fechaActualStr);
        $stmtEventos->execute();
        
        $eventosActualizados = $stmtEventos->affected_rows;
        log_activity("Eventos finalizados actualizados: " . $eventosActualizados);

        // Actualizar los jugadores de eventos finalizados
        $sqlUpdateJugadores = "UPDATE jugadores j 
                             INNER JOIN eventos e ON j.idEvento = e.id 
                             SET j.idEstado = 3 
                             WHERE e.idEstado = 3 
                             AND j.idEstado != 3";
        $stmtJugadores = $conexion->prepare($sqlUpdateJugadores);
        $stmtJugadores->execute();
        
        $jugadoresActualizados = $stmtJugadores->affected_rows;
        log_activity("Jugadores actualizados a terminado: " . $jugadoresActualizados);

        // Actualizar eventos que deben iniciar
        $sqlEventosInicio = "UPDATE eventos SET idEstado = 2 
                            WHERE idEstado = 1 
                            AND fechaInicio <= ?";
        $stmtEventosInicio = $conexion->prepare($sqlEventosInicio);
        $stmtEventosInicio->bind_param("s", $fechaActualStr);
        $stmtEventosInicio->execute();

        $eventosIniciados = $stmtEventosInicio->affected_rows;
        log_activity("Eventos iniciados: " . $eventosIniciados);

        // Verificar estado de jugadores para terminar eventos
        $sqlVerificacion = "SELECT e.id, e.nombre, e.idEstado as estado_evento, 
                           COUNT(j.id) as total_jugadores, 
                           SUM(CASE WHEN j.idEstado = 3 THEN 1 ELSE 0 END) as jugadores_terminados
                           FROM eventos e 
                           LEFT JOIN jugadores j ON e.id = j.idEvento
                           WHERE e.idEstado = 3 
                           GROUP BY e.id";
        $result = $conexion->query($sqlVerificacion);
        
        while ($row = $result->fetch_assoc()) {
            log_activity("Evento ID: {$row['id']}, Nombre: {$row['nombre']}, " .
                        "Jugadores terminados: {$row['jugadores_terminados']}/{$row['total_jugadores']}");
        }

    } catch (Exception $e) {
        log_activity("Error en actualización de estados: " . $e->getMessage(), 'error');
    }
}

actualizarEstadosEventos($conexion);
?>