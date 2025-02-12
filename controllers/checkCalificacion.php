<?php
require_once('../database.php');
custom_session_start('player_session');

header('Content-Type: application/json');
$response = ['calificado' => false, 'status' => null, 'nuevoPuntaje' => null];

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['challengeId'])) {
        throw new Exception('ID del desafío no proporcionado');
    }

    $sql = "SELECT d.estado, d.calificado, d.jugador_id 
            FROM desafios d 
            WHERE d.id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $data['challengeId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $desafio = $result->fetch_assoc();
    
    if ($desafio && $desafio['calificado']) {
        $response['calificado'] = true;
        $response['status'] = $desafio['estado'];
        
        // Actualizar tiempo_fin cuando el desafío es calificado
        if ($desafio['estado'] === 'aprobado') {
            $sqlUpdateTiempo = "UPDATE jugadores 
                               SET tiempo_fin = CURRENT_TIMESTAMP 
                               WHERE id = ?";
            $stmtTiempo = $conexion->prepare($sqlUpdateTiempo);
            $stmtTiempo->bind_param("i", $desafio['jugador_id']);
            $stmtTiempo->execute();
        }
        
        // Obtener nuevo puntaje
        $sql = "SELECT puntaje FROM jugadores WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $desafio['jugador_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $jugador = $result->fetch_assoc();
        $response['nuevoPuntaje'] = $jugador['puntaje'];
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
exit;
