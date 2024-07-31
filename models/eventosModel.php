<?php
$user_id = $_SESSION['user_id'];
$sql = "SELECT usuarios.nombres, usuarios.rol, rol.rol AS nombre_rol 
        FROM usuarios 
        JOIN rol ON usuarios.rol = rol.id 
        WHERE usuarios.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$nombre_usuario = $user['nombres'];
$rol_usuario = $user['nombre_rol'];
?>
