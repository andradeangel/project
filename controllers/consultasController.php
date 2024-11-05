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
?>
