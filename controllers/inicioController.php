<?php
custom_session_start('player_session');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isset($_POST["btnAccess"])) {
    if(empty($_POST["codigoAccess"])) {
        $_SESSION['error_message'] = "El campo está vacío";
    } else {
        $codigo = $_POST["codigoAccess"];
        
        // Consulta para verificar el evento y su capacidad
        $sql = $conexion->prepare("
            SELECT e.id, e.nombre, e.codigo, e.idEstado, e.personas, 
                   COUNT(j.id) as jugadores_inscritos
            FROM eventos e
            LEFT JOIN jugadores j ON e.id = j.idEvento
            WHERE e.codigo = ?
            GROUP BY e.id, e.nombre, e.codigo, e.idEstado, e.personas
        ");
        $sql->bind_param("s", $codigo);
        $sql->execute();
        $resultado = $sql->get_result();
        
        if($dato = $resultado->fetch_object()) {
            // Verificar estado del evento
            if($dato->idEstado == 2) { 
                // Verificar capacidad del evento
                if($dato->jugadores_inscritos >= $dato->personas) {
                    $_SESSION['error_message'] = "El evento ha alcanzado su capacidad máxima";
                } else {
                    // Evento activo y con capacidad disponible
                    $_SESSION['player_evento_id'] = $dato->id;  
                    $_SESSION['player_evento_nombre'] = $dato->nombre;  
                    header("Location: views/datosJugador.php");
                    exit();
                }
            } else {
                switch($dato->idEstado) {
                    case 1:
                        $_SESSION['error_message'] = "El evento aun no ha empezado";
                        break;
                    case 3:
                        $_SESSION['error_message'] = "El evento ha finalizado";
                        break;
                    default:
                        $_SESSION['error_message'] = "El evento no está activo";
                }
            }
        } else {
            $_SESSION['error_message'] = "El evento no existe";
        }
    }
}
?>