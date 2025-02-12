<?php
require_once("../database.php");
custom_session_start('player_session');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idJugador = $_POST['idJugador'] ?? null;
    $idEvento = $_POST['idEvento'] ?? null;
    $comentarios = $_POST['comentarios'] ?? null;
    
    if ($idJugador && $idEvento && $comentarios) {
        date_default_timezone_set('America/La_Paz');
        $fecha = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO feedback (idJugador, idEvento, comentarios, fecha) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiss", $idJugador, $idEvento, $comentarios, $fecha);
        
        if ($stmt->execute()) {
            header("Location: https://lapuerta.net/");
            exit();
        }
    }
    
    // En caso de error, también redirigir a la página
    header("Location: https://lapuerta.net/");
    exit();
}
?> 