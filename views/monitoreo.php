<?php
    session_start();
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    require_once("../database.php");    
    require_once("../controllers/monitoreoController.php");

    $controller = new MonitoreoController($conexion);
    $eventosEnProceso = $controller->getEventosEnProceso();

    function formatearFecha($fecha) {
        $datetime = new DateTime($fecha);
        return $datetime->format('d/m/y H:i');
    }

    // Asumiendo que tienes estas variables definidas en algún lugar
    $nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
    $rol_usuario = $_SESSION['rol_usuario'] ?? 'Rol no definido';
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
                <button class="btn btn-outline-light custom-logout-btn" onclick="confirmarCerrarSesion()">Cerrar Sesión</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active fijarHeader" id="monitorear" role="tabpanel" aria-labelledby="nav-monitorear-tab">
                        <h2>Monitoreo</h2>
                        
                        <!-- Sección de Solicitudes -->
                        <div class="mt-4">
                            <p>Aún no hay solicitudes.</p>
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
    function confirmarCerrarSesion() {
        if (confirm("¿Está seguro de que desea cerrar sesión?")) {
            window.location.href = "/";
        }
    }
    </script>
</body>
</html>