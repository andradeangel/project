<?php
    require_once("../database.php");
    require_once("../models/eventosModel.php");
    require_once("../controllers/actualizarEstadosEventos.php");

    $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'e.fechaInicio';
    $orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'DESC';

    $eventos = obtenerEventos($conexion, $orderBy, $orderDir);
    $sprints = obtenerSprints($conexion);

    function formatDate($date) {
        return date('d/m/y - H:i', strtotime($date));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        $resultado = ['success' => false, 'message' => 'Acción no válida'];

        switch ($accion) {
            case 'crear':
                $resultado = crearEvento($conexion, $_POST['nombre'], $_POST['fechaInicio'], $_POST['fechaFin'], $_POST['sprint'], $_POST['descripcion']);
                break;
            case 'obtener':
                $evento = obtenerEvento($conexion, $_POST['id']);
                $resultado = $evento ? ['success' => true, 'data' => $evento] : ['success' => false, 'message' => 'Evento no encontrado'];
                break;
            case 'editar':
                $resultado = editarEvento($conexion, $_POST['id'], $_POST['nombre'], $_POST['fechaInicio'], $_POST['fechaFin'], $_POST['sprint'], $_POST['descripcion']);
                break;
            case 'eliminar':
                $resultado = eliminarEvento($conexion, $_POST['id']);
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
?>