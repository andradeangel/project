<?php
require_once('../database.php');
custom_session_start('admin_session');

if (!isset($_SESSION['pending_challenges'])) {
    $_SESSION['pending_challenges'] = [];
}

error_log("Contenido de pending_challenges en monitoreo: " . print_r($_SESSION['pending_challenges'], true));
    require_once("../models/eventosModel.php");
    require_once("../controllers/monitoreoController.php");

    error_log("Contenido de _SESSION en monitoreo.php: " . print_r($_SESSION, true));
error_log("Desafíos pendientes: " . print_r($_SESSION['pending_challenges'] ?? [], true));

    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
    if (!isset($_SESSION['pending_challenges'])) {
        $_SESSION['pending_challenges'] = [];
    }
    error_log("Desafíos pendientes en monitoreo.php: " . print_r($_SESSION['pending_challenges'], true));

    // Obtener el nombre de usuario y rol
    $user_id = $_SESSION['admin_id'];
    $usuario = obtenerUsuario($conexion, $user_id);
    $nombre_usuario = $usuario['nombres'] ?? 'Usuario desconocido';
    $rol_usuario = $usuario['nombre_rol'] ?? 'Rol no definido';

    $controller = new MonitoreoController($conexion);
    $eventosEnProceso = $controller->getEventosEnProceso();
    $pendingChallenges = $controller->getPendingChallenges();

    // Depuración
    error_log("Pending challenges: " . print_r($pendingChallenges, true));

    function formatearFecha($fecha) {
        $datetime = new DateTime($fecha);
        return $datetime->format('d/m/y H:i');
    }

    // Agregar log para depuración
    error_log("Desafíos pendientes en monitoreo.php: " . print_r($_SESSION['pending_challenges'] ?? [], true));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <img src="../images/logo.png" height="30" class="d-inline-block align-top" alt="Logo">
            <div class="brand-text">
                <span class="panel-title">Panel de Control</span>
                <span class="welcome-text">Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($rol_usuario); ?></span>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link" href="eventos.php">Eventos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link active" href="">Monitoreo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link" href="sprints.php">Sprints</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link" href="consultas.php">Consultas</a>
                    </li>
                </ul>
                <button class="btn btn-outline-light custom-logout-btn" onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active fijarHeader" id="monitorear" role="tabpanel" aria-labelledby="nav-monitorear-tab">
                        <h3>Pendientes de revisión</h3>
                        
                        <!-- Sección de Solicitudes -->
                        <div class="mt-4">
                            <div class="row">
                                <?php if (isset($_SESSION['pending_challenges']) && !empty($_SESSION['pending_challenges'])): ?>
                                    <?php foreach ($_SESSION['pending_challenges'] as $challengeId => $challenge): ?>
                                        <div class="col-md-4 mb-4" id="challenge-<?php echo htmlspecialchars($challengeId); ?>">
                                            <div class="card">
                                                <?php if ($challenge['gameType'] === 'photo'): ?>
                                                    <img src="<?php echo htmlspecialchars($challenge['challenge']); ?>" 
                                                         class="card-img-top" alt="Foto para revisión">
                                                <?php elseif ($challenge['gameType'] === 'video'): ?>
                                                    <video controls class="card-img-top">
                                                        <source src="<?php echo htmlspecialchars($challenge['challenge']); ?>" type="video/mp4">
                                                        Tu navegador no soporta el elemento de video.
                                                    </video>
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <strong>Evento:</strong> <?php echo htmlspecialchars($challenge['eventoNombre'] ?? 'No definido'); ?>
                                                    </h5>
                                                    <p class="card-text">
                                                        <strong>Jugador:</strong> <?php echo htmlspecialchars($challenge['jugadorNombre'] ?? 'No definido'); ?><br>
                                                        <strong>Descripción:</strong> <?php echo htmlspecialchars($challenge['gameDescription'] ?? 'No disponible'); ?><br>
                                                    </p>
                                                    <br>
                                                    <button class="btn btn-success btn-sm" 
                                                            onclick="calificarDesafio('<?php echo $challengeId; ?>', 'aprobado')">
                                                        Aprobar
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="calificarDesafio('<?php echo $challengeId; ?>', 'rechazado')">
                                                        Reprobar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <p>No hay desafíos pendientes de calificación.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Sección de Eventos en Proceso -->
                        <div class="mt-4">
                            <h3>Eventos en Proceso</h3>
                            <div class="row">
                                <?php if (empty($eventosEnProceso)): ?>
                                    <p>No hay eventos en proceso actualmente.</p>
                                <?php else: ?>
                                    <?php foreach ($eventosEnProceso as $evento): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($evento['nombre']); ?></h5>
                                                    <p class="card-text">
                                                        <strong>Código:</strong> <?php echo htmlspecialchars($evento['codigo']); ?><br>
                                                        <strong>Fecha Inicio:</strong> <?php echo formatearFecha($evento['fechaInicio']); ?><br>
                                                        <strong>Fecha Fin:</strong> <?php echo formatearFecha($evento['fechaFin']); ?><br>
                                                        <strong>Estado:</strong> En proceso
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calificarDesafio(challengeId, status) {
            console.log("Intentando calificar desafío:", challengeId, status);
            fetch('../controllers/calificarDesafio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ challengeId: challengeId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                console.log("Respuesta del servidor:", data);
                if (data.success) {
                    document.getElementById('challenge-' + challengeId).remove();
                    alert(data.message);
                } else {
                    alert('Error al calificar el desafío: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }
        
        function confirmarCerrarSesion() {
            if (confirm("¿Está seguro de que desea cerrar sesión?")) {
                window.location.href = "/";
            }
        }
        // Actualizar estados de eventos cada 30 segundos
        setInterval(function() {
            $.ajax({
                type: 'GET',
                url: '../controllers/actualizarEstadosEventos.php', // Cambia la dirección aquí
                success: function(data) {
                console.log('Actualización de estados de eventos realizada con éxito');
                },
                error: function(xhr, status, error) {
                console.error('Error al actualizar estados de eventos:', error);
                }
            });
        }, 30000);

        // Recargar la página cada 30 segundos
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

