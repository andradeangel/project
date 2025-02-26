<?php
require_once("../database.php");
custom_session_start('player_session');

// Crear o verificar token de acceso
if (isset($_SESSION['player_id'])) {
    // Jugador recién llegado: crear token y destruir sesión
    $jugadorId = $_SESSION['player_id'];
    $eventoId = $_SESSION['player_evento_id'];
    
    // Crear token único
    $token = bin2hex(random_bytes(32));
    
    // Guardar token en la base de datos
    $sql = "UPDATE jugadores SET token_acceso = ?, token_expiracion = DATE_ADD(NOW(), INTERVAL 24 HOUR) 
            WHERE id = ? AND idEvento = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sii", $token, $jugadorId, $eventoId);
    $stmt->execute();
    
    // Guardar token en cookie
    setcookie('fin_juego_token', $token, time() + 86400, '/', '', true, true);
    
    // Destruir sesión después de crear el token
    session_destroy();
} else {
    // Verificar acceso mediante token
    $token = $_COOKIE['fin_juego_token'] ?? '';
    
    if (empty($token)) {
        header("Location: ../index.php");
        exit();
    }
    
    // Verificar token en la base de datos
    $sql = "SELECT j.id, j.idEvento FROM jugadores j 
            WHERE j.token_acceso = ? AND j.token_expiracion > NOW()";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../index.php");
        exit();
    }
    
    $jugadorData = $result->fetch_assoc();
    $jugadorId = $jugadorData['id'];
    $eventoId = $jugadorData['idEvento'];
}

// Prevenir navegación hacia atrás
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Establecer zona horaria
date_default_timezone_set('America/La_Paz');

// Verificar si el jugador ya completó todos sus juegos
$sqlJugadorEstado = "SELECT idEstado, juego_actual, 
    (SELECT COUNT(*) FROM juegos WHERE idEvento = ?) as total_juegos 
    FROM jugadores WHERE id = ?";
$stmtJugadorEstado = $conexion->prepare($sqlJugadorEstado);
$stmtJugadorEstado->bind_param("ii", $eventoId, $jugadorId);
$stmtJugadorEstado->execute();
$jugadorEstado = $stmtJugadorEstado->get_result()->fetch_assoc();

// Si el jugador ya completó todos los juegos, actualizar su estado
if ($jugadorEstado['juego_actual'] >= $jugadorEstado['total_juegos'] && $jugadorEstado['idEstado'] != 3) {
    $sqlUpdateEstado = "UPDATE jugadores SET idEstado = 3, tiempo_fin = CURRENT_TIMESTAMP WHERE id = ?";
    $stmtUpdate = $conexion->prepare($sqlUpdateEstado);
    $stmtUpdate->bind_param("i", $jugadorId);
    $stmtUpdate->execute();
}

// Verificar si todos los jugadores han terminado
$sqlJugadoresActivos = "SELECT COUNT(*) as total, 
    SUM(CASE WHEN idEstado = 3 THEN 1 ELSE 0 END) as terminados 
    FROM jugadores WHERE idEvento = ?";
$stmtJugadores = $conexion->prepare($sqlJugadoresActivos);
$stmtJugadores->bind_param("i", $eventoId);
$stmtJugadores->execute();
$jugadores = $stmtJugadores->get_result()->fetch_assoc();

// Obtener información del evento
$sqlEvento = "SELECT fechaFin FROM eventos WHERE id = ?";
$stmtEvento = $conexion->prepare($sqlEvento);
$stmtEvento->bind_param("i", $eventoId);
$stmtEvento->execute();
$evento = $stmtEvento->get_result()->fetch_assoc();

// Verificar si el tiempo ha terminado
$fechaActual = new DateTime('now', new DateTimeZone('America/La_Paz'));
$fechaFin = new DateTime($evento['fechaFin'], new DateTimeZone('America/La_Paz'));
$tiempoTerminado = $fechaActual >= $fechaFin;

$todosTerminaron = $jugadores['total'] == $jugadores['terminados'] || $tiempoTerminado;

// Si el jugador ha completado todos los juegos, destruir su sesión individual
if ($jugadorEstado['juego_actual'] >= 7 || $tiempoTerminado || $todosTerminaron) {
    // Guardar temporalmente los datos necesarios antes de destruir la sesión
    $eventoIdTemp = $eventoId;
    $jugadorIdTemp = $jugadorId;
    
    // Destruir la sesión del jugador
    unset($_SESSION['player_id']);
    unset($_SESSION['player_evento_id']);
    unset($_SESSION['player_name']);
    unset($_SESSION['jugador_actual']);
    unset($_SESSION['evento_actual']);
    
    // Establecer una cookie de "finalizado" para prevenir la navegación hacia atrás
    setcookie('evento_completado', 'true', time() + (86400), '/'); // 24 horas de duración
    
    // Restaurar variables temporales para uso en esta página
    $eventoId = $eventoIdTemp;
    $jugadorId = $jugadorIdTemp;
}

// Obtener tabla de posiciones
$sqlPosiciones = "SELECT nombres, puntaje 
                 FROM jugadores 
                 WHERE idEvento = ? 
                 ORDER BY puntaje DESC, tiempo_fin ASC";
