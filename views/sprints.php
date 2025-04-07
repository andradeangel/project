<?php
require_once("../database.php");
require_once("../models/eventosModel.php");
custom_session_start('admin_session');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once("../models/sprintsModel.php");
require_once("../controllers/sprintsController.php");

$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'nombre';
$orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'ASC';
$controller = new SprintController($conexion);
$sprints = $controller->getAllSprints($orderBy, $orderDir);
$juegos = $controller->getAllJuegos();

// Obtener el nombre de usuario y rol
$user_id = $_SESSION['admin_id'];
$usuario = obtenerUsuario($conexion, $user_id);
$nombre_usuario = $usuario['nombres'] ?? 'Usuario desconocido';
$rol_usuario = $usuario['nombre_rol'] ?? 'Rol no definido';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="icon" type="image/x-icon" href="../images/ico.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/ico.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/ico.png">
    <link rel="shortcut icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
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
                        <a class="nav-link custom-nav-link" href="eventos.php">Eventos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link" href="monitoreo.php">Monitoreo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link active" href="">Sprints</a>
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
                    <h2>Sprints</h2>
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createSprintModal">
                        Crear Sprint    
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    Nombre
                                    <a href="?orderBy=nombre&orderDir=<?php echo ($orderBy === 'nombre' && $orderDir === 'ASC') ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <i class="fas fa-sort<?php 
                                            if ($orderBy === 'nombre') {
                                                echo $orderDir === 'ASC' ? '-up' : '-down';
                                            }
                                        ?>"></i>
                                    </a>
                                </th>
                                <th>Juego 1</th>
                                <th>Juego 2</th>
                                <th>Juego 3</th>
                                <th>Juego 4</th>
                                <th>Juego 5</th>
                                <th>Juego 6</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="sprintsTableBody">
                            <?php foreach($sprints as $sprint): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sprint['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($sprint['juego1']); ?></td>
                                <td><?php echo htmlspecialchars($sprint['juego2']); ?></td>
                                <td><?php echo htmlspecialchars($sprint['juego3']); ?></td>
                                <td><?php echo htmlspecialchars($sprint['juego4']); ?></td>
                                <td><?php echo htmlspecialchars($sprint['juego5']); ?></td>
                                <td><?php echo htmlspecialchars($sprint['juego6']); ?></td>
                                <td>
                                    <button class="btn btn-sm bg-primary edit-btn" data-id="<?php echo $sprint['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $sprint['id']; ?>" type="button">
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

    <!-- Modal para crear sprint -->
    <div class="modal fade" id="createSprintModal" tabindex="-1" aria-labelledby="createSprintModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSprintModalLabel">Crear Nuevo Sprint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createSprintForm">
                        <div class="mb-3">
                            <label for="createSprintNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="createSprintNombre" name="nombre" required>
                        </div>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="mb-3">
                            <label for="createJuego<?php echo $i; ?>" class="form-label">Juego <?php echo $i; ?></label>
                            <select class="form-select" id="createJuego<?php echo $i; ?>" name="juego<?php echo $i; ?>" placeholder="Seleccione un juego">
                                <?php foreach ($juegos as $juego): ?>
                                    <option value="<?php echo $juego['id']; ?>"><?php echo htmlspecialchars($juego['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endfor; ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveNewSprintBtn">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar sprint -->
    <div class="modal fade" id="sprintModal" tabindex="-1" aria-labelledby="sprintModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="sprintModalLabel">Editar Sprint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSprintForm">
                        <input type="hidden" id="sprintId" name="id">
                        <div class="mb-3">
                            <label for="sprintNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="sprintNombre" name="nombre" required>
                        </div>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="mb-3">
                            <label for="juego<?php echo $i; ?>" class="form-label">Juego <?php echo $i; ?></label>
                            <select class="form-select" id="juego<?php echo $i; ?>" name="juego<?php echo $i; ?>">
                                <?php foreach ($juegos as $juego): ?>
                                    <option value="<?php echo $juego['id']; ?>"><?php echo htmlspecialchars($juego['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endfor; ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveSprintBtn">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>

    <div id="customModal" class="custom-modal" style="display: none;">
        <h3 id="modalTitle"></h3>
        <p id="modalMessage"></p>
        <button onclick="closeCustomModal()">Aceptar</button>
    </div>

    <div id="confirmModal" class="custom-modal" style="display: none;">
        <h3>Confirmar eliminación</h3>
        <p>¿Está seguro que desea eliminar este sprint?</p>
        <button onclick="confirmDelete()">Eliminar</button>
        <button onclick="closeConfirmModal()">Cancelar</button>
    </div>

    <!-- Modal de confirmación para cerrar sesión -->
    <div id="logoutConfirmModal" class="custom-modal" style="display: none;">
        <h3>Confirmar cierre de sesión</h3>
        <p>¿Está seguro de que desea cerrar la sesión?</p>
        <button onclick="confirmLogout()" class="btn btn-danger">Cerrar Sesión</button>
        <button onclick="closeLogoutModal()" class="btn btn-secondary">Cancelar</button>
    </div>

    <!-- Overlay para el fondo oscuro -->
    <div id="modalOverlay" class="modal-overlay"></div>

    <!-- Referencia personal-->
    <div class="pin">
        <pre>
Desarrollado por Andrade Angel
andradevelop@gmail.com
</pre>
    </div> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/sprints.js"></script>
    
    <script>
        function showCustomMessage(title, message) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').textContent = message;
        document.getElementById('customModal').style.display = 'block';
        }

        function closeCustomModal() {
            document.getElementById('customModal').style.display = 'none';
            if (document.getElementById('modalTitle').textContent === 'Éxito') {
                location.reload();
            }
        }

        let sprintToDelete = null;

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            sprintToDelete = null;
        }

        function saveSprint() {
            // Validar campos requeridos
            if (!$('#sprintNombre').val() || !$('#juego1').val() || !$('#juego2').val() || 
                !$('#juego3').val() || !$('#juego4').val() || !$('#juego5').val() || !$('#juego6').val()) {
                showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
                return;
            }

            var formData = {
                action: $('#sprintId').val() ? 'update' : 'create',
                id: $('#sprintId').val(),
                nombre: $('#sprintNombre').val(),
                juego1: $('#juego1').val(),
                juego2: $('#juego2').val(),
                juego3: $('#juego3').val(),
                juego4: $('#juego4').val(),
                juego5: $('#juego5').val(),
                juego6: $('#juego6').val()
            };

            $.ajax({
                url: '../controllers/sprintsController.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#sprintModal').modal('hide');
                    if (response.success) {
                        showCustomMessage('Éxito', formData.action === 'create' ? 
                            'Sprint creado con éxito' : 'Sprint modificado con éxito');
                    } else {
                        showCustomMessage('Error', 'Error al guardar el sprint');
                    }
                }
            });
        }
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar el event listener para el botón de cerrar sesión
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
    </script>
</body>
</html>
