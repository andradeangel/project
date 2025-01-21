<?php 
require_once '../controllers/eventoController.php';
require_once('../database.php');
custom_session_start('player_session');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si existe la sesión del jugador
if (!isset($_SESSION['player_id']) || !isset($_SESSION['player_evento_id'])) {
    header('Location: /');
    exit;
}

// Verificar el estado del jugador
$jugadorId = $_SESSION['player_id'];
$sql = "SELECT juego_actual, idEstado FROM jugadores WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $jugadorId);
$stmt->execute();
$jugador = $stmt->get_result()->fetch_assoc();

// Si el jugador está en estado "Terminado" (3), redirigir a finJuego.php
if ($jugador['idEstado'] == 3) {
    header('Location: finJuego.php');
    exit;
}

// Verificar si el jugador ya terminó todos los juegos
if ($jugador['juego_actual'] > 6) {
    //Actualizar el estado del jugador a "Terminado" (3)
    $sqlUpdate = "UPDATE jugadores SET idEstado = 3 WHERE id = ?";
    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->bind_param('i', $jugadorId);
    $stmtUpdate->execute();
    
    header('Location: finJuego.php');
    exit;
}

// Obtener datos del evento actual
$eventoId = $_SESSION['player_evento_id'];
$query = "SELECT e.*, s.nombre as tematica 
          FROM eventos e 
          LEFT JOIN sprint s ON e.idSprint = s.id 
          WHERE e.id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param('i', $eventoId);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

// Guardar datos del evento en la sesión
$_SESSION['evento_actual'] = [
    'id' => $evento['id'],
    'nombre' => $evento['nombre'],
    'tematica' => $evento['tematica'] ?? 'Temática no disponible'
];

// Obtener datos del jugador
$jugadorId = $_SESSION['player_id'];
$query = "SELECT * FROM jugadores WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param('i', $jugadorId);
$stmt->execute();
$jugador = $stmt->get_result()->fetch_assoc();

// Guardar datos del jugador en la sesión
$_SESSION['jugador_actual'] = [
    'id' => $jugador['id'],
    'nombres' => $jugador['nombres']
];

// Manejar la solicitud de abandono
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'abandonar') {
    header('Content-Type: application/json');
    
    $jugadorId = $_POST['jugadorId'];
    $sql = "UPDATE jugadores SET idEstado = 3 WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $jugadorId);
    
    $response = ['success' => $stmt->execute()];
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andrade Ángel">
    <title>Evento</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="evento-forms text-center">
            <div id="evento-container" class="card p-4 bg-dark text-light"> 
                <div class="d-flex justify-content-center align-items-center w-35">
                    <h6 class="text-light mb-0 me-2 text-start w-35">Evento:</h6>
                    <h4 class="mb-0 neon-text gamer-text"><?= $_SESSION['player_evento_nombre'] ?? 'Nombre no disponible' ?></h4>
                </div>
                <div class="d-flex justify-content-center align-items-center w-35">
                    <h6 class="text-light mb-0 me-2 text-start w-35">Temática:</h6>
                    <h4 class="mb-0 neon-text gamer-text"><?= $tematica ?? 'Descripción no disponible' ?></h4>
                </div>
                <div class="d-flex justify-content-center align-items-center w-35">
                    <h6 class="text-light mb-0 me-2 text-start w-35">Jugador/a:</h6>
                    <h4 class="mb-0 neon-text gamer-text"><?= $_SESSION['jugador_actual']['nombres'] ?></h4>
                </div>
                <div>
                    <div id="countdown" class="display-5 neon-countdown gamer-text my-2"></div>
                </div>
                <h6 class="text-light"> 👇Lista de juegos y retos a ser completados👇</h6>
                <div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($juegos as $index => $juego):
                            $juego_actual = $jugador['juego_actual'] ?? 1;
                            $disabled = ($index + 1) !== $juego_actual ? 'disabled' : '';
                            $opacity = ($index + 1) !== $juego_actual ? 'opacity: 0.5;' : '';
                        ?>
                        <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center" style="<?php echo $opacity; ?>">
                            <button type="button" class="btn btn-success btn-sm" 
                                    <?php echo $disabled; ?>
                                    onclick="window.location.href='<?php echo $juego['direccion']; ?>?juego_id=<?php echo $juego['id']; ?>&descripcion=<?php echo urlencode($juego['descripcion']); ?>'">
                                <i class="fas fa-play"></i>
                            </button>
                            <span><img src="../images/key.png" alt="Imagen de llave"> <?php echo $juego['nombre']; ?></span>
                            <span>
                                <?php if (($index + 1) < $juego_actual): ?>
                                    <img src="../images/padlock-open.png" alt="Candado abierto" class="img-fluid">
                                <?php else: ?>
                                    <img src="../images/padlock-closed.png" alt="Candado cerrado" class="img-fluid">
                                <?php endif; ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <form id="evento-form" method="POST" action="">
                    <button type="button" onclick="mostrarTablaPosiciones()" class="btn btn-primary btn-block btn-lg mt-2">Mostrar tabla de posiciones</button>
                    <button type="button" onclick="confirmarAbandonar()" class="btn btn-secondary btn-block btn-lg mt-2">Abandonar Evento</button>
                </form>
            </div>  
        </div>
    </div>

    <!-- Modal de tabla de posiciones -->
    <div class="modal fade" id="modalTablaPosiciones" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tabla de posiciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            <th>Nombre</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPosicionesBody" class="top-group">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <!-- Agregar este nuevo modal para confirmación de abandono -->
    <div class="modal fade" id="modalConfirmarAbandono" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar abandono</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro que deseas abandonar el evento?</p>
                    <p>No podrás volver a ingresar.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="abandonarEvento()">Abandonar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarTablaPosiciones() {
            $('#modalTablaPosiciones').modal('show');
            var tabla = $('#tablaPosicionesBody');
            tabla.empty();
            var jugadores = <?= json_encode($_SESSION['jugadores']) ?>;
            jugadores.sort(function(a, b) {
                return b.puntaje - a.puntaje; // Ordenar de mayor a menor según puntaje
            });
            $.each(jugadores, function(index, jugador) {
                tabla.append('<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + jugador.nombres + '</td>' +
                    '<td><img src="../images/key.png" alt="Imagen de llave" class="img-fluid"> ' + jugador.puntaje + '</td>' +
                    '</tr>');
            });
        }

        function confirmarAbandonar() {
            // Mostrar el modal en lugar del confirm
            $('#modalConfirmarAbandono').modal('show');
        }

        function abandonarEvento() {
            var formData = new FormData();
            formData.append('action', 'abandonar');
            formData.append('jugadorId', <?php echo $_SESSION['player_id']; ?>);

            fetch('evento.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                window.location.href = '../index.php';
            })
            .catch(error => {
                // Aquí también podrías mostrar el error en un modal estilizado
                alert('Error al abandonar el evento');
            });
        }

        // Convertir la fecha de fin a timestamp
        const fechaFin = new Date('<?php echo $evento['fechaFin']; ?>').getTime();

        const timer = setInterval(function() {
            const ahora = new Date().getTime();
            const diferencia = fechaFin - ahora;

            const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = 
                dias + "d " + horas + "h " + minutos + "m " + segundos + "s ";

            if (diferencia < 0) {
                clearInterval(timer);
                document.getElementById("countdown").innerHTML = "¡TIEMPO TERMINADO!";
                window.location.href = 'finJuego.php';
            }
        }, 1000);
    </script>
</body>
</html>
