<?php
require_once("../database.php");
require_once("../models/eventosModel.php");
require_once("../controllers/consultasController.php");
require_once("procesarConsulta.php");
custom_session_start('admin_session');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si el usuario está logueado
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar el rol del usuario
$sql = "SELECT idRol FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($usuario['idRol'] == 2) { // Si es Game Master
    header("Location: eventos.php");
    exit();
}

// Manejar solicitudes POST primero
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean(); // Limpiar cualquier salida anterior
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'eliminar_usuario':
            $id = $_POST['id'];
            $sql = "DELETE FROM usuarios WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
            echo json_encode(['success' => $result]);
            exit;
            
        case 'crear_usuario':
            // Validaciones
            if (!preg_match('/^\d+$/', $_POST['ci'])) {
                echo json_encode(['success' => false, 'message' => 'CI debe contener solo números']);
                exit;
            }
            if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $_POST['nombres']) || 
                !preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $_POST['apellidos'])) {
                echo json_encode(['success' => false, 'message' => 'Nombres y apellidos deben contener solo letras']);
                exit;
            }
            
            $ci = $_POST['ci'];
            $nombres = $_POST['nombres'];
            $apellidos = $_POST['apellidos'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $idRol = $_POST['idRol'];
            
            $sql = "INSERT INTO usuarios (ci, nombres, apellidos, password, idRol) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('isssi', $ci, $nombres, $apellidos, $password, $idRol);
            $result = $stmt->execute();
            echo json_encode(['success' => $result]);
            exit;
    }
}

// Obtener eventos para el select
$sql = "SELECT id, nombre FROM eventos ORDER BY nombre ASC";
$result = $conexion->query($sql);
$eventos = $result->fetch_all(MYSQLI_ASSOC);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .table td {
            max-width: 300px;
            white-space: normal;
            word-wrap: break-word;
        }
        .table td:last-child {
            max-width: 500px; /* Más espacio para la columna de comentarios */
        }
    </style>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
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
                    <li class="nav-item">
                        <button class="btn btn-info nav-link custom-nav-link" onclick="mostrarUsuarios()">
                            <i class="fas fa-users"></i>
                        </button>
                    </li>
                </ul>
                <button class="btn btn-outline-light custom-logout-btn" onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card p-4 bg-dark text-light">
                    <div class="card-body">
                        <h2>Consultas del Sistema</h2>
                        <form id="consultaForm" method="POST" class="mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Consulta:</label>
                                    <select name="tipoConsulta" class="form-select">
                                        <option value="ranking_jugadores">Ranking de Jugadores por Puntaje</option>
                                        <option value="estadisticas_genero">Estadísticas por Género</option>
                                        <option value="juegos_populares">Estadísticas de Juegos</option>
                                        <option value="progreso_eventos">Estado de Eventos</option>
                                        <option value="analisis_edad">Análisis por Grupos de Edad</option>
                                        <option value="feedback_analisis">Análisis de Feedback</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Evento (opcional):</label>
                                    <select name="evento" class="form-select">
                                        <option value="">Todos los eventos</option>
                                        <?php foreach ($eventos as $evento): ?>
                                            <option value="<?php echo $evento['id']; ?>">
                                                <?php echo htmlspecialchars($evento['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Consultar</button>
                        </form>

                        <div id="resultadosConsulta">
                            <!-- Aquí se mostrarán los resultados -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Usuarios -->
<div class="modal fade" id="modalUsuarios" tabindex="-1" aria-labelledby="modalUsuariosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuariosLabel">Lista de Usuarios</h5>
                <button type="button" class="btn btn-primary me-2" onclick="mostrarCrearUsuario()">
                    <i class="fas fa-user-plus"></i> Crear Usuario
                </button>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT u.id, u.nombres, u.apellidos, r.rol 
                               FROM usuarios u 
                               LEFT JOIN rol r ON u.idRol = r.id 
                               ORDER BY u.nombres ASC";
                        $result = $conexion->query($sql);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['nombres']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['apellidos']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['rol']) . "</td>";
                            echo "<td>
                                    <button class='btn btn-danger btn-sm' onclick='eliminarUsuario(" . $row['id'] . ")'>
                                        <i class='fas fa-trash'></i>
                                    </button>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Crear Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearUsuario">
                    <div class="mb-3">
                        <input type="number" class="form-control" name="ci" placeholder="CI" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="nombres" placeholder="Nombres" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="apellidos" placeholder="Apellidos" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                    </div>
                    <div class="mb-3">
                        <select class="form-control" name="idRol" required>
                            <?php
                            $sqlRoles = "SELECT id, rol FROM rol";
                            $resultRoles = $conexion->query($sqlRoles);
                            while($rol = $resultRoles->fetch_assoc()) {
                                echo "<option value='" . $rol['id'] . "'>" . htmlspecialchars($rol['rol']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('consultaForm');
            const resultadosContainer = document.getElementById('resultadosConsulta');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('procesarConsulta.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        resultadosContainer.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultadosContainer.innerHTML = '<div class="alert alert-danger">Error al procesar la consulta</div>';
                    });
                });
            }

            // Cargar eventos al inicio
            fetch('../controllers/consultasController.php?action=getEventos')
            .then(response => response.json())
            .then(eventos => {
                const selectEvento = document.querySelector('select[name="evento"]');
                if (selectEvento) {
                    selectEvento.innerHTML = '<option value="">Todos los eventos</option>';
                    eventos.forEach(evento => {
                        selectEvento.innerHTML += `<option value="${evento.id}">${evento.nombre}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Mantener las funciones existentes para usuarios
        function mostrarUsuarios() {
            var modal = new bootstrap.Modal(document.getElementById('modalUsuarios'));
            modal.show();
        }

        function mostrarCrearUsuario() {
            var modalUsuarios = bootstrap.Modal.getInstance(document.getElementById('modalUsuarios'));
            modalUsuarios.hide();
            var modalCrear = new bootstrap.Modal(document.getElementById('modalCrearUsuario'));
            modalCrear.show();
        }

        function eliminarUsuario(id) {
            if (confirm('¿Estás seguro de eliminar este usuario?')) {
                const formData = new FormData();
                formData.append('action', 'eliminar_usuario');
                formData.append('id', id);

                fetch('consultas.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta del servidor');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al eliminar usuario');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            }
        }

        document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'crear_usuario');

            fetch('consultas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al crear usuario');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });

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