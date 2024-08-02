<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once("../database.php");
require_once("../controllers/consultasController.php");

// Obtener datos del usuario desde la sesión
$nombre_usuario = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Usuario';
$rol_usuario = isset($_SESSION['rol_usuario']) ? $_SESSION['rol_usuario'] : 'Rol';

$consultasController = new ConsultasController();
$resultado = [];
$error = "";
$generos = $consultasController->obtenerGeneros();
$eventos = $consultasController->obtenerEventos();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $consulta = $_POST["consulta"];
    $genero = $_POST["genero"];
    $evento = $_POST["evento"];

    switch ($consulta) {
        case 'genero_ultimo_mes':
            $sql = "SELECT genero.genero, COUNT(jugadores.id) AS cantidad
                    FROM jugadores
                    JOIN genero ON jugadores.genero = genero.id
                    WHERE jugadores.evento = $evento AND MONTH(CURDATE()) = MONTH(jugadores.fecha)
                    GROUP BY genero.genero";
            break;
        case 'evento_especifico':
            $sql = "SELECT eventos.nombre, COUNT(jugadores.id) AS cantidad
                    FROM jugadores
                    JOIN eventos ON jugadores.evento = eventos.id
                    WHERE eventos.id = $evento
                    GROUP BY eventos.nombre";
            break;
        default:
            $sql = "";
            break;
    }

    if (!empty($sql)) {
        $resultado = $consultasController->ejecutarConsulta($sql);
        if (isset($resultado["error"])) {
            $error = $resultado["error"];
            $resultado = [];
        }
    }
}
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
                        <a class="nav-link custom-nav-link" href="sprints.php">Sprints</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-nav-link active" href="">Consultas</a>
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
                    <div class="tab-pane fade show active" id="consultas" role="tabpanel" aria-labelledby="nav-consultas-tab">
                        <h2>Consultas</h2>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="consulta" class="form-label">Tipo de Consulta</label>
                                <select class="form-select" id="consulta" name="consulta">
                                    <option value="genero_ultimo_mes">Cantidad de hombres y mujeres que jugaron el último mes</option>
                                    <option value="evento_especifico">Cantidad de jugadores en un evento específico</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="genero" class="form-label">Género</label>
                                <select class="form-select" id="genero" name="genero">
                                    <?php foreach ($generos as $genero): ?>
                                        <option value="<?php echo htmlspecialchars($genero['id']); ?>"><?php echo htmlspecialchars($genero['genero']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="evento" class="form-label">Evento</label>
                                <select class="form-select" id="evento" name="evento">
                                    <?php foreach ($eventos as $evento): ?>
                                        <option value="<?php echo htmlspecialchars($evento['id']); ?>"><?php echo htmlspecialchars($evento['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Ejecutar Consulta</button>
                        </form>
                        <?php if ($error): ?>
                            <div class="alert alert-danger mt-3" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($resultado)): ?>
                            <div class="mt-3">
                                <h3>Resultados</h3>
                                <table class="table table-bordered table-dark">
                                    <thead>
                                        <tr>
                                            <?php foreach ($resultado[0] as $key => $value): ?>
                                                <th><?php echo htmlspecialchars($key); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultado as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $value): ?>
                                                    <td><?php echo htmlspecialchars($value); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
