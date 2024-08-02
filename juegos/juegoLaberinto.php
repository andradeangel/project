<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maze Game</title>
</head>
<body>
    <canvas id="mazeCanvas"></canvas>
    <script>
        const canvas = document.getElementById('mazeCanvas');
        const ctx = canvas.getContext('2d');
        const width = 400; // Ancho del laberinto
        const height = 400; // Alto del laberinto

        // Genera un laberinto utilizando el algoritmo de Backtracking
        function generateMaze() {
            const maze = new Array(width).fill(null).map(() => new Array(height).fill(false));
            // Implementa aquí tu lógica para generar el laberinto
            // ...

            return maze;
        }

        // Dibuja el laberinto en el canvas
        function drawMaze(maze) {
            const cellSize = 20; // Tamaño de cada celda
            canvas.width = width;
            canvas.height = height;

            maze.forEach((row, x) => {
                row.forEach((cell, y) => {
                    ctx.fillStyle = cell ? 'black' : 'white';
                    ctx.fillRect(x * cellSize, y * cellSize, cellSize, cellSize);
                });
            });
        }

        const maze = generateMaze();
        drawMaze(maze);
    </script>
</body>
</html>
