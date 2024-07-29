<?php
    if(!empty($_POST["btnAccess"])){
        if(empty($_POST["codigoAccess"])){
            echo "El campo esta vacio";
        }
    } else{
        $codigo=$_POST["codigoAccess"];
        $sql=$conexion->query("SELECT * FROM eventos WHERE codigo='$codigo'");
        if($dato=sql->fetch_object()){
            header(location: evento.php);
        } else{
            echo"<div>Acceso denegado</div>";
        }
    }
?>