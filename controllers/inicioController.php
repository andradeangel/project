<?php
session_start();
if(isset($_POST["btnAccess"])){
    if(empty($_POST["codigoAccess"])){
        echo "<div class='alert alert-danger'>El campo está vacío</div>";
    } else {
        $codigo = $_POST["codigoAccess"];
        $sql = $conexion->prepare("SELECT * FROM eventos WHERE codigo = ?");
        $sql->bind_param("s", $codigo);
        $sql->execute();
        $resultado = $sql->get_result();
        if($dato = $resultado->fetch_object()){
            $_SESSION['evento_id'] = $dato->id;  // Guardamos el ID del evento en la sesión
            header("Location: views/datosJugador.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Evento no existente</div>";
        }
    }
}
?>