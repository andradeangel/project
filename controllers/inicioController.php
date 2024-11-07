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
        $sql = $conexion->prepare("SELECT * FROM eventos WHERE codigo = ?");
        $sql->bind_param("s", $codigo);
        $sql->execute();
        $resultado = $sql->get_result();
        if($dato = $resultado->fetch_object()) {
            if($dato->idEstado == 2) { // Verificar si el evento está activo
                $_SESSION['player_evento_id'] = $dato->id;  // Guardamos el ID del evento en la sesión
                $_SESSION['player_evento_nombre'] = $dato->nombre;  // Guardamos el nombre del evento en la sesión
                header("Location: views/datosJugador.php");
                exit();
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
