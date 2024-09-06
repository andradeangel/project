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
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addSprintModal">
                        Crear Sprint
                    </button>
                </div>
                <table class="table table-dark table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Juego 1</th>
                            <th>Juego 2</th>
                            <th>Juego 3</th>
                            <th>Juego 4</th>
                            <th>Juego 5</th>
                            <th>Juego 6</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                <button class="btn btn-sm btn-warning" onclick="editSprint(<?php echo $sprint['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSprint(<?php echo $sprint['id']; ?>)">
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

    <!-- Modal para añadir/editar sprint -->
    <div class="modal fade" id="sprintModal" tabindex="-1" aria-labelledby="sprintModalLabel" aria-hidden="true">
        <!-- ... (el contenido del modal permanece igual) ... -->
    </div>

    <script>
    function confirmarCerrarSesion() {
        if (confirm("¿Está seguro de que desea cerrar sesión?")) {
            window.location.href = "/";
        }
    }

    function editSprint(id) {
        // Aquí deberías cargar los datos del sprint y mostrarlos en el modal
        $('#sprintId').val(id);
        $('#sprintModal').modal('show');
    }

    function deleteSprint(id) {
        if (confirm("¿Está seguro de que desea eliminar este sprint?")) {
            // Aquí deberías hacer una llamada AJAX para eliminar el sprint
        }
    }

    function saveSprint() {
        // Aquí deberías hacer una llamada AJAX para guardar o actualizar el sprint
        $('#sprintModal').modal('hide');
    }
    </script>
</body>
</html>