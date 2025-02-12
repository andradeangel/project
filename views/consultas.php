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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'cambiar_estado_usuario') {
        $response = ['success' => false, 'message' => ''];
        
        try {
            if (!isset($_POST['id']) || !isset($_POST['estado'])) {
                throw new Exception('Datos incompletos');
            }
            
            $id = (int)$_POST['id'];
            $estado = (int)$_POST['estado'];
            
            if ($estado !== 2 && $estado !== 3) {
                throw new Exception('Estado inválido');
            }
            
            $sql = "UPDATE usuarios SET idEstado = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ii", $estado, $id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Estado actualizado correctamente';
            } else {
                throw new Exception('Error al actualizar el estado');
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
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
            max-width: 500px;
        }
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
        .modal-title{
            margin-right: 10px;
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
                <a class="nav-link" href="#" onclick="showLogoutConfirm(event)">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 fijarHeader">
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
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT u.id, u.nombres, u.apellidos, r.rol, u.idEstado 
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
                                            <div class='form-check form-switch'>
                                                <input class='form-check-input' type='checkbox' 
                                                    id='switchUsuario_" . $row['id'] . "' 
                                                    onchange='cambiarEstadoUsuario(" . $row['id'] . ", this.checked)'
                                                    " . ($row['idEstado'] == 2 ? 'checked' : '') . ">
                                                <label class='form-check-label' for='switchUsuario_" . $row['id'] . "'>
                                                    " . ($row['idEstado'] == 2 ? 'Activo' : 'Inactivo') . "
                                                </label>
                                            </div>
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
    </div>

    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Usuario</h5> <br>
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

    <!-- Modal personalizado para mensajes -->
    <div id="customModal" class="custom-modal" style="display: none;">
        <h3 id="modalTitle"></h3>
        <p id="modalMessage"></p>
        <button onclick="closeCustomModal()">Aceptar</button>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div id="confirmModal" class="custom-modal" style="display: none;">
        <h3>Confirmar eliminación</h3>
        <p>¿Está seguro de eliminar este usuario?</p>
        <button onclick="confirmDeleteUser()">Eliminar</button>
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

            // Agregar el event listener para el botón de cerrar sesión
            const logoutButton = document.querySelector('a[href="#"][onclick="showLogoutConfirm(event)"]');
            if (logoutButton) {
                logoutButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    showLogoutConfirm(e);
                });
            }
        });

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

        function showCustomMessage(title, message, callback) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('customModal').style.display = 'block';
            if (callback) {
                window.modalCallback = callback;
            }
        }

        function closeCustomModal() {
            document.getElementById('customModal').style.display = 'none';
            if (window.modalCallback) {
                window.modalCallback();
                window.modalCallback = null;
            }
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            userToDelete = null;
        }

        let userToDelete = null;

        function eliminarUsuario(id) {
            userToDelete = id;
            document.getElementById('confirmModal').style.display = 'block';
        }

        function confirmDeleteUser() {
            if (userToDelete) {
                const formData = new FormData();
                formData.append('action', 'eliminar_usuario');
                formData.append('id', userToDelete);

                fetch('consultas.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta del servidor');
                    return response.json();
                })
                .then(data => {
                    document.getElementById('confirmModal').style.display = 'none';
                    if (data.success) {
                        showCustomMessage('Éxito', 'Usuario eliminado con éxito', () => {
                            location.reload();
                        });
                    } else {
                        showCustomMessage('Error', 'Error al eliminar usuario');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showCustomMessage('Error', 'Error al procesar la solicitud');
                });
            }
        }

        document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar nombres y apellidos
            const nombres = this.querySelector('[name="nombres"]').value;
            const apellidos = this.querySelector('[name="apellidos"]').value;
            
            if (!/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/.test(nombres) || !/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/.test(apellidos)) {
                showCustomMessage('Error', 'Nombres y apellidos deben contener solo letras');
                return;
            }

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
                    showCustomMessage('Éxito', 'Usuario creado con éxito', () => {
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', data.message || 'Error al crear usuario');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomMessage('Error', 'Error al procesar la solicitud');
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

        function cambiarEstadoUsuario(id, estado) {
            const nuevoEstado = estado ? 2 : 3; // 2 para activo, 3 para inactivo
            const formData = new FormData();
            formData.append('action', 'cambiar_estado_usuario');
            formData.append('id', id);
            formData.append('estado', nuevoEstado);

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
                    const label = document.querySelector(`label[for="switchUsuario_${id}"]`);
                    if (label) {
                        label.textContent = estado ? 'Activo' : 'Inactivo';
                    }
                    showCustomMessage('Éxito', 'Estado del usuario actualizado correctamente');
                } else {
                    showCustomMessage('Error', data.message || 'Error al actualizar estado');
                    // Revertir el switch si hubo error
                    const switch_element = document.getElementById(`switchUsuario_${id}`);
                    if (switch_element) {
                        switch_element.checked = !estado;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomMessage('Error', 'Error al procesar la solicitud');
                // Revertir el switch si hubo error
                const switch_element = document.getElementById(`switchUsuario_${id}`);
                if (switch_element) {
                    switch_element.checked = !estado;
                }
            });
        }
    </script>
</body>
</html>