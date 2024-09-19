<?php
session_start();
if(isset($_POST["btnAccess"])) {
    if(empty($_POST["codigoAccess"])) {
        echo "<div class='alert alert-danger'>El campo está vacío</div>";
    } else {
        $codigo = $_POST["codigoAccess"];
        $sql = $conexion->prepare("SELECT * FROM eventos WHERE codigo = ?");
        $sql->bind_param("s", $codigo);
        $sql->execute();
        $resultado = $sql->get_result();
        if($dato = $resultado->fetch_object()) {
            if($dato->idEstado == 2) { // Verificar si el evento está activo
                $_SESSION['evento_id'] = $dato->id;  // Guardamos el ID del evento en la sesión
                $_SESSION['evento_nombre'] = $dato->nombre;  // Guardamos el nombre del evento en la sesión
                header("Location: views/evento.php");
                exit();
            } else {
                switch($dato->idEstado) {
                    case 1:
                        echo "<div class='alert alert-danger'>El evento aun no ha empezado</div>";
                        break;
                    case 3:
                        echo "<div class='alert alert-danger'>El evento ha finalizado</div>";
                        break;
                    default:
                        echo "<div class='alert alert-danger'>El evento no está activo</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger'>El evento no existe</div>";
        }
    }
}
?>