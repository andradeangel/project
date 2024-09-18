<?php
    session_start();
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    require_once("../database.php");
    require_once("../models/eventosModel.php");
    require_once("../models/sprintsModel.php");
    require_once("../controllers/sprintsController.php");


    $controller = new SprintController($conexion);
    $sprints = $controller->getAllSprints();
    $juegos = $controller->getAllJuegos();

    // Obtener nombre de usuario y rol (asumiendo que están definidos en eventosModel.php)
    $user_id = $_SESSION['user_id'];
    $user = obtenerUsuario($conexion, $user_id);
    $nombre_usuario = $user['nombres'];
    $rol_usuario = $user['nombre_rol'];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                        <a class="nav-link custom-nav-link" href="monitoreo.php">Monitoreo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link active" href="">Sprints</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link" href="consultas.php">Consultas</a>
                    </li>
                </ul>
                <button class="btn btn-outline-light custom-logout-btn" onclick="confirmarCerrarSesion()">Cerrar Sesión</button>
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
                <table class="table table-dark table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                Nombre
                                <a href="?orderBy=nombre&orderDir=<?php echo $orderBy == 'nombre' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                    <?php echo $orderBy == 'nombre' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
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
                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $sprint['id']; ?>">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/sprints.js"></script><script>
        function confirmarCerrarSesion() {
            if (confirm("¿Está seguro de que desea cerrar sesión?")) {
                window.location.href = "/";
            }
        }

        function editSprint(id) {
            $.ajax({
                url: '../controllers/sprintsController.php',
                type: 'POST',
                data: {action: 'get', id: id},
                dataType: 'json',
                success: function(sprint) {
                    $('#sprintId').val(sprint.id);
                    $('#sprintNombre').val(sprint.nombre);
                    $('#juego1').val(sprint.idJuego1);
                    $('#juego2').val(sprint.idJuego2);
                    $('#juego3').val(sprint.idJuego3);
                    $('#juego4').val(sprint.idJuego4);
                    $('#juego5').val(sprint.idJuego5);
                    $('#juego6').val(sprint.idJuego6);
                    $('#sprintModal').modal('show');
                }
            });
        }

        function deleteSprint(id) {
            if (confirm("¿Está seguro de que desea eliminar este sprint?")) {
                $.ajax({
                    url: '../controllers/sprintsController.php',
                    type: 'POST',
                    data: {action: 'delete', id: id},
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error al eliminar el sprint');
                        }
                    }
                });
            }
        }

        function saveSprint() {
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
                    if (response.success) {
                        $('#sprintModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error al guardar el sprint');
                    }
                }
            });

            function confirmarCerrarSesion() {
                if (confirm("¿Está seguro de que desea cerrar sesión?")) {
                    window.location.href = "/";
                }
            }
        }
    </script>
</body>
</html>