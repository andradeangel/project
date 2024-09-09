<?php
    session_start();
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    require_once("../database.php");
    require_once("../models/eventosModel.php");
    require_once("../controllers/eventosController.php");
?>
<!DOCTYPE html> 
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <button class="btn btn-outline-light custom-logout-btn" onclick="confirmarCerrarSesion()">Cerrar Sesión</button>
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
                                        <?php echo $orderBy == 'e.nombre' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
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
                                    Sprint
                                    <a href="?orderBy=s.nombre&orderDir=<?php echo $orderBy == 's.nombre' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-btn ms-2">
                                        <?php echo $orderBy == 's.nombre' ? ($orderDir == 'ASC' ? '▲' : '▼') : '⇵'; ?>
                                    </a>
                                </th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evento['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($evento['codigo']); ?></td>
                                <td><?php echo formatDate($evento['fechaInicio']); ?></td>
                                <td><?php echo formatDate($evento['fechaFin']); ?></td>
                                <td><?php echo htmlspecialchars($evento['estado_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($evento['sprint_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($evento['descripcion']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="<?php echo $evento['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $evento['id']; ?>)">
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        function confirmarCerrarSesion() {
            if (confirm("¿Está seguro de que desea cerrar sesión?")) {
                window.location.href = "/";
            }
        }

        document.getElementById('crearEventoBtn').addEventListener('click', function() {
            var form = document.getElementById('crearEventoForm');
            if (form.checkValidity()) {
                var formData = new FormData(form);

                fetch('eventos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Evento creado con éxito');
                        location.reload(); // Recargar la página para mostrar el nuevo evento
                    } else {
                        alert('Error al crear el evento: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al crear el evento');
                });
            } else {
                alert('Por favor, complete todos los campos requeridos.');
            }
        });

        // Función para generar un código aleatorio
        function generarCodigoEvento() {
            return 'EV-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        }

        // Generar código automáticamente al abrir el modal
        document.getElementById('crearEventoModal').addEventListener('show.bs.modal', function (event) {
            document.getElementById('codigo').value = generarCodigoEvento();
        });

        
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/eventos.js"></script>
    <script>
        console.log('Página de eventos cargada');
    </script>
</body>
</html>