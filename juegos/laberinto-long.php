<?php
require_once('../database.php');
custom_session_start('player_session');

if (!isset($_SESSION['jugador_actual']) || !isset($_SESSION['evento_actual'])) {
    error_log("Redirección a evento.php por falta de datos de sesión");
    header('Location: ../views/evento.php');
    exit;
}

$juego_id = $_GET['juego_id'] ?? null;
$descripcion = $_GET['descripcion'] ?? 'Descripción no disponible';
$_SESSION['current_game_id'] = $juego_id;
$_SESSION['current_game_description'] = $descripcion;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'actualizarPuntaje' && isset($data['puntos'])) {
        $resultado = actualizarPuntaje($data['puntos']);
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
}

// Función para actualizar el puntaje
function actualizarPuntaje($puntos) {
    global $conexion;
    $jugadorId = $_SESSION['jugador_actual']['id'];
    
    // Obtener puntaje actual y juego actual
    $sql = "SELECT puntaje, juego_actual FROM jugadores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $jugadorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $puntajeActual = $row['puntaje'];
    $juegoActual = $row['juego_actual'];
    
    // Actualizar puntaje, juego actual y tiempo_fin
    $nuevoPuntaje = $puntajeActual + $puntos;
    $nuevoJuegoActual = $juegoActual + 1;
    $sql = "UPDATE jugadores 
            SET puntaje = ?, 
                juego_actual = ?, 
                tiempo_fin = CURRENT_TIMESTAMP 
            WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iii", $nuevoPuntaje, $nuevoJuegoActual, $jugadorId);
    
    if ($stmt->execute()) {
        return ['success' => true, 'nuevoPuntaje' => $nuevoPuntaje];
    }
    return ['success' => false];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Long Laberinto</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Press Start 2P', cursive;
            color: white;
            background: url('https://img.freepik.com/fotos-premium/laberinto-oscuro-luz-brillante-al-final-camino-que-representa-solucion-o-escape_362549-2322.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding: 20px;
        }
        #game-container {
            width: 95vmin;
            height: 95vmin;
            max-width: 800px;
            max-height: 800px;
            position: relative;
            background-color: rgba(17, 17, 17, 0.8);
            border: 3px solid #0f0;
            box-shadow: 0 0 10px #0f0;
        }
        #player {
            width: 1.875%;
            height: 1.875%;
            background-color: #0f0;
            position: absolute;
            border-radius: 50%;
            transition: all 0.1s;
            z-index: 10;
        }
        .wall {
            position: absolute;
            background-color: #f00;
            width: 2.5%;
            height: 2.5%;
        }
        #timer {
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }
        #message {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
            color: #0f0;
        }
        #controls {
            display: flex;
            justify-content: center;
        }
        .control-btn {
            width: 40px;
            height: 40px;
            margin: 0 5px;
            font-size: 16px;
        }
        #message-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        #overlay-message {
            text-align: center;
            line-height: 1.5;
        }
        #overlay-message button {
            margin-top: 15px;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.8rem;
            padding: 10px 20px;
        }
        h1{
            font-size: 25px;
            margin-bottom: 10px;
            text-shadow: 0 0 10px #0f0; 
        }
        .timer{
            text-shadow: 0 0 10px #0f0; 
        }
        #key {
            position: absolute;
            width: 1.875%;
            right: 0;
            bottom: 2.5%;
            z-index: 5;
        }
        @media (max-width: 576px) {
            h1 {
                font-size: 20px;
            }
            .control-btn {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }
            #timer, #message {
                font-size: 10px;
            }
        }
        .back-btn {
            background-color: rgba(50, 50, 50, 0.9);
            border: none;
            color: #ddd;
            padding: 12px 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 10px;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .back-btn:hover {
            background-color: rgba(80, 80, 80, 0.9);
        }
        .card-text {
            text-align: center;
        }
        .reset-boton, .aceptar-btn{
            font-size: 10px;
            border: none;
            padding: 12px 5px;
            color: black;
            background-color:#0b0;
        }
        .reset-boton:hover, .aceptar-btn:hover{
            color: black;
            background-color: #0f0;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <h1>Laberinto</h1>
        <p class="card-text"><?php echo htmlspecialchars($descripcion); ?></p>
        <div id="game-container">
            <div id="player"></div>
            <img id="key" src="../images/key.png" alt="Llave">
        </div>
        <div id="timer" class="timer">Tiempo: 0s</div>
        <div id="message"></div>
        <div id="controls">
            <button class="btn btn-primary control-btn" onclick="movePlayer(0, -2.5)">↑</button>
            <button class="btn btn-primary control-btn" style="transform: scaleX(-1)" onclick="movePlayer(-2.5, 0)">➔</button>
            <button class="btn btn-primary control-btn" onclick="movePlayer(2.5, 0)">➔</button>
            <button class="btn btn-primary control-btn" onclick="movePlayer(0, 2.5)">↓</button>
        </div>
        <div class="mt-2">
            <button id="restartBtn" class="btn btn-success reset-boton">Reiniciar</button>
            <button onclick="window.location.href='../views/evento.php'" class="back-btn">Volver</button>
        </div>
        <div id="message-overlay">
            <div id="overlay-message"></div>
        </div>
    </div>

    <script>
        const player = document.getElementById('player');
        const gameContainer = document.getElementById('game-container');
        const timerDisplay = document.getElementById('timer');
        const messageDisplay = document.getElementById('message');
        const overlayMessageDisplay = document.getElementById('overlay-message');
        const messageOverlay = document.getElementById('message-overlay');
        const restartBtn = document.getElementById('restartBtn');

        let playerX = 0;
        let playerY = 2.5;
        let isGameOver = false;
        let startTime;
        let timerInterval;

        const laberinto = [
            [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            [0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,1,0,0,0,1],
            [1,0,1,1,1,1,1,0,1,1,1,0,1,1,1,1,1,0,1,1,1,1,1,0,1,1,1,1,0,1,0,1,0,1,0,1,0,1,0,1],
            [1,0,1,0,0,0,1,0,0,0,0,0,1,0,0,0,1,0,0,0,1,0,0,0,1,0,0,0,0,1,0,0,0,1,0,0,0,1,0,1],
            [1,0,1,0,1,0,1,1,1,1,1,0,1,1,1,0,1,1,1,0,1,0,1,1,1,0,1,0,1,1,1,1,1,1,1,1,1,1,0,1],
            [1,0,0,0,1,0,1,0,0,0,1,0,0,0,0,0,1,0,0,0,1,0,0,0,0,0,1,0,1,0,1,0,0,0,0,0,0,0,0,1],
            [1,0,1,1,1,1,1,0,1,0,1,1,1,0,1,1,1,0,1,1,1,1,1,1,1,1,1,0,1,0,1,0,1,1,1,1,1,1,0,1],
            [1,0,1,0,0,0,1,0,1,0,0,0,1,0,1,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,1,0,0,1,0,0,0,1,0,1],
            [1,1,1,0,1,0,1,0,1,1,1,0,1,0,1,0,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,0,1,0,1],
            [1,0,0,0,1,0,1,0,1,0,0,0,1,0,1,0,0,0,0,0,1,0,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,1,0,1],
            [1,0,1,1,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,0,1,0,1,1,0,1,0,1,1,1,1,1,1,1,0,1,1,1,1,1],
            [1,0,0,0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,1,0,0,1,0,1,0,1,0,0,0,1,0,1,0,0,0,1,0,1],
            [1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,0,1,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1],
            [1,0,1,0,1,0,0,0,1,0,0,0,0,0,1,0,1,0,1,0,1,0,0,1,0,1,0,0,0,1,0,0,0,1,0,1,0,0,0,1],
            [1,1,1,0,1,0,1,0,1,0,1,0,1,1,1,0,1,0,1,0,1,0,1,1,0,1,1,1,1,1,1,1,0,1,0,1,1,1,1,1],
            [1,0,0,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,1,0,1,0,0,1,0,0,0,0,0,1,0,0,0,1,0,0,0,1,0,1],
            [1,0,1,1,1,0,1,0,1,1,1,0,1,1,1,1,1,0,1,1,1,1,0,1,0,1,1,1,0,1,0,1,1,1,1,1,0,1,0,1],
            [1,0,0,0,0,0,1,0,0,0,1,0,1,0,0,0,1,0,1,0,1,0,0,1,0,1,0,0,0,1,0,1,0,0,0,1,0,1,0,1],
            [1,0,1,1,1,1,1,1,1,0,1,1,1,0,1,0,1,1,1,0,1,1,1,1,0,1,1,1,1,1,0,1,0,1,1,1,0,1,0,1],
            [1,0,1,0,0,0,0,0,1,0,1,0,0,0,1,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,1,0,1,0,0,0,1,0,1],
            [1,0,1,1,1,0,1,1,1,0,1,0,1,1,1,1,1,1,1,1,1,1,0,1,1,1,0,1,0,1,1,1,0,1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1,0,0,0,1,0,1,0,0,0,1,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,1,0,1,0,0,0,1],
            [1,1,1,1,1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,0,1,1,1,1,0,1,1,1,0,1,0,1,0,1,0,1,0,1,0,1],
            [1,0,1,1,1,0,1,0,1,1,1,1,1,0,1,1,1,0,1,0,0,1,0,1,0,1,0,1,0,1,1,1,0,1,0,1,1,1,0,1],
            [1,0,1,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,1,0,0,0,0,0,1,0,0,0,0,0,0,0,1],
            [1,0,1,0,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,0,1,1,1,1,0,1,0,1,1,1,1,1,1,1,1,1,0,1,0,1],
            [1,0,0,0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,0,1,0,1,0,0,0,0,0,1,0,0,0,1,0,1],
            [1,0,1,1,1,0,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,0,1,1,1,1,1,1,0,1,1,1,0,1,0,1,1,1,0,1],
            [1,0,1,0,1,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,1,0,1,0,0,0,0,0,0,0,0,1,0,1,0,1,0,0,0,1],
            [1,0,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,0,1,0,1,0,1,1,1,1,1],
            [1,0,1,0,0,0,1,0,0,0,0,0,1,0,0,0,0,0,0,0,1,0,1,0,1,0,1,0,1,0,0,1,0,1,0,1,0,0,0,1],
            [1,0,1,1,1,1,1,1,1,1,1,0,1,0,1,1,1,1,1,1,1,0,1,0,1,0,1,0,1,0,1,1,0,1,0,1,0,1,0,1],
            [1,0,1,0,0,0,0,0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,1,0,1,0,1,0,0,0,1,0,0,1,0,0,0,1,0,1],
            [1,0,1,0,1,1,1,0,1,0,1,1,1,1,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1,0,1,1,1,1,0,1,0,1],
            [1,0,0,0,1,0,0,0,1,0,0,0,0,0,1,0,0,0,0,0,1,0,1,0,1,0,1,0,0,0,1,0,0,0,1,0,0,1,0,1],
            [1,1,1,1,1,0,1,1,1,0,1,1,1,0,1,0,1,1,1,1,1,0,1,1,1,0,1,0,1,0,1,1,1,0,1,1,1,1,0,1],
            [1,0,0,0,0,0,1,0,0,0,1,0,0,0,1,0,1,0,0,0,0,0,1,0,0,0,1,0,1,0,1,0,1,0,1,0,0,0,0,1],
            [1,0,1,1,1,1,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1,0,1,1,1,0,1,0,1,0,1,0,1,0,1,1,0,1],
            [1,0,0,0,0,0,0,0,0,0,0,0,1,0,1,0,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,1,0,0,0,0,1,0,0],
            [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
        ];


        function createWalls() {
            for (let y = 0; y < laberinto.length; y++) {
                for (let x = 0; x < laberinto[y].length; x++) {
                    if (laberinto[y][x] === 1) {
                        const wallElement = document.createElement('div');
                        wallElement.className = 'wall';
                        wallElement.style.left = (x * 2.5) + '%';
                        wallElement.style.top = (y * 2.5) + '%';
                        gameContainer.appendChild(wallElement);
                    }
                }
            }
        }

        function movePlayer(dx, dy) {
            if (isGameOver) return;

            const newX = playerX + dx;
            const newY = playerY + dy;
            
            // Convertir las coordenadas de porcentaje a índices de la matriz
            const gridX = Math.round(newX / 2.5);
            const gridY = Math.round(newY / 2.5);
            
            // Verificar que estamos dentro de los límites del laberinto
            if (gridX >= 0 && gridX < 40 && gridY >= 0 && gridY < 40) {
                // Verificar si la nueva posición es un espacio válido (0)
                if (laberinto[gridY][gridX] === 0) {
                    playerX = newX;
                    playerY = newY;
                    player.style.left = playerX + '%';
                    player.style.top = playerY + '%';

                    // Verificar si llegó a la meta (donde está la llave)
                    if (gridX === 39 && gridY === 38) {
                        gameOver(true);
                    }
                }
            }
        }

        function gameOver(win) {
            isGameOver = true;
            clearInterval(timerInterval);
            const endTime = (Date.now() - startTime) / 1000;
            
            if (win) {
                let puntos = 1;
                if (endTime <= 45) {
                    puntos = 3;
                } else if (endTime <= 60) {
                    puntos = 2;
                }

                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'actualizarPuntaje',
                        puntos: puntos
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        overlayMessageDisplay.innerHTML = `
                            ¡Llave conseguida!<br>
                            Tiempo: ${endTime.toFixed(2)}s<br>
                            Puntos ganados: +${puntos}<br>
                            Nuevo puntaje total: ${data.nuevoPuntaje}<br>
                            <button onclick="redirigirEvento()" class="btn btn-success mt-3 aceptar-btn">Aceptar</button>
                        `;
                    } else {
                        overlayMessageDisplay.innerHTML = `
                            Error al actualizar puntaje.<br>
                            Tiempo: ${endTime.toFixed(2)}s<br>
                            <button onclick="redirigirEvento()" class="btn btn-success mt-3 aceptar-btn">Aceptar</button>
                        `;
                    }
                    overlayMessageDisplay.style.color = '#0f0';
                    messageOverlay.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    overlayMessageDisplay.innerHTML = `
                        Error al procesar el resultado.<br>
                        Tiempo: ${endTime.toFixed(2)}s<br>
                        <button onclick="redirigirEvento()" class="btn btn-success mt-3">Aceptar</button>
                    `;
                    overlayMessageDisplay.style.color = '#f00';
                    messageOverlay.style.display = 'flex';
                });
            } else {
                overlayMessageDisplay.textContent = '¡Perdiste! Inténtalo de nuevo.';
                overlayMessageDisplay.style.color = '#f00';
                messageOverlay.style.display = 'flex';
            }
        }   

        function redirigirEvento() {
            window.location.href = '../views/evento.php';
        }

        function startGame() {
            playerX = 0;
            playerY = 2.5;
            player.style.left = playerX + '%';
            player.style.top = playerY + '%';
            isGameOver = false;
            messageOverlay.style.display = 'none';
            startTime = Date.now();
            timerInterval = setInterval(updateTimer, 100);
        }

        function updateTimer() {
            const currentTime = (Date.now() - startTime) / 1000;
            timerDisplay.textContent = `Tiempo: ${currentTime.toFixed(1)}s`;
        }

        document.addEventListener('keydown', (e) => {
            switch(e.key) {
                case 'ArrowUp': movePlayer(0, -2.5); break;
                case 'ArrowDown': movePlayer(0, 2.5); break;
                case 'ArrowLeft': movePlayer(-2.5, 0); break;
                case 'ArrowRight': movePlayer(2.5, 0); break;
            }
        });

        restartBtn.addEventListener('click', startGame);

        createWalls();
        startGame();
    </script>
</body>
</html>
