<?php
require_once("../database.php");
require_once("../utils/logger.php");

date_default_timezone_set('America/La_Paz');

function actualizarEstadosEventos($conexion) {
    try {
        $fechaActual = new DateTime();
        $fechaActualStr = $fechaActual->format("Y-m-d H:i:s");
        
        log_activity("Iniciando actualización de estados de eventos. Fecha actual: " . $fechaActualStr);

        // 1. Primero actualizamos los eventos que han finalizado
        $sqlEventosFinalizados = "UPDATE eventos SET idEstado = 3 
                                WHERE idEstado = 2 
                                AND fechaFin <= ?";
        $stmtEventos = $conexion->prepare($sqlEventosFinalizados);
        $stmtEventos->bind_param("s", $fechaActualStr);
        $stmtEventos->execute();
        
        $eventosActualizados = $stmtEventos->affected_rows;
        log_activity("Eventos finalizados actualizados: " . $eventosActualizados);

        // 2. Luego actualizamos los jugadores de los eventos finalizados
        $sqlUpdateJugadores = "UPDATE jugadores j 
                             INNER JOIN eventos e ON j.idEvento = e.id 
                             SET j.idEstado = 3 
                             WHERE e.idEstado = 3 
                             AND j.idEstado != 3";
        $stmtJugadores = $conexion->prepare($sqlUpdateJugadores);
        $stmtJugadores->execute();
        
        $jugadoresActualizados = $stmtJugadores->affected_rows;
        log_activity("Jugadores actualizados a terminado: " . $jugadoresActualizados);

        // 3. Por último actualizamos los eventos que deben iniciar
        $sqlEventosInicio = "UPDATE eventos SET idEstado = 2 
                            WHERE idEstado = 1 
                            AND fechaInicio <= ?";
        $stmtEventosInicio = $conexion->prepare($sqlEventosInicio);
        $stmtEventosInicio->bind_param("s", $fechaActualStr);
        $stmtEventosInicio->execute();

        $eventosIniciados = $stmtEventosInicio->affected_rows;
        log_activity("Eventos iniciados: " . $eventosIniciados);

        // Verificación final del estado de eventos y jugadores
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