<?php
require_once('../database.php');
require_once('../controllers/monitoreoController.php');
require_once("../controllers/actualizarEstadosEventos.php");
custom_session_start('admin_session');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

// Verificar conexión
if (!isset($conexion)) {
    error_log("Error: No hay conexión a la base de datos en monitoreo.php");
    die("Error de conexión a la base de datos");
}

$controller = new MonitoreoController($conexion);

// Obtener el nombre de usuario y rol
$user_id = $_SESSION['admin_id'];
$usuario = obtenerUsuario($conexion, $user_id);
$nombre_usuario = $usuario['nombres'] ?? 'Usuario desconocido';
$rol_usuario = $usuario['nombre_rol'] ?? 'Rol no definido';
$desafiosPendientes = $controller->getPendingChallenges();
$eventosEnProceso = $controller->getEventosEnProceso();
$pendingChallenges = $controller->getPendingChallenges();

error_log("Pending challenges: " . print_r($pendingChallenges, true));
function formatearFecha($fecha) {
    $datetime = new DateTime($fecha);
    return $datetime->format('d/m/y H:i');
}

// Agregar log para depuración
error_log("Desafíos pendientes en monitoreo.php: " . print_r($_SESSION['pending_challenges'] ?? [], true));
error_log("Desafíos pendientes en monitoreo.php:");
error_log(print_r($pendingChallenges, true));

$uploadDir = "../uploads/challenges/";
if (is_dir($uploadDir)) {
    error_log("Directorio existe: " . $uploadDir);
    $files = scandir($uploadDir);
    error_log("Archivos en el directorio: " . print_r($files, true));
} else {
    error_log("Directorio NO existe: " . $uploadDir);
}

