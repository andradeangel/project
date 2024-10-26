<?php
    include_once("../database.php");

    session_start();

    if(isset($_POST["btnDatosJugador"])) {
        if(empty($_POST["nombre"]) || empty($_POST["edad"]) || empty($_POST["genero"])) {
            echo "<div class='alert alert-danger'>Todos los campos son obligatorios</div>";
        } else {
            $nombre = $_POST["nombre"];
            $edad = $_POST["edad"];
            $genero = $_POST["genero"];
            
            // Convertir el género a su valor numérico correspondiente
            switch($genero) {
                case 'Masculino':
                    $generoValor = 1;
                    break;
                case 'Femenino':
                    $generoValor = 2;
                    break;
                case 'Otro':
                    $generoValor = 3;
                    break;
                default:
                    $generoValor = 3; // Por defecto, si hay algún error
            }
            
            $eventoId = $_SESSION['evento_id'] ?? null;
            
            if($eventoId === null) {
                echo "<div class='alert alert-danger'>Error: No se pudo obtener el ID del evento</div>";
            } else {
                // Preparar la consulta SQL
                $sql = $conexion->prepare("INSERT INTO jugadores (nombres, edad, idGenero, idEvento, puntaje, idEstado) VALUES (?, ?, ?, ?, 0, 1)");
                $sql->bind_param("siii", $nombre, $edad, $generoValor, $eventoId);
                
                // Ejecutar la consulta
                if($sql->execute()) {
                    $jugadorId = $sql->insert_id;
                    $_SESSION['user_id'] = $jugadorId;
                    $_SESSION['user_name'] = $nombre;
                    header("Location: ../views/evento.php");
                    exit();
                } else {
                    echo "<div class='alert alert-danger'>Error al registrar al jugador: " . $sql->error . "</div>";
                }
            }
        }
    }
?>