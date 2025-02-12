<?php
require_once('../database.php');
custom_session_start('player_session');

header('Content-Type: application/json');
$response = ['success' => false, 'challengeId' => null, 'message' => ''];

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (isset($data['challenge']) && isset($data['gameType'])) {
        $challengeId = uniqid();
        
        // Crear directorio si no existe
        $uploadDir = "../uploads/challenges/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($data['gameType'] === 'video') {
            // Procesar video
            $base64Data = preg_replace('#^data:video/\w+;base64,#i', '', $data['challenge']);
            $fileData = base64_decode($base64Data);
            $fileName = $challengeId . '.mp4';
        } else {
            // Procesar imagen
            $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $data['challenge']);
            $fileData = base64_decode($base64Data);
            $fileName = $challengeId . '.jpg';
        }
        
        $filePath = $uploadDir . $fileName;
        
        // Guardar archivo
        if (file_put_contents($filePath, $fileData)) {
            // Guardar en la base de datos
            $sql = "INSERT INTO desafios (
                id, jugador_id, evento_id, juego_id, 
                tipo, archivo_ruta, estado, calificador_id
            ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', NULL)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("siisss",
                $challengeId,
                $_SESSION['jugador_actual']['id'],
                $_SESSION['evento_actual']['id'],
                $data['juego_id'],
                $data['gameType'],
                $fileName
            );
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['challengeId'] = $challengeId;
                $response['message'] = 'Desafío guardado correctamente';
            }
        } else {
            throw new Exception('Error al guardar el archivo');
        }
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Error en uploadChallenge.php: " . $e->getMessage());
}

echo json_encode($response);
exit;

