<?php
require_once('../database.php');
custom_session_start('player_session');

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['puntos']) && isset($_SESSION['jugador_actual']['id'])) {
        $jugadorId = $_SESSION['jugador_actual']['id'];
        
        // Obtener puntaje actual
        $sql = "SELECT puntaje FROM jugadores WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $jugadorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $puntajeActual = $row['puntaje'];
        
        // Actualizar puntaje
        $nuevoPuntaje = $puntajeActual + $data['puntos'];
        $sql = "UPDATE jugadores SET puntaje = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $nuevoPuntaje, $jugadorId);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Puntaje actualizado correctamente';
            $response['nuevoPuntaje'] = $nuevoPuntaje;
        } else {
            $response['message'] = 'Error al actualizar el puntaje';
        }
    } else {
        $response['message'] = 'Datos incompletos';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response); 