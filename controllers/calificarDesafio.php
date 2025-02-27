<?php
require_once('../database.php');
require_once('../controllers/monitoreoController.php');
custom_session_start('admin_session');

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'nuevoPuntaje' => 0];

try {
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('No hay sesión de administrador activa');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['challengeId']) || !isset($data['status'])) {
        throw new Exception('Datos incompletos');
    }

    $controller = new MonitoreoController($conexion);
    
    // Obtener el jugador_id primero
    $sql = "SELECT jugador_id FROM desafios WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $data['challengeId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $desafio = $result->fetch_assoc();
    
    if (!$desafio) {
        throw new Exception('No se encontró el desafío');
    }

    if ($data['status'] === 'aprobado') {
        if ($controller->aprobarDesafio($data['challengeId'], $_SESSION['admin_id'])) {
            $nuevoPuntaje = $controller->getJugadorPuntaje($desafio['jugador_id']);
            $response = [
                'success' => true,
                'message' => 'Desafío aprobado y puntaje actualizado',
                'nuevoPuntaje' => $nuevoPuntaje
            ];
        } else {
            throw new Exception('Error al aprobar el desafío');
        }
    } else {
        // Actualizar estado del desafío
        $sql = "UPDATE desafios SET estado = 'reprobado', calificado = TRUE, calificador_id = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("is", $_SESSION['admin_id'], $data['challengeId']);
        
        if ($stmt->execute()) {
            // Actualizar juego_actual del jugador
            $sql = "UPDATE jugadores SET juego_actual = juego_actual + 10 WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $desafio['jugador_id']);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Desafío reprobado y juego actualizado';
            } else {
                throw new Exception('Error al actualizar el juego actual');
            }
        } else {
            throw new Exception('Error al actualizar el estado del desafío');
        }
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
exit;