// Agregar al inicio del archivo, después de los requires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'terminarEvento') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    try {
        if (!isset($_POST['eventoId'])) {
            throw new Exception('ID del evento no proporcionado');
        }
        
        // Agregar log para debugging
        error_log("Recibida solicitud para terminar evento ID: " . $_POST['eventoId']);
        
        $controller = new MonitoreoController($conexion);
        $resultado = $controller->terminarEvento($_POST['eventoId']);
        
        if ($resultado) {
            $response['success'] = true;
            $response['message'] = 'Evento terminado exitosamente';
            error_log("Evento terminado exitosamente: " . $_POST['eventoId']);
        } else {
            throw new Exception('Error al terminar el evento');
        }
    } catch (Exception $e) {
        error_log("Error en terminarEvento: " . $e->getMessage());
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Panel de Control</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .custom-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 0, 0, 0.9); /* Fondo rojo */
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #ff4d4d; /* Borde rojo claro */
            color: #fff;
            text-align: center;
            z-index: 2000;
            min-width: 300px;
            font-family: 'Press Start 2P', cursive;
            animation: glow 2s infinite alternate;
        }

        .custom-modal h3 {
            color: #ff4d4d; /* Título en rojo claro */
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .custom-modal button {
            background-color: #ff4d4d; /* Botón en rojo claro */
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .custom-modal button:hover {
            transform: scale(1.05);
            background-color: #ff1a1a; /* Rojo más oscuro al pasar el mouse */
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
        }

        .custom-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #212529;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            z-index: 9999;
            color: white;
            min-width: 300px;
            text-align: center;
        }

        .custom-modal h3 {
            margin-bottom: 15px;
            color: white;
        }

        .custom-modal p {
            margin-bottom: 20px;
            color: white;
        }

        .custom-modal button {
            padding: 8px 15px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .custom-modal button:first-of-type {
            background-color: #dc3545;
            color: white;
        }

        .custom-modal button:last-of-type {
            background-color: #6c757d;
            color: white;
        }
        .pin > pre{
            font-size: .5rem;
            color: yellow;
            position: fixed;
            bottom: 0;
            left: 0;
            margin: 0;
            filter: opacity(0.3);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a href="../index.php"><img src="../images/logo.png" height="30" class="d-inline-block align-top" alt="Logo"></a>
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
                <a class="nav-link" href="#" onclick="showLogoutConfirm(event)">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
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
                                <?php if (!empty($pendingChallenges)): ?>
                                    <?php foreach ($pendingChallenges as $desafio): ?>
                                        <div class="col-md-4 mb-4" id="challenge-<?php echo htmlspecialchars($desafio['id']); ?>">
                                            <div class="card">
                                                <?php if ($desafio['tipo'] === 'photo'): ?>
                                                    <img src="../uploads/challenges/<?php echo htmlspecialchars($desafio['archivo_ruta']); ?>" 
                                                         class="card-img-top" 
                                                         alt="Foto para revisión"
                                                         style="object-fit: contain;">
                                                <?php elseif ($desafio['tipo'] === 'video'): ?>
                                                    <video controls class="card-img-top">
                                                        <source src="../uploads/challenges/<?php echo htmlspecialchars($desafio['archivo_ruta']); ?>" 
                                                                type="video/mp4">
                                                        Tu navegador no soporta la reproducción de videos.
                                                    </video>
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <strong>Jugador:</strong> <?php echo htmlspecialchars($desafio['jugador_nombre']); ?>
                                                    </h5>
                                                    <p class="card-text">
                                                        <strong>Evento:</strong> <?php echo htmlspecialchars($desafio['evento_nombre']); ?><br>
                                                        <strong>Descripción:</strong> <?php echo htmlspecialchars($desafio['game_description']); ?>
                                                    </p>
                                                    <button class="btn btn-success" 
                                                            onclick="calificarDesafio('<?php echo $desafio['id']; ?>', 'aprobado')">
                                                        Aprobar
                                                    </button>
                                                    <button class="btn btn-danger" 
                                                            onclick="calificarDesafio('<?php echo $desafio['id']; ?>', 'reprobado')">
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
                                                        <strong>Capacidad:</strong> <?php echo $evento['jugadores_actuales']; ?>/<?php echo $evento['personas']; ?>
                                                    </p>
                                                    <button class="btn btn-danger" 
                                                            onclick="confirmarTerminarEvento(<?php echo $evento['id']; ?>, '<?php echo htmlspecialchars($evento['nombre']); ?>')">
                                                        Terminar
                                                    </button>
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

    <div id="customModal" class="custom-modal" style="display: none;">
        <h3 id="modalTitle"></h3>
        <p id="modalMessage"></p>
        <button onclick="closeCustomModal()">Aceptar</button>
    </div>

    <div id="modalOverlay" class="modal-overlay"></div>
    
    <div id="logoutConfirmModal" class="custom-modal" style="display: none;">
        <h3>Confirmar cierre de sesión</h3>
        <p>¿Está seguro de que desea cerrar la sesión?</p>
        <button onclick="confirmLogout()" class="btn btn-danger">Cerrar Sesión</button>
        <button onclick="closeLogoutModal()" class="btn btn-secondary">Cancelar</button>
    </div>

    <div id="modalOverlay" class="modal-overlay"></div>

    <div id="terminarEventoModal" class="custom-modal" style="display: none;">
        <h3>Confirmar finalización</h3>
        <p id="terminarEventoMessage"></p>
        <div class="d-flex justify-content-center gap-2">
            <button onclick="terminarEvento()" class="btn btn-danger">Terminar</button>
            <button onclick="closeTerminarModal()" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>

    <!-- Referencia personal-->
    <div class="pin">
        <pre>
Desarrollado por Andrade Angel
andradevelop@gmail.com
</pre>
    </div> 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutButton = document.querySelector('a[href="#"][onclick="showLogoutConfirm(event)"]');
            if (logoutButton) {
                logoutButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    showLogoutConfirm(e);
                });
            }
        });

        function calificarDesafio(challengeId, status) {
            fetch('../controllers/calificarDesafio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    challengeId: challengeId, 
                    status: status 
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Respuesta del servidor:', text);
                        throw new Error(`Error al parsear JSON: ${e.message}`);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    showCustomMessage('Éxito', data.message);
                    // Ocultar el desafío calificado
                    const desafioElement = document.querySelector(`[data-desafio-id="${challengeId}"]`);
                    if (desafioElement) {
                        desafioElement.remove();
                    }
                    // Retrasar la recarga de la página por 10 segundos
                    setTimeout(() => {
                        location.reload();
                    }, 10000);
                } else {
                    showCustomMessage('Error', data.message || 'Error al procesar la solicitud');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomMessage('Error', 'Error al procesar la solicitud: ' + error.message);
            });
        }

        function showCustomMessage(title, message) {
            const modal = document.getElementById('customModal');
            const overlay = document.getElementById('modalOverlay');
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            modal.style.display = 'block';
            overlay.style.display = 'block';
        }

        function closeCustomModal() {
            const modal = document.getElementById('customModal');
            const overlay = document.getElementById('modalOverlay');
            modal.style.display = 'none';
            overlay.style.display = 'none';
            if (document.getElementById('modalTitle').textContent === 'Éxito') {
                location.reload();
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

        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.replace('/');
        }
        
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.replace('/');
            }
        };

        function showLogoutConfirm(event) {
            event.preventDefault();
            const overlay = document.getElementById('modalOverlay');
            const modal = document.getElementById('logoutConfirmModal');
            if (overlay && modal) {
                overlay.style.display = 'block';
                modal.style.display = 'block';
            }
        }

        function closeLogoutModal() {
            const overlay = document.getElementById('modalOverlay');
            const modal = document.getElementById('logoutConfirmModal');
            if (overlay && modal) {
                overlay.style.display = 'none';
                modal.style.display = 'none';
            }
        }

        function confirmLogout() {
            window.location.href = '../logout.php';
        }

        let eventoIdToTerminate = null;

        function confirmarTerminarEvento(eventoId, eventoNombre) {
            eventoIdToTerminate = eventoId;
            const modal = document.getElementById('terminarEventoModal');
            const message = document.getElementById('terminarEventoMessage');
            message.textContent = `¿Está seguro que desea terminar el evento "${eventoNombre}"?`;
            modal.style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function closeTerminarModal() {
            document.getElementById('terminarEventoModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
            eventoIdToTerminate = null;
        }

        function terminarEvento() {
            if (!eventoIdToTerminate) return;
            
            const formData = new FormData();
            formData.append('action', 'terminarEvento');
            formData.append('eventoId', eventoIdToTerminate);
            
            console.log('Enviando solicitud para terminar evento:', eventoIdToTerminate);
            
            fetch('monitoreo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                closeTerminarModal();
                console.log('Respuesta del servidor:', data);
                
                if (data.success) {
                    showCustomMessage('Éxito', 'El evento ha sido terminado exitosamente');
                    // Forzar recarga después de un breve delay
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Error al terminar el evento');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeTerminarModal();
                showCustomMessage('Error', error.message || 'Ocurrió un error al procesar la solicitud');
            });
        }
    </script>
</body>
</html>

