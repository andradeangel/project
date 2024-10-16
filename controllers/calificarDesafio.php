<?php
session_start();
$response = ['success' => false];

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['challengeId']) && isset($data['status'])) {
    $challengeId = $data['challengeId'];
    $status = $data['status'];

    if (isset($_SESSION['pending_challenges'][$challengeId])) {
        $_SESSION['pending_challenges'][$challengeId]['estado'] = $status;
        $_SESSION['pending_challenges'][$challengeId]['calificado'] = true;
        $response['success'] = true;

        // No eliminamos el desafío de la sesión aquí para que cholitas.php pueda verificarlo
    }
}

echo json_encode($response);
