<?php
// Este archivo contiene el juego de Rompecabezas en un solo archivo PHP.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rompecabezas</title>
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
        #puzzle-board {
            display: grid;
            grid-template-columns: repeat(5, 80px);
            grid-template-rows: repeat(5, 80px);
            gap: 5px;
            margin: 20px auto;
        }
        .puzzle-piece {
            width: 80px;
            height: 80px;
            background-size: 400px 400px;
            cursor: pointer;
            border: 2px solid #444;
            border-radius: 5px;
            transition: transform 0.2s;
        }
        .puzzle-piece:hover {
            transform: scale(1.05);
        }
        #timer {
            font-size: 20px;
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
        <h1>Rompecabezas</h1>
        <div id="timer">Tiempo: 0s</div>
        <div id="puzzle-board"></div>
        <button id="restart-button">Reiniciar</button>
    </div>

    <script>
        const board = document.getElementById("puzzle-board");
        const timerDisplay = document.getElementById("timer");
        const restartButton = document.getElementById("restart-button");
        const gridSize = 5;
        const pieceSize = 80;
        const imageUrl = "https://st3.depositphotos.com/5852012/15878/v/1600/depositphotos_158781058-stock-illustration-photo-gallery-flat-icon-with.jpg"; // Cambia esta URL por la imagen que desees
        let pieces = [];
        let emptyIndex = gridSize * gridSize - 1;
        let time = 0;
        let timerInterval;

        function createPuzzle() {
            board.innerHTML = "";
            pieces = [];
            for (let i = 0; i < gridSize * gridSize; i++) {
                const piece = document.createElement("div");
                piece.classList.add("puzzle-piece");
                if (i === emptyIndex) {
                    piece.style.backgroundColor = "#444";
                    piece.style.cursor = "default";
                } else {
                    const x = (i % gridSize) * pieceSize;
                    const y = Math.floor(i / gridSize) * pieceSize;
                    piece.style.backgroundImage = `url(${imageUrl})`;
                    piece.style.backgroundPosition = `-${x}px -${y}px`;
                    piece.addEventListener("click", () => movePiece(i));
                }
                pieces.push(piece);
                board.appendChild(piece);
            }
        }

        function movePiece(index) {
            const row = Math.floor(index / gridSize);
            const col = index % gridSize;
            const emptyRow = Math.floor(emptyIndex / gridSize);
            const emptyCol = emptyIndex % gridSize;

            if ((row === emptyRow && Math.abs(col - emptyCol) === 1) ||
                (col === emptyCol && Math.abs(row - emptyRow) === 1)) {
                [pieces[index], pieces[emptyIndex]] = [pieces[emptyIndex], pieces[index]];
                board.innerHTML = "";
                pieces.forEach(piece => board.appendChild(piece));
                emptyIndex = index;
                checkWin();
            }
        }

        function checkWin() {
            const isSolved = pieces.every((piece, index) => {
                if (index === emptyIndex) return true;
                const x = (index % gridSize) * pieceSize;
                const y = Math.floor(index / gridSize) * pieceSize;
                return piece.style.backgroundPosition === `-${x}px -${y}px`;
            });

            if (isSolved) {
                clearInterval(timerInterval);
                let finalScore;
                if (time < 60) {
                    finalScore = 3;
                } else if (time < 120) {
                    finalScore = 2;
                } else {
                    finalScore = 1;
                }
                alert(`¡Rompecabezas completado! Tiempo: ${time}s. Ganas ${finalScore} puntos.`);
            }
        }

        function shufflePieces() {
            for (let i = 0; i < 1000; i++) {
                const movablePieces = pieces.filter((_, index) => {
                    const row = Math.floor(index / gridSize);
                    const col = index % gridSize;
                    const emptyRow = Math.floor(emptyIndex / gridSize);
                    const emptyCol = emptyIndex % gridSize;
                    return (row === emptyRow && Math.abs(col - emptyCol) === 1) ||
                           (col === emptyCol && Math.abs(row - emptyRow) === 1);
                });
                const randomPiece = movablePieces[Math.floor(Math.random() * movablePieces.length)];
                movePiece(pieces.indexOf(randomPiece));
            }
        }

        function startGame() {
            clearInterval(timerInterval);
            time = 0;
            timerDisplay.textContent = "Tiempo: 0s";
            createPuzzle();
            shufflePieces();
            timerInterval = setInterval(updateTimer, 1000);
        }

        function updateTimer() {
            time++;
            timerDisplay.textContent = "Tiempo: " + time + "s";
        }

        restartButton.addEventListener("click", startGame);

        startGame();
    </script>
</body>
</html>