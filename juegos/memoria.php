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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['action']) && $data['action'] === 'actualizarPuntaje' && isset($data['puntos'])) {
        $resultado = actualizarPuntaje($data['puntos']);
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memorizando</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Press Start 2P', cursive;
            background-color: #1a1a1a;
            color: #fff;
        }
        .container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .game-container {
            text-align: center;
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            max-width: 100%;
            width: 450px;
        }
        .game-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .card {
            width: 100%;
            padding-bottom: 100%; /* Aspect ratio 1:1 */
            background-color: #4a4a4a;
            position: relative;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }
        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 255, 0, 0.5);
        }
        .card img {
            position: absolute;
            top: 5%;
            left: 5%;
            width: 90%;
            height: 90%;
            object-fit: cover;
            display: none;
            border-radius: 5px;
        }
        .card.flipped img {
            display: block;
        }
        .card.flipped {
            transform: rotateY(180deg);
            background-color: #6a6a6a;
        }
        p.card-text{
            font-sice: 12px;
        }
        #restart-button {
            font-family: 'Press Start 2P', cursive;
            border: none;
            padding: 12px 5px;
        }
        #win-message {
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
        #win-content {
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            max-width: 90%;
        }
        #win-content img {
            width: 50px;
            margin-top: 10px;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-shadow: 0 0 20px rgba(0, 255, 0, 0.5),
            0 0 20px rgba(0, 255, 0, 0.5),
            0 0 20px rgba(0, 255, 0, 0.5);
            color: #fff;
        }
        @media (max-width: 576px) {
            .game-container {
                padding: 10px;
                width: 95%;
            }
            .game-board {
                gap: 5px;
            }
            h1 {
                font-size: 20px;
            }
            h2{
                font-size: 20px;
            }
            #restart-button {
                font-size: 0.7rem;
                padding: 12x 5px;
            }
        }
        .back-btn {
            background-color: rgba(80, 80, 80, 0.9);
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
            background-color: rgba(50, 50, 50, 0.9);
        }
        #timer {
            text-shadow: 0 0 5px #00ff00,
                         0 0 5px #00ff00,
                         0 0 5px #00ff00;
            color: #fff;
            font-weight: bold;
        }
        .reset-boton, .aceptar-btn{
            font-size: 10px;
            border: none;
            padding: 12px 5px;
            color: black;
            background-color:#0a0;
            transition: all 0.3s ease;
        }
        .reset-boton:hover, .aceptar-btn:hover{
            color: black;
            background-color: #0d0;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="game-container" style="width: 600px;">
            <h1>Memoria</h1>
            <p class="card-text" style="font-size: 12px;"><?php echo htmlspecialchars($descripcion);?></p>
            <div id="timer" class="mb-3" style="font-size: 11px; text-shadow: 0 0 20px rgba(0, 255, 0, 0.5),
            0 0 20px rgba(0, 255, 0, 0.5),
            0 0 20px rgba(0, 255, 0, 0.5);">Tiempo: 0s</div>
            <div class="game-board mb-3">
                <!-- Las tarjetas se generarán dinámicamente con JavaScript -->
            </div>
            <button id="restart-button" class="btn reset-boton">Reiniciar Juego</button>
            <button onclick="window.location.href='../views/evento.php'" class="back-btn">Volver</button>
        </div>
    </div>

    <div id="win-message">
        <div id="win-content">
            <h2>Muy buena memoria, aca tienes tu llave</h2>
            <p id="tiempo-final"></p>
            <p id="puntos-ganados"></p>
            <p id="puntaje-total"></p>
            <img src="../images/key.png" alt="Llave">
            <button onclick="redirigirEvento()" class="btn btn-success mt-3 aceptar-btn">Aceptar</button>
        </div>
    </div>

    <script>
        const images = [
            'https://artishockrevista.com/wp-content/uploads/2020/06/1-illimaniinsitu.jpg',
            'https://www.opinion.com.bo/asset/thumbnail,992,558,center,center/media/opinion/images/2013/08/06/2013N102477.jpg',
            'https://i.pinimg.com/474x/20/78/27/2078277d2ee2fba144fb18acf676486b.jpg',
            'https://thumbs.dreamstime.com/b/bolivian-saltena-meat-pastries-traditional-savory-called-filled-thick-stew-which-popular-street-snack-bolivia-51131075.jpg',
            'https://adminweb.miteleferico.bo/uploads/NARANJA_009e523187.jpg',
            'https://www.bolivar.com.bo/data/picture/2011/Hinchas_Miraflores_Bolivar_The_Strongest_LRZIMA20141029_0021_1.jpg',
            'https://avianreport.com/wp-content/uploads/2023/05/condor-andino-450-550.x46230.webp',
            'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTQ_KvHDAdtG_qZy_IdT7HiCvjm_H7OXhPHeYg9tyFrHFanzr2Ep2-54nPDFhmfO6h1enI&usqp=CAU'
        ];

        let cards = [...images, ...images];
        let flippedCards = [];
        let matchedPairs = 0;
        let startTime;
        let timerInterval;

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        function createCard(image) {
            const card = document.createElement('div');
            card.classList.add('card');
            const img = document.createElement('img');
            img.src = image;
            card.appendChild(img);
            card.addEventListener('click', flipCard);
            return card;
        }

        function flipCard() {
            if (flippedCards.length < 2 && !this.classList.contains('flipped')) {
                this.classList.add('flipped');
                flippedCards.push(this);

                if (flippedCards.length === 2) {
                    setTimeout(checkMatch, 1000);
                }
            }
        }

        function checkMatch() {
            const [card1, card2] = flippedCards;
            const img1 = card1.querySelector('img').src;
            const img2 = card2.querySelector('img').src;

            if (img1 === img2) {
                matchedPairs++;
                if (matchedPairs === images.length) {
                    showWinMessage();
                }
            } else {
                card1.classList.remove('flipped');
                card2.classList.remove('flipped');
            }

            flippedCards = [];
        }

        function updateTimer() {
            const currentTime = (Date.now() - startTime) / 1000;
            document.getElementById('timer').textContent = `Tiempo: ${currentTime.toFixed(1)}s`;
        }

        function showWinMessage() {
            const endTime = (Date.now() - startTime) / 1000;
            clearInterval(timerInterval);
            
            let puntos = 1;
            if (endTime <= 20) {
                puntos = 3;
            } else if (endTime <= 40) {
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
                    document.getElementById('tiempo-final').textContent = `Tiempo: ${endTime.toFixed(2)}s`;
                    document.getElementById('puntos-ganados').textContent = `Puntos ganados: +${puntos}`;
                    document.getElementById('puntaje-total').textContent = `Puntaje total: ${data.nuevoPuntaje}`;
                }
                document.getElementById('win-message').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('win-message').style.display = 'flex';
            });
        }

        function redirigirEvento() {
            window.location.href = '../views/evento.php';
        }

        function initGame() {
            const gameBoard = document.querySelector('.game-board');
            gameBoard.innerHTML = '';
            matchedPairs = 0;
            shuffleArray(cards);

            cards.forEach(image => {
                const card = createCard(image);
                gameBoard.appendChild(card);
            });

            document.getElementById('win-message').style.display = 'none';
            startTime = Date.now();
            if (timerInterval) clearInterval(timerInterval);
            timerInterval = setInterval(updateTimer, 100);
        }

        document.getElementById('restart-button').addEventListener('click', initGame);
        initGame();
    </script>
</body>
</html>