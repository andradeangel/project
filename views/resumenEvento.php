<?php
require_once("../database.php");

// Iniciar y verificar sesión de administrador
custom_session_start('admin_session');

// Logs para depuración
error_log("Accediendo a resumenEvento.php");
error_log("GET params: " . print_r($_GET, true));

$eventoId = null;

// Verificar si viene de eventos.php (admin)
if (isset($_GET['id'])) {
    // Verificar que haya una sesión de administrador activa
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
    $eventoId = $_GET['id'];
} 
// Verificar si viene de finJuego.php (jugador)
elseif (isset($_GET['token'])) {
    // Decodificar el token (formato: base64_encode(eventoId_timestamp))
    $decodedToken = base64_decode($_GET['token']);
    list($tokenEventoId, $timestamp) = explode('_', $decodedToken);
    
    // Verificar que el token no tenga más de 1 hora de antigüedad
    if (time() - $timestamp > 3600) { // 3600 segundos = 1 hora
        header("Location: ../index.php");
        exit();
    }
    
    $eventoId = $tokenEventoId;
} else {
    header("Location: ../index.php");
    exit();
}

// Verificar que el evento existe
$sql = "SELECT * FROM eventos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $eventoId);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if (!$evento) {
    header("Location: ../index.php");
    exit();
}

// Obtener todos los jugadores del evento con sus detalles
$sql = "SELECT j.id, j.nombres, j.puntaje, j.tiempo_fin,
        GROUP_CONCAT(
            CONCAT(
                d.tipo, ':', 
                d.archivo_ruta, ':', 
                d.created_at, ':', 
                d.juego_id, ':', 
                d.estado
            ) ORDER BY d.created_at ASC
        ) as desafios_info
        FROM jugadores j
        LEFT JOIN desafios d ON j.id = d.jugador_id
        WHERE j.idEvento = ?
        GROUP BY j.id
        ORDER BY j.puntaje DESC, j.tiempo_fin ASC";

error_log("Ejecutando consulta de jugadores para evento ID: " . $eventoId);

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $eventoId);
$stmt->execute();
$resultado = $stmt->get_result();
$jugadores = $resultado->fetch_all(MYSQLI_ASSOC);

error_log("Jugadores encontrados: " . count($jugadores));
error_log("Datos de jugadores: " . print_r($jugadores, true));
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

        /* Estilos específicos para impresión */
        @media print {
            .no-print {
                display: none !important;
            }
            
            .video-placeholder {
                border: 1px solid #ccc;
                padding: 10px;
                text-align: center;
                background-color: #f5f5f5;
                color: #666;
                font-style: italic;
            }

            .download-btn {
                display: none !important;
            }
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
                        echo "No completo los juegos";
                    }
                ?></p>
                
                <div class="row">
                    <?php
                    if ($jugador['desafios_info']) {
                        $desafios = explode(',', $jugador['desafios_info']);
                        $contador_retos = 1;
                        foreach ($desafios as $desafio) {
                            list($tipo, $ruta, $tiempo, $juego_id, $estado) = explode(':', $desafio);
                            echo '<div class="col-md-4 desafio-card">';
                            echo '<h5>Reto ' . $contador_retos . '</h5>';
                            if ($tipo == 'photo' && $ruta) {
                                echo '<img src="../uploads/challenges/' . htmlspecialchars($ruta) . '" class="desafio-imagen">';
                                echo '<div class="mt-2 download-btn no-print">';
                                echo '<a href="../uploads/challenges/' . htmlspecialchars($ruta) . '" 
                                        download="Reto_' . $contador_retos . '_' . htmlspecialchars($jugador['nombres']) . '.jpg" 
                                        class="btn btn-success btn-sm">
                                        <i class="fas fa-download"></i> Descargar Foto
                                     </a>';
                                echo '</div>';
                            } elseif ($tipo == 'video' && $ruta) {
                                echo '<video class="desafio-video no-print" controls>
                                        <source src="../uploads/challenges/' . htmlspecialchars($ruta) . '">
                                      </video>';
                                echo '<div class="video-placeholder d-none d-print-block">Video</div>';
                                echo '<div class="mt-2 download-btn no-print">';
                                echo '<a href="../uploads/challenges/' . htmlspecialchars($ruta) . '" 
                                        download="Reto_' . $contador_retos . '_' . htmlspecialchars($jugador['nombres']) . '.mp4" 
                                        class="btn btn-success btn-sm">
                                        <i class="fas fa-download"></i> Descargar Video
                                     </a>';
                                echo '</div>';
                            }
                            echo '</div>';
                            $contador_retos++;
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="text-center mb-4 no-print">
            <button onclick="window.print()" class="btn btn-success m-2">Descargar Resumen</button>
            <a href="https://lapuerta.net" class="btn btn-secondary m-2">Salir</a>
        </div>
    </div>
</body>
</html>