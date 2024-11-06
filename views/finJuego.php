<?php
require_once("../database.php");
custom_session_start('player_session');

if (!isset($_SESSION['player_id'])) {
    header("Location: ../index.php");
    exit();
}

$jugadorId = $_SESSION['player_id'];
$eventoId = $_SESSION['player_evento_id'];

// Establecer zona horaria para Bolivia
date_default_timezone_set('America/La_Paz');

// Actualizar estado del jugador actual a "Terminado"
$sqlUpdateEstado = "UPDATE jugadores SET idEstado = 3 WHERE id = ?";
$stmtUpdate = $conexion->prepare($sqlUpdateEstado);
$stmtUpdate->bind_param("i", $jugadorId);
$stmtUpdate->execute();

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

// Verificar si el tiempo ha terminado usando la zona horaria correcta
$fechaActual = new DateTime('now', new DateTimeZone('America/La_Paz'));
$fechaFin = new DateTime($evento['fechaFin'], new DateTimeZone('America/La_Paz'));
$tiempoTerminado = $fechaActual >= $fechaFin;

$todosTerminaron = $jugadores['total'] == $jugadores['terminados'] || $tiempoTerminado;

// Obtener tabla de posiciones
$sqlPosiciones = "SELECT nombres, puntaje FROM jugadores WHERE idEvento = ? ORDER BY puntaje DESC";
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
</head>
<body>
    <div class="container d-flex flex-column align-items-center vh-100">
        <div class="card bg-dark text-light p-4">
            <?php if ($todosTerminaron): ?>
                <div class="alert alert-success text-center">
                    <h2>¡Evento Finalizado!</h2>
                    <?php if ($tiempoTerminado): ?>
                        <h3>El tiempo del evento ha terminado</h3>
                    <?php endif; ?>
                    <h3>El ganador es: <?php echo htmlspecialchars($ganador); ?></h3>
                    <p>¡Felicitaciones al ganador y a todos los participantes!</p>
                    
                    <!-- Formulario de Feedback -->
                    <div class="feedback-form mt-4">
                        <h4>¡Cuéntanos tu experiencia!</h4>
                        <p class="text-muted">Nos gustaría saber qué te pareció el evento. Tus comentarios nos ayudan a mejorar.</p>
                        <form id="feedbackForm" method="POST" action="../controllers/feedbackController.php">
                            <input type="hidden" name="idJugador" value="<?php echo $jugadorId; ?>">
                            <input type="hidden" name="idEvento" value="<?php echo $eventoId; ?>">
                            <div class="form-group">
                                <textarea class="form-control" name="comentarios" rows="4" 
                                    placeholder="Comparte tus comentarios, sugerencias o experiencia..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" class="btn btn-success" style="margin: 10px;">Enviar Comentarios</button>
                                <a href="https://lapuerta.net/" class="btn btn-secondary" style="margin: 10px;">Visita nuestra página</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <h2 class="text-center mb-4">¡Felicidades! Has completado todos los retos</h2>
                <div id="countdown" class="text-center mb-4">
                    <h4>Tiempo restante del evento:</h4>
                    <div id="timer" class="display-4"></div>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <h4 class="text-center mb-3">Tabla de Posiciones</h4>
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
        // Convertir la fecha de fin a timestamp considerando la zona horaria
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
    </script>
</body>
</html>