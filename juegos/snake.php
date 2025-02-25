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

// Manejar la solicitud AJAX para actualizar puntaje
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'actualizarPuntaje' && isset($data['score'])) {
        $score = $data['score'];
        $puntos = 1; // puntuación base
        
        if ($score >= 50) {
            $puntos = 5;
        } elseif ($score >= 30) {
            $puntos = 3;
        }
        
        $resultado = actualizarPuntaje($puntos);
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
}

function actualizarPuntaje($puntos) {
    global $conexion;
    $jugadorId = $_SESSION['jugador_actual']['id'];
    
    $sql = "SELECT puntaje, juego_actual FROM jugadores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $jugadorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $nuevoPuntaje = $row['puntaje'] + $puntos;
    $nuevoJuegoActual = $row['juego_actual'] + 1;
    
    $sql = "UPDATE jugadores SET puntaje = ?, juego_actual = ?, tiempo_fin = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iii", $nuevoPuntaje, $nuevoJuegoActual, $jugadorId);
    
    if ($stmt->execute()) {
        return ['success' => true, 'puntos' => $puntos, 'nuevoPuntaje' => $nuevoPuntaje];
    }
    return ['success' => false];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snake Game</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            font-family: 'Press Start 2P', cursive;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            box-sizing: border-box;
        }

        #game-container {
            width: min(95vw, 600px);
            height: min(95vh, 800px);
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            border: 2px solid #00ff88;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow: hidden;
        }

        h1 {
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
            font-size: clamp(16px, 4vw, 28px);
            margin: 0;
        }

        .descripcion-juego {
            color: #fff;
            font-size: clamp(8px, 2vw, 12px);
            line-height: 1.5;
            margin: 5px 0;
        }

        #game-board {
            width: min(90vw, 400px);
            height: min(90vw, 400px);
            border: 2px solid #00ff88;
            background-color: #000;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.2);
            margin: 10px auto;
            border-radius: 8px;
        }

        #score, #timer {
            font-size: clamp(12px, 2.5vw, 16px);
            color: #00ff88;
            margin: 5px 0;
            text-shadow: 0 0 5px rgba(0, 255, 136, 0.5);
        }

        .controls-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: auto;
        }

        #mobile-controls {
            display: none;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 8px;
            justify-content: center;
            margin: 10px 0;
        }

        .control-button {
            width: 45px;
            height: 45px;
            background: rgba(0, 255, 136, 0.2);
            border: 2px solid #00ff88;
            border-radius: 50%;
            color: #fff;
            font-size: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .control-button:active {
            transform: scale(0.95);
            background: rgba(0, 255, 136, 0.4);
        }

        .button-container {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .game-button {
            padding: 10px 20px;
            font-family: 'Press Start 2P', cursive;
            font-size: clamp(10px, 2vw, 14px);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #restart-button {
            background-color: #00ff88;
            color: #000;
        }

        #back-button {
            background-color: #666;
            color: #fff;
        }

        .game-button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            #game-container {
                padding: 15px;
                gap: 8px;
            }

            #mobile-controls {
                display: grid;
            }

            #game-board {
                width: min(85vw, 300px);
                height: min(85vw, 300px);
            }
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: rgba(26, 26, 46, 0.95);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #00ff88;
            text-align: center;
            color: #fff;
            max-width: 80%;
        }

        .modal-content button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #00ff88;
            border: none;
            border-radius: 5px;
            color: #000;
            font-family: 'Press Start 2P', cursive;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="game-container">
        <h1>SNAKE GAME</h1>
        <p class="descripcion-juego"><?php echo htmlspecialchars($descripcion); ?></p>
        <div id="score">Puntuación: 0</div>
        <div id="timer">Tiempo: 0s</div>
        <canvas id="game-board" width="400" height="400"></canvas>
        <div class="button-container">
            <button id="restart-button" class="game-button">Reiniciar</button>
            <button id="back-button" class="game-button" onclick="window.location.href='../views/evento.php'">Volver</button>
        </div>
    </div>

    <div id="mobile-controls">
        <div></div>
        <button class="control-button" onclick="handleMobileControl('ArrowUp')">↑</button>
        <div></div>
        <button class="control-button" onclick="handleMobileControl('ArrowLeft')">←</button>
        <button class="control-button" onclick="handleMobileControl('ArrowDown')">↓</button>
        <button class="control-button" onclick="handleMobileControl('ArrowRight')">→</button>
    </div>

    <div id="winModal" class="modal">
        <div class="modal-content">
            <h2>¡Juego Terminado!</h2>
            <p id="modal-mensaje"></p>
            <button onclick="window.location.href='../views/evento.php'">Aceptar</button>
        </div>
    </div>

    <script>
        const canvas = document.getElementById("game-board");
        const ctx = canvas.getContext("2d");
        const scoreDisplay = document.getElementById("score");
        const timerDisplay = document.getElementById("timer");
        const restartButton = document.getElementById("restart-button");

        const gridSize = 20;
        const tileCount = canvas.width / gridSize;
        let snake = [{ x: 10, y: 10 }];
        let food = { x: 5, y: 5 };
        let direction = { x: 0, y: 0 };
        let score = 0;
        let time = 0;
        let gameInterval;
        let timerInterval;

        function startGame() {
            clearInterval(gameInterval);
            clearInterval(timerInterval);
            snake = [{ x: 10, y: 10 }];
            food = { x: 5, y: 5 };
            direction = { x: 0, y: 0 };
            score = 0;
            time = 0;
            scoreDisplay.textContent = "Puntuación: 0";
            timerDisplay.textContent = "Tiempo: 0s";
            gameInterval = setInterval(gameLoop, 100);
            timerInterval = setInterval(updateTimer, 1000);
        }

        function gameLoop() {
            update();
            draw();
            checkCollision();
        }

        function update() {
            const head = { x: snake[0].x + direction.x, y: snake[0].y + direction.y };
            snake.unshift(head);

            if (head.x === food.x && head.y === food.y) {
                score++;
                scoreDisplay.textContent = "Puntuación: " + score;
                placeFood();
            } else {
                snake.pop();
            }
        }

        function draw() {
            ctx.fillStyle = "#000";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Dibujar la serpiente
            snake.forEach((segment, index) => {
                // Color degradado para la serpiente
                const hue = (120 + index * 2) % 360; // Variación de color verde
                ctx.fillStyle = `hsl(${hue}, 100%, 50%)`;
                
                if (index === 0) {
                    // Cabeza redonda
                    ctx.beginPath();
                    ctx.arc(
                        (segment.x + 0.5) * gridSize,
                        (segment.y + 0.5) * gridSize,
                        gridSize/2,
                        0,
                        Math.PI * 2
                    );
                    ctx.fill();
                } else {
                    // Cuerpo con bordes redondeados
                    ctx.beginPath();
                    ctx.roundRect(
                        segment.x * gridSize,
                        segment.y * gridSize,
                        gridSize,
                        gridSize,
                        4
                    );
                    ctx.fill();
                }
            });

            // Dibujar la comida con efecto brillante
            ctx.fillStyle = "#ff3300";
            ctx.beginPath();
            ctx.arc(
                (food.x + 0.5) * gridSize,
                (food.y + 0.5) * gridSize,
                gridSize/2,
                0,
                Math.PI * 2
            );
            ctx.fill();

            // Efecto de brillo en la comida
            ctx.shadowColor = "#ff3300";
            ctx.shadowBlur = 10;
            ctx.beginPath();
            ctx.arc(
                (food.x + 0.5) * gridSize,
                (food.y + 0.5) * gridSize,
                gridSize/4,
                0,
                Math.PI * 2
            );
            ctx.fill();
            ctx.shadowBlur = 0;
        }

        function placeFood() {
            food.x = Math.floor(Math.random() * tileCount);
            food.y = Math.floor(Math.random() * tileCount);
        }

        function checkCollision() {
            const head = snake[0];
            if (
                head.x < 0 || head.x >= tileCount ||
                head.y < 0 || head.y >= tileCount ||
                snake.slice(1).some(segment => segment.x === head.x && segment.y === head.y)
            ) {
                endGame();
            }
        }

        function endGame() {
            clearInterval(gameInterval);
            clearInterval(timerInterval);
            
            let puntos = 1;
            if (score >= 50) {
                puntos = 5;
            } else if (score >= 30) {
                puntos = 3;
            }

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'actualizarPuntaje',
                    score: score
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('modal-mensaje').innerHTML = 
                        `Puntuación final: ${score}<br>
                        Puntos ganados: ${data.puntos}<br>
                        Puntaje total: ${data.nuevoPuntaje}`;
                } else {
                    document.getElementById('modal-mensaje').innerHTML = 
                        'Error al actualizar puntaje.';
                }
                document.getElementById('winModal').style.display = 'flex';
            });
        }

        function updateTimer() {
            time++;
            timerDisplay.textContent = "Tiempo: " + time + "s";
        }

        window.addEventListener("keydown", e => {
            switch (e.key) {
                case "ArrowUp":
                    if (direction.y === 0) direction = { x: 0, y: -1 };
                    break;
                case "ArrowDown":
                    if (direction.y === 0) direction = { x: 0, y: 1 };
                    break;
                case "ArrowLeft":
                    if (direction.x === 0) direction = { x: -1, y: 0 };
                    break;
                case "ArrowRight":
                    if (direction.x === 0) direction = { x: 1, y: 0 };
                    break;
            }
        });

        restartButton.addEventListener("click", startGame);

        function handleMobileControl(key) {
            const event = new KeyboardEvent('keydown', { key: key });
            window.dispatchEvent(event);
        }

        startGame();
    </script>
</body>
</html>