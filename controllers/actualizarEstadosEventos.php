<?php
    require_once("../database.php");

    function actualizarEstadosEventos($conexion) {
        $fechaActual = date("Y-m-d H:i:s");

    // Actualizar eventos que están en espera y su fecha de inicio es igual a la fecha actual
    $sql = "UPDATE eventos SET idEstado = 2 WHERE idEstado = 1 AND fechaInicio <= ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $fechaActual);
    $stmt->execute();

    // Actualizar eventos que están activos y su fecha de finalización es igual a la fecha actual
    $sql = "UPDATE eventos SET idEstado = 3 WHERE idEstado = 2 AND fechaFin <= ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $fechaActual);
    $stmt->execute();
    }

    actualizarEstadosEventos($conexion);
?>