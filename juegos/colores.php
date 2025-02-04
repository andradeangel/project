<?php
// Este archivo contiene el juego de memoria de colores en un solo archivo PHP.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Rush</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1e1e1e;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }
        #game-container {
            text-align: center;
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        #color-board {
            display: grid;
            grid-template-columns: repeat(2, 100px);
            grid-template-rows: repeat(2, 100px);
            gap: 10px;
            margin: 20px auto;
        }
        .color-button {
            width: 100px;
            height: 100px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s, transform 0.2s;
        }
        .color-button:hover {
            opacity: 1;
            transform: scale(1.05);
        }
        #score {
            font-size: 20px;
            margin: 10px 0;
        }
        #timer {
            font-size: 20px;
            margin: 10px 0;
        }
        #start-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #start-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div id="game-container">
        <h1>Color Rush</h1>
        <div id="score">Puntuación: 0</div>
        <div id="timer">Tiempo: 0s</div>
        <div id="color-board">
            <button class="color-button" id="red" style="background-color: #ff4d4d;"></button>
            <button class="color-button" id="blue" style="background-color: #4d79ff;"></button>
            <button class="color-button" id="green" style="background-color: #4dff4d;"></button>
            <button class="color-button" id="yellow" style="background-color: #ffff4d;"></button>
        </div>
        <button id="start-button">Comenzar</button>
    </div>

    <script>
        const colorButtons = document.querySelectorAll(".color-button");
        const scoreDisplay = document.getElementById("score");
        const timerDisplay = document.getElementById("timer");
        const startButton = document.getElementById("start-button");
        const colors = ["red", "blue", "green", "yellow"];
        let sequence = [];
        let playerSequence = [];
        let score = 0;
        let time = 0;
        let timerInterval;
        let isPlaying = false;

        function startGame() {
            if (isPlaying) return;
            isPlaying = true;
            sequence = [];
            playerSequence = [];
            score = 0;
            time = 0;
            scoreDisplay.textContent = "Puntuación: 0";
            timerDisplay.textContent = "Tiempo: 0s";
            clearInterval(timerInterval);
            timerInterval = setInterval(updateTimer, 1000);
            nextLevel();
        }

        function nextLevel() {
            playerSequence = [];
            sequence.push(colors[Math.floor(Math.random() * colors.length)]);
            showSequence();
        }

        function showSequence() {
            let i = 0;
            const interval = setInterval(() => {
                flashColor(sequence[i]);
                i++;
                if (i >= sequence.length) {
                    clearInterval(interval);
                    enableButtons();
                }
            }, 1000);
        }

        function flashColor(color) {
            const button = document.getElementById(color);
            button.style.opacity = 1;
            setTimeout(() => {
                button.style.opacity = 0.7;
            }, 500);
        }

        function enableButtons() {
            colorButtons.forEach(button => {
                button.addEventListener("click", handleClick);
            });
        }

        function disableButtons() {
            colorButtons.forEach(button => {
                button.removeEventListener("click", handleClick);
            });
        }

        function handleClick(e) {
            const color = e.target.id;
            playerSequence.push(color);
            flashColor(color);
            if (playerSequence.length === sequence.length) {
                checkSequence();
            }
        }

        function checkSequence() {
            disableButtons();
            if (playerSequence.toString() === sequence.toString()) {
                score++;
                scoreDisplay.textContent = "Puntuación: " + score;
                setTimeout(nextLevel, 1000);
            } else {
                endGame();
            }
        }

        function endGame() {
            clearInterval(timerInterval);
            isPlaying = false;
            alert(`¡Juego terminado! Puntuación: ${score}.`);
        }

        function updateTimer() {
            time++;
            timerDisplay.textContent = "Tiempo: " + time + "s";
        }

        startButton.addEventListener("click", startGame);
    </script>
</body>
</html>