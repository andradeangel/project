<?php
// Este archivo contiene el juego de Snake en un solo archivo PHP.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snake Game</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        #game-container {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #game-board {
            border: 2px solid #333;
            background-color: #000;
            display: inline-block;
        }
        #score, #timer {
            font-size: 20px;
            color: #333;
            margin: 10px 0;
        }
        #restart-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #restart-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div id="game-container">
        <h1>Snake Game</h1>
        <div id="score">Puntuación: 0</div>
        <div id="timer">Tiempo: 0s</div>
        <canvas id="game-board" width="400" height="400"></canvas>
        <button id="restart-button">Reiniciar</button>
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

            ctx.fillStyle = "#4CAF50";
            snake.forEach(segment => ctx.fillRect(segment.x * gridSize, segment.y * gridSize, gridSize, gridSize));

            ctx.fillStyle = "#FF0000";
            ctx.fillRect(food.x * gridSize, food.y * gridSize, gridSize, gridSize);
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
            let finalScore;
            if (score < 10) {
                finalScore = 1;
            } else if (score < 15) {
                finalScore = 2;
            } else {
                finalScore = 3;
            }
            alert(`¡Juego terminado! Puntuación: ${score}. Ganas ${finalScore} puntos.`);
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

        startGame();
    </script>
</body>
</html>