<?php
require_once("../database.php");
require_once("../controllers/actualizarEstadosEventos.php");
custom_session_start('admin_session');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once("../models/eventosModel.php");
require_once("../controllers/eventosController.php");
$user_id = $_SESSION['admin_id'];
$usuario = obtenerUsuario($conexion, $user_id);
$nombre_usuario = $usuario['nombres'] ?? 'Usuario';
$rol_usuario = $usuario['nombre_rol'] ?? 'Rol no definido';

// Procesar solicitud AJAX para crear evento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $resultado = ['success' => false, 'message' => 'Acción no válida'];
    switch ($accion) {
        case 'eliminar':
            $resultado = eliminarEvento($conexion, $_POST['id']);
            break;
        // Otras acciones...
    }
    header('Content-Type: application/json');
    echo json_encode($resultado);
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
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                        <a class="nav-link custom-nav-link active" href="">Eventos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link" href="monitoreo.php">Monitoreo</a>
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
                <div class="d-flex justify-content-between align-items-center mb-3 fijarHeader">
                    <h2>Eventos</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearEventoModal">
                        Crear Evento
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    Nombre
                                    <a href="?orderBy=e.nombre&orderDir=<?php echo $orderBy == 'e.nombre' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 'e.nombre' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵';?>
                                    </a>
                                </th>
                                <th>
                                    Código
                                    <a href="?orderBy=e.codigo&orderDir=<?php echo $orderBy == 'e.codigo' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 'e.codigo' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
                                    </a>
                                </th>
                                <th>
                                    Fecha Inicio
                                    <a href="?orderBy=e.fechaInicio&orderDir=<?php echo $orderBy == 'e.fechaInicio' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 'e.fechaInicio' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
                                    </a>
                                </th>
                                <th>
                                    Fecha Fin
                                    <a href="?orderBy=e.fechaFin&orderDir=<?php echo $orderBy == 'e.fechaFin' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 'e.fechaFin' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
                                    </a>
                                </th>
                                <th>
                                    Estado
                                    <a href="?orderBy=es.estado&orderDir=<?php echo $orderBy == 'es.estado' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 'es.estado' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
                                    </a>
                                </th>
                                <th>
                                    N°
                                    <a href="?orderBy=e.personas&orderDir=<?php echo $orderBy == 'e.personas' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 'e.personas' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
                                    </a>
                                </th>
                                <th>
                                    Sprint
                                    <a href="?orderBy=s.nombre&orderDir=<?php echo $orderBy == 's.nombre' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 's.nombre' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
                                    </a>
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td>
                                    <?php
                                    error_log("ID del evento: " . $evento['id']);
                                    ?>
                                    <a href="resumenEvento.php?id=<?php echo $evento['id']; ?>" 
                                       class="text-light" target="_blank"
                                       onclick="console.log('Clickeando evento ID: <?php echo $evento['id']; ?>')">
                                        <?php echo htmlspecialchars($evento['nombre']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($evento['codigo']); ?></td>
                                <td><?php echo formatDate($evento['fechaInicio']); ?></td>
                                <td><?php echo formatDate($evento['fechaFin']); ?></td>
                                <td><?php echo htmlspecialchars($evento['estado_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($evento['personas']); ?></td>
                                <td><?php echo htmlspecialchars($evento['sprint_nombre']); ?></td>
                                <td>
                                    <button class="btn btn-sm bg-primary edit-btn" data-id="<?php echo $evento['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $evento['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear evento -->
    <div class="modal fade" id="crearEventoModal" tabindex="-1" aria-labelledby="crearEventoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearEventoModalLabel">Crear Nuevo Evento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="crearEventoForm">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
                            <input type="datetime-local" class="form-control" id="fechaInicio" name="fechaInicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="fechaFin" class="form-label">Fecha de Finalización</label>
                            <input type="datetime-local" class="form-control" id="fechaFin" name="fechaFin" required>
                        </div>
                        <div class="mb-3">
                            <label for="personas" class="form-label">Capacidad de Personas</label>
                            <input type="number" class="form-control" id="personas" name="personas" min="0" value="5" required>
                        </div>
                        <div class="mb-3">
                            <label for="sprint" class="form-label">Sprint</label>
                            <select class="form-select" id="sprint" name="sprint" required>
                                <?php foreach ($sprints as $sprint): ?>
                                    <option value="<?php echo $sprint['id']; ?>"><?php echo htmlspecialchars($sprint['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="crearEventoBtn">Crear</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar evento -->
    <div class="modal fade" id="editarEventoModal" tabindex="-1" aria-labelledby="editarEventoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarEventoModalLabel">Editar Evento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editarEventoForm">
                        <input type="hidden" id="editarEventoId" name="id">
                        <div class="mb-3">
                            <label for="editarNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editarFechaInicio" class="form-label">Fecha de Inicio</label>
                            <input type="datetime-local" class="form-control" id="editarFechaInicio" name="fechaInicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="editarFechaFin" class="form-label">Fecha de Finalización</label>
                            <input type="datetime-local" class="form-control" id="editarFechaFin" name="fechaFin" required>
                        </div>
                        <div class="mb-3">
                            <label for="editarPersonas" class="form-label">Capacidad de Personas</label>
                            <input type="number" class="form-control" id="editarPersonas" name="personas" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="editarSprint" class="form-label">Sprint</label>
                            <select class="form-select" id="editarSprint" name="sprint" required>
                                <?php foreach ($sprints as $sprint): ?>
                                    <option value="<?php echo $sprint['id']; ?>"><?php echo htmlspecialchars($sprint['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editarDescripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editarDescripcion" name="descripcion" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="editarEventoBtn">Guardar cambios</button>
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

    <!-- Modal de confirmación para cerrar sesión -->
    <div id="logoutConfirmModal" class="custom-modal" style="display: none;">
        <h3>Confirmar cierre de sesión</h3>
        <p>¿Está seguro de que desea cerrar la sesión?</p>
        <button onclick="confirmLogout()" class="btn btn-danger">Cerrar Sesión</button>
        <button onclick="closeLogoutModal()" class="btn btn-secondary">Cancelar</button>
    </div>

    <!-- Referencia personal-->
    <div class="pin">
        <pre>
Desarrollado por Andrade Angel
andradevelop@gmail.com
</pre>
    </div> 
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
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

        function generarCodigoEvento() {
            return 'EV-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        }

        setInterval(function() {
            $.ajax({
                type: 'GET',
                url: '../controllers/actualizarEstadosEventos.php',
                success: function(data) {
                console.log('Actualización de estados de eventos realizada con éxito');
                },
                error: function(xhr, status, error) {
                console.error('Error al actualizar estados de eventos:', error);
                }
            });
        }, 30000);
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/eventos.js"></script>
    <script>
        console.log('Página de eventos cargada');
    </script>
    <script>
        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.replace('/');
        }
        
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.replace('/');
            }
        };
    </script>
</body>
</html>
