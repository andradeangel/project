<?php
require_once("../database.php");

// Verificar que se proporcione un token
if (!isset($_GET['token'])) {
    header("Location: ../index.php");
    exit();
}

try {
    // Decodificar el token
    $decodedToken = base64_decode($_GET['token']);
    list($eventoId, $timestamp) = explode('_', $decodedToken);
    
    // Verificar que el token no tenga más de 1 hora de antigüedad
    if (time() - $timestamp > 3600) {
        header("Location: ../index.php");
        exit();
    }
    
    // Convertir a entero para mayor seguridad
    $eventoId = (int)$eventoId;
    
    // Verificar que el evento exista y haya terminado
    $sqlVerificar = "SELECT COUNT(*) as total, 
                     SUM(CASE WHEN j.idEstado = 3 THEN 1 ELSE 0 END) as terminados,
                     e.fechaFin
                     FROM jugadores j
                     JOIN eventos e ON j.idEvento = e.id
                     WHERE j.idEvento = ? AND e.idEstado = 3";
    $stmtVerificar = $conexion->prepare($sqlVerificar);
    $stmtVerificar->bind_param("i", $eventoId);
    $stmtVerificar->execute();
    $verificacion = $stmtVerificar->get_result()->fetch_assoc();
    
    if (!$verificacion) {
        throw new Exception("Evento no válido");
    }
    
    // Verificar si el tiempo ha terminado
    $fechaActual = new DateTime('now', new DateTimeZone('America/La_Paz'));
    $fechaFin = new DateTime($verificacion['fechaFin'], new DateTimeZone('America/La_Paz'));
    $tiempoTerminado = $fechaActual >= $fechaFin;

    $eventoTerminado = ($verificacion['total'] == $verificacion['terminados']) || $tiempoTerminado;

    if (!$eventoTerminado) {
        header("Location: ../index.php");
        exit();
    }

    // Obtener todos los jugadores del evento con sus detalles
    $sql = "SELECT j.id, j.nombres, j.puntaje, j.tiempo_fin,
            (SELECT GROUP_CONCAT(
                CONCAT_WS(':', 
                    d.tipo, 
                    d.archivo_ruta, 
                    d.created_at,
                    d.juego_id,
                    COALESCE(d.estado, 'pendiente')
                ) ORDER BY d.created_at
            )
             FROM desafios d 
             WHERE d.jugador_id = j.id) as desafios_info,
            (SELECT MIN(created_at) FROM desafios WHERE jugador_id = j.id) as tiempo_inicio
            FROM jugadores j 
            WHERE j.idEvento = ?
            ORDER BY j.puntaje DESC, j.tiempo_fin ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $eventoId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $jugadores = $resultado->fetch_all(MYSQLI_ASSOC);

    // Obtener el nombre del evento
    $sqlEvento = "SELECT nombre FROM eventos WHERE id = ?";
    $stmtEvento = $conexion->prepare($sqlEvento);
    $stmtEvento->bind_param("i", $eventoId);
    $stmtEvento->execute();
    $evento = $stmtEvento->get_result()->fetch_assoc();
} catch (Exception $e) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen del Evento</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .resumen-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            margin: 20px;
        }
        .desafio-card {
            background-color: rgba(40, 40, 40, 0.9);
            border: 1px solid #0f0;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
        }
        .tiempo-juego {
            color: #0f0;
            font-family: 'Press Start 2P', cursive;
        }
        .desafio-imagen {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
        }
        .desafio-video {
            max-width: 200px;
        }
        .estado-desafio {
            padding: 5px;
            border-radius: 3px;
            margin-top: 5px;
            text-align: center;
            font-weight: bold;
        }
        
        .estado-desafio[data-estado="aprobado"] {
            background-color: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
        }
        
        .estado-desafio[data-estado="pendiente"] {
            background-color: rgba(255, 193, 7, 0.2);
            border: 1px solid #ffc107;
        }
        
        .estado-desafio[data-estado="reprobado"] {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container-fluid">
        <h1 class="text-center mt-4">Resumen del Evento: <?php echo htmlspecialchars($evento['nombre']); ?></h1>
        
        <?php foreach ($jugadores as $jugador): ?>
            <div class="resumen-container">
                <h3><?php echo htmlspecialchars($jugador['nombres']); ?></h3>
                <p>Puntaje Final: <?php echo $jugador['puntaje']; ?> llaves</p>
                <p>Hora de Finalización: <?php 
                    if ($jugador['tiempo_fin']) {
                        echo date('H:i:s', strtotime($jugador['tiempo_fin']));
                    } else {
                        echo "No disponible";
                    }
                ?></p>
                
                <div class="row">
                    <?php
                    if ($jugador['desafios_info']) {
                        $desafios = explode(',', $jugador['desafios_info']);
                        foreach ($desafios as $desafio) {
                            list($tipo, $ruta, $tiempo, $juego_id, $estado) = explode(':', $desafio);
                            echo '<div class="col-md-4 desafio-card">';
                            echo '<h5>Juego ' . $juego_id . '</h5>';
                            if ($tipo == 'photo' && $ruta) {
                                echo '<img src="' . htmlspecialchars($ruta) . '" class="desafio-imagen">';
                            } elseif ($tipo == 'video' && $ruta) {
                                echo '<video class="desafio-video" controls><source src="' . htmlspecialchars($ruta) . '"></video>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="text-center mb-4">
            <button onclick="window.print()" class="btn btn-success m-2">Descargar Resumen</button>
            <a href="../index.php" class="btn btn-primary m-2">Salir</a>
        </div>
    </div>
</body>
</html>