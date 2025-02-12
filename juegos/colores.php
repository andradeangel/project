<?php
require_once('../database.php');
custom_session_start('player_session');

// Obtener el ID del juego actual
$juego_id = $_GET['juego_id'] ?? null;

// Obtener la descripción del juego desde la base de datos
$sql = "SELECT descripcion FROM juegos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $juego_id);
$stmt->execute();
$result = $stmt->get_result();
$descripcion = $result->fetch_assoc()['descripcion'] ?? 'Descripción no disponible';

// Inicializar variables del juego
$ronda = 0;
$totalRondas = 5;
$puntajeTotal = 0;
$coloresPorRonda = [1, 3, 4, 6, 7]; // Colores a presionar por ronda
$colores = ['#ff4d4d', '#4d79ff', '#4dff4d', '#ffff4d', '#ffffff', '#808080']; // Colores disponibles
$coloresSeleccionados = [];
$coloresPresionados = 0;

function mostrarModal($ronda, $puntaje, $puntajeTotal) {
    echo "<script>
        alert('Has llegado a la ronda $ronda. Llaves conseguidas: $puntaje. Puntaje total: $puntajeTotal.');
        window.location.href = '../views/evento.php';
    </script>";
}

// Lógica del juego
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí se manejaría la lógica de las rondas y la puntuación
    // ...
}

// ... Código HTML y JavaScript para el juego ...
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Rush</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1e1e1e;
            color: #fff;
            text-align: center;
        }
        #game-container {
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            margin: auto;
            width: 80%;
        }
        .color-button {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
        }
        #progress-bar {
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
            margin: 10px 0;
        }
        #progress {
            height: 20px;
            background-color: #4CAF50;
            width: 0%;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="game-container">
        <h1>Color Rush</h1>
        <p><?php echo $descripcion; ?></p>
        <div id="round-info">Ronda: <span id="round-number">0</span>/5</div>
        <div id="progress-bar"><div id="progress"></div></div>
        <div id="press-info">Colores presionados: <span id="press-count">0</span>/<span id="total-colors">0</span></div>
        <div id="color-board" style="display: grid; grid-template-columns: repeat(5, 1fr);">
            <?php foreach ($colores as $color): ?>
                <button class="color-button" style="background-color: <?php echo $color; ?>;"></button>
            <?php endforeach; ?>
        </div>
        <button id="start-button" class="btn btn-success">Empezar</button>
    </div>

    <script>
        const startButton = document.getElementById('start-button');
        const roundInfo = document.getElementById('round-info');
        const pressInfo = document.getElementById('press-info');
        const progress = document.getElementById('progress');
        const colorButtons = document.querySelectorAll('.color-button');
        let ronda = 0;
        let puntajeTotal = 0;
        let coloresPorRonda = [1, 3, 4, 6, 7];
        let coloresSeleccionados = [];
        let coloresPresionados = 0;

        startButton.addEventListener('click', startGame);

        function startGame() {
            ronda++;
            if (ronda > 5) {
                mostrarModal(ronda, puntajeTotal, puntajeTotal);
                return;
            }
            roundInfo.querySelector('#round-number').textContent = ronda;
            coloresSeleccionados = [];
            coloresPresionados = 0;
            pressInfo.querySelector('#press-count').textContent = coloresPresionados;
            pressInfo.querySelector('#total-colors').textContent = coloresPorRonda[ronda - 1];
            progress.style.width = '0%';
            mostrarColores();
        }

        function mostrarColores() {
            const colorIndex = Math.floor(Math.random() * 5);
            const colorButton = colorButtons[colorIndex];
            colorButton.style.opacity = 1;
            setTimeout(() => {
                colorButton.style.opacity = 0.5;
            }, 200);
        }

        colorButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (coloresPresionados < coloresPorRonda[ronda - 1]) {
                    coloresPresionados++;
                    pressInfo.querySelector('#press-count').textContent = coloresPresionados;
                    if (coloresPresionados === coloresPorRonda[ronda - 1]) {
                        puntajeTotal += (ronda - 1);
                        progress.style.width = ((ronda / 5) * 100) + '%';
                        setTimeout(startGame, 1000);
                    }
                } else {
                    mostrarModal(ronda, puntajeTotal, puntajeTotal);
                }
            });
        });
    </script>
</body>
</html>