<?php
require_once("../models/consultasModel.php");

$consultasModel = new ConsultasModel();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'getEventos') {
        header('Content-Type: application/json');
        $sql = "SELECT id, nombre FROM eventos ORDER BY nombre ASC";
        try {
            $eventos = $consultasModel->ejecutarConsulta($sql);
            echo json_encode($eventos);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipoConsulta'])) {
    try {
        $tipoConsulta = $_POST['tipoConsulta'];
        $eventoId = !empty($_POST['evento']) ? $_POST['evento'] : null;
        
        $consultasModel = new ConsultasModel();
        $resultado = $consultasModel->ejecutarConsultaEspecifica($tipoConsulta, $eventoId);
        
        if (!empty($resultado)) {
            $_SESSION['resultado_consulta'] = $resultado;
        } else {
            $_SESSION['mensaje'] = "No se encontraron resultados para la consulta.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear_usuario') {
    $response = ['success' => false, 'message' => ''];

    try {
        // Validar y obtener datos del formulario
        $ci = $_POST['ci'];
        $nombres = $_POST['nombres'];
        $apellidos = $_POST['apellidos'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Asegúrate de usar hash para la contraseña
        $idRol = $_POST['idRol'];

        // Insertar el nuevo usuario en la base de datos
        $sql = "INSERT INTO usuarios (ci, nombres, apellidos, password, idRol, idEstado) VALUES (?, ?, ?, ?, ?, 2)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isssi", $ci, $nombres, $apellidos, $password, $idRol);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Usuario creado con éxito';
        } else {
            throw new Exception('Error al crear el usuario: ' . $stmt->error);
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
