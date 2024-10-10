<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laberinto</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Press Start 2P', cursive;
            color: white;
            background: url('https://wallpapers.com/images/hd/labyrinth-on-blank-black-qu7ldvf7f59j1ieq.jpg') no-repeat center center fixed;
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
            width: 90vmin;
            height: 90vmin;
            max-width: 450px;
            max-height: 450px;
            position: relative;
            background-color: rgba(17, 17, 17, 0.8);
            border: 3px solid #0f0;
            box-shadow: 0 0 10px #0f0;
        }
        #player {
            width: 3.33%;
            height: 3.33%;
            background-color: #0f0;
            position: absolute;
            border-radius: 50%;
            transition: all 0.1s;
            z-index: 10;
        }
        .wall {
            position: absolute;
            background-color: #f00;
            width: 5%;
            height: 5%;
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
            margin-top: 10px;
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
            font-size: 18px;
            color: #0f0;
            background-color: #111;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #0f0;
            box-shadow: 0 0 10px #0f0;
        }
        h1 {
            font-size: 25px;
            margin-bottom: 10px;
        }
        #key {
            position: absolute;
            width: 3.5%;
            right: 0;
            bottom: 5.5%;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Laberinto</h1>
        <div id="game-container">
            <div id="player"></div>
            <img id="key" src="../images/key.png" alt="Llave">
        </div>
        <div id="timer">Tiempo: 0s</div>
        <div id="message"></div>
        <div id="controls">
            <button class="btn btn-primary control-btn" onclick="movePlayer(0, -5)">↑</button>
            <button class="btn btn-primary control-btn" onclick="movePlayer(-5, 0)">←</button>
            <button class="btn btn-primary control-btn" onclick="movePlayer(5, 0)">→</button>
            <button class="btn btn-primary control-btn" onclick="movePlayer(0, 5)">↓</button>
        </div>
        <div class="mt-2">
            <button id="restartBtn" class="btn btn-success">Reiniciar</button>
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
        let playerY = 5.77;  // Ajustado para la nueva escala
        let isGameOver = false;
        let startTime;
        let timerInterval;

        const laberinto = [
            [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
            [0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1],
            [1, 1, 1, 0, 1, 0, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 0, 1],
            [1, 0, 0, 0, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1],
            [1, 0, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1],
            [1, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 1],
            [1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1],
            [1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 1],
            [1, 0, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 0, 1],
            [1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 1, 0, 1],
            [1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1],
            [1, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 1],
            [1, 0, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1, 0, 1],
            [1, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 1],
            [1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1, 1, 1, 1, 1],
            [1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1],
            [1, 0, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1],
            [1, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1],
            [1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 0, 0, 0, 1, 1, 1, 1, 1, 0, 0],
            [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]
        ];

        function createWalls() {
            for (let y = 0; y < laberinto.length; y++) {
                for (let x = 0; x < laberinto[y].length; x++) {
                    if (laberinto[y][x] === 1) {
                        const wallElement = document.createElement('div');
                        wallElement.className = 'wall';
                        wallElement.style.left = (x * 5) + '%';
                        wallElement.style.top = (y * 5) + '%';
                        gameContainer.appendChild(wallElement);
                    }
                }
            }
        }

        function movePlayer(dx, dy) {
            if (isGameOver) return;

            const newX = playerX + dx;
            const newY = playerY + dy;

            const gridX = Math.floor(newX / 5);
            const gridY = Math.floor(newY / 5);

            if (gridX >= 0 && gridX < 20 && gridY >= 0 && gridY < 20) {
                if (laberinto[gridY][gridX] === 0 || (gridX === 19 && gridY === 18)) {
                    playerX = newX;
                    playerY = newY;
                    player.style.left = playerX + '%';
                    player.style.top = playerY + '%';

                    if (gridX === 19 && gridY === 18) {
                        gameOver(true);
                    }
                }
            }
        }

        function gameOver(win) {
            isGameOver = true;
            clearInterval(timerInterval);
            const endTime = (Date.now() - startTime) / 1000;
            overlayMessageDisplay.textContent = win ? `¡Llave conseguida!\nTiempo: ${endTime.toFixed(2)}s` : '¡Perdiste! Inténtalo de nuevo.';
            overlayMessageDisplay.style.color = win ? '#0f0' : '#f00';
            messageOverlay.style.display = 'flex';
        }

        function startGame() {
            playerX = 0;
            playerY = 5.77;  // Ajustado para la nueva escala
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
                case 'ArrowUp': movePlayer(0, -5); break;
                case 'ArrowDown': movePlayer(0, 5); break;
                case 'ArrowLeft': movePlayer(-5, 0); break;
                case 'ArrowRight': movePlayer(5, 0); break;
            }
        });

        restartBtn.addEventListener('click', startGame);

        createWalls();
        startGame();
    </script>
</body>
</html>