$stmtPosiciones = $conexion->prepare($sqlPosiciones);
$stmtPosiciones->bind_param("i", $eventoId);
$stmtPosiciones->execute();
$posiciones = $stmtPosiciones->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener al ganador si todos terminaron o el tiempo terminó
$ganador = $todosTerminaron ? $posiciones[0]['nombres'] : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fin del Juego</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            text-align: center;
        }
        
        .container{
            padding: 10px;
        }
        .text-dark {
            color: #1a1a1a !important;
        }

        .feedback-form{
            padding: 0;
        }
        .feedback-form textarea {
            background-color: #fff;
            border: 1px solid #28a745;
            color: #1a1a1a;
            padding: 0;
        }

        .feedback-form textarea::placeholder {
            color: #6c757d;
        }

        .fw-bold {
            font-weight: 600 !important;
        }

        /* Estilo neón para el mensaje de felicitación */
        h2.text-center {
            text-shadow: 0 0 10px #00ff00,
                         0 0 20px #00ff00,
                         0 0 30px #00ff00;
            color: #fff;
            margin: 20px 0;
        }

        /* Estilo neón para el timer */
        #timer, .winer{
            text-shadow: 0 0 5px #00ff00,
                         0 0 5px #00ff00,
                         0 0 5px #00ff00;
            color: #000;
            font-weight: bold;
        }
        .winer{
            font-family: cursive;
            font-size: xx-large
        }
    </style>
    <!-- Agregar meta tags para prevenir caché -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script>
        // Prevenir navegación hacia atrás de manera más agresiva
        window.onload = function() {
            if (window.history && window.history.pushState) {
                window.history.pushState('forward', null, '');
                window.history.forward();
                
                window.onpopstate = function(event) {
                    window.history.pushState('forward', null, '');
                    window.location.replace('../index.php');
                };
            }
        }
        
        // Prevenir atajos de teclado para navegación
        document.addEventListener('keydown', function(e) {
            if ((e.keyCode == 116) || // F5
                (e.keyCode == 82 && e.ctrlKey) || // Ctrl + R
                (e.keyCode == 37 && e.altKey) || // Alt + Flecha izquierda
                (e.keyCode == 39 && e.altKey)) { // Alt + Flecha derecha
                e.preventDefault();
            }
        });

        // Si se intenta salir de la página
        window.onbeforeunload = function() {
            if (!event.target.href) {
                window.location.href = '../index.php';
            }
        };
    </script>
</head>
<body>
    <div class="container d-flex flex-column align-items-center">
        <div class="card bg-dark text-light px-4 py-0">
            <?php if ($todosTerminaron): ?>
                <div class="alert alert-success text-dark mt-3 mb-0">
                    <h2 class="text-dark fw-bold">¡Evento Finalizado!</h2>
                    <h3 class="text-dark text-center my-2">Ganador/a del evento: <span class="winer"><?php echo htmlspecialchars($ganador); ?></span></h3>
                    <p class="text-dark fw-bold">¡Felicitaciones al ganador y a todos los participantes!</p>
                    
                    <!-- Formulario de Feedback -->
                    <div class="feedback-form">
                        <h4 class="text-dark fw-bold m-2">¡Cuéntanos tu experiencia!</h4>
                        <form id="feedbackForm" method="POST" action="../controllers/feedbackController.php">
                            <input type="hidden" name="idJugador" value="<?php echo $jugadorId; ?>">
                            <input type="hidden" name="idEvento" value="<?php echo $eventoId; ?>">
                            <div class="form-group">
                                <textarea class="form-control" name="comentarios" rows="4" 
                                placeholder=" Compartenos tus comentarios, sugerencias o experiencia..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <!-- Botón de Resumen del Evento -->
                                <button type="button" 
                                        onclick="verResumenEvento('<?php echo base64_encode($eventoId . '_' . time()); ?>')" 
                                        class="btn btn-info">Ver Resumen del Evento y Salir</button>
                                <button type="submit" class="btn btn-success">Enviar y Salir</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <h2 class="text-center my-2">¡Felicidades! Has completado todos los retos</h2>
                <div id="countdown" class="text-center">
                    <h4 style="margin: 0;">Tiempo restante del evento:</h4>
                    <div id="timer" class="display-4"></div>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Jugador</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posiciones as $index => $jugador): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($jugador['nombres']); ?></td>
                                <td><img src="../images/key.png" alt="Imagen de llave" class="" width="20"> <?php echo $jugador['puntaje']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const fechaFin = new Date('<?php echo $fechaFin->format('Y-m-d H:i:s'); ?>').getTime();

        const timer = setInterval(function() {
            const ahora = new Date().getTime();
            const diferencia = fechaFin - ahora;

            const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

            const timerElement = document.getElementById("timer");
            if (timerElement) {
                timerElement.innerHTML = dias + "d " + horas + "h " + minutos + "m " + segundos + "s ";

                if (diferencia < 0) {
                    clearInterval(timer);
                    timerElement.innerHTML = "EVENTO FINALIZADO";
                    location.reload();
                }
            }
        }, 1000);

        // Actualizar la página cada 30 segundos solo si no han terminado todos
        <?php if (!$todosTerminaron): ?>
            setInterval(function() {
                location.reload();
            }, 30000);
        <?php endif; ?>

        function verResumenEvento(hash) {
            window.location.href = 'resumenEvento.php?token=' + hash;
        }
    </script>
</body>
</html>