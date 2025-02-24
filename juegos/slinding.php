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
    
    if (isset($data['action']) && $data['action'] === 'actualizarPuntaje' && isset($data['movimientos'])) {
        // Calcular puntos basado en movimientos
        $movimientos = $data['movimientos'];
        $puntos = 2; // puntuación base
        
        if ($movimientos <= 25) {
            $puntos = 5;
        } elseif ($movimientos <= 50) {
            $puntos = 3;
        }
        
        // Actualizar puntaje en la base de datos
        $jugadorId = $_SESSION['jugador_actual']['id'];
        $sql = "UPDATE jugadores SET 
                puntaje = puntaje + ?,
                juego_actual = juego_actual + 1 
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $puntos, $jugadorId);
        $resultado = $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $resultado,
            'puntos' => $puntos,
            'nuevoPuntaje' => $resultado ? obtenerNuevoPuntaje($jugadorId) : null
        ]);
        exit;
    }
}

function obtenerNuevoPuntaje($jugadorId) {
    global $conexion;
    $sql = "SELECT puntaje FROM jugadores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $jugadorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['puntaje'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sliding Puzzle</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            font-family: 'Press Start 2P', cursive;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        #game-container {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #ff3300;
            border-radius: 15px;
            padding: 20px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 0 20px rgba(255, 51, 0, 0.3);
            text-align: center;
        }

        h1 {
            color: #ff3300;
            text-shadow: 0 0 10px rgba(255, 51, 0, 0.5);
            margin: 0 0 20px 0;
            font-size: clamp(24px, 4vw, 32px);
        }

        .descripcion-juego {
            color: #fff;
            margin: 15px 0;
            font-size: clamp(10px, 2vw, 14px);
            line-height: 1.5;
            text-shadow: 0 0 5px rgba(255, 51, 0, 0.3);
        }

        .puzzle-container {
            width: min(300px, 90vw);
            height: min(300px, 90vw);
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #ff3300;
            padding: 10px;
            border-radius: 8px;
        }

        .puzzle-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 2px;
            width: 100%;
            height: 100%;
            background: #16213e;
        }

        .puzzle-tile {
            background-image: url('https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png');
            background-size: 300% 300%;
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0; /* Ocultar números */
            cursor: pointer;
            border-radius: 4px;
            transition: transform 0.3s ease;
            border: 2px solid #ff3300;
        }

        .puzzle-tile:hover {
            transform: scale(0.98);
            box-shadow: 0 0 15px rgba(255, 51, 0, 0.3);
        }

        .empty {
            background: #16213e;
            border: 2px dashed #ff3300;
        }

        .moves {
            color: #ff3300;
            font-size: clamp(14px, 3vw, 18px);
            margin: 20px 0;
            text-shadow: 0 0 5px rgba(255, 51, 0, 0.5);
        }

        .shuffle-btn {
            background: linear-gradient(45deg, #ff3300, #ff6600);
            border: none;
            padding: 15px 30px;
            color: white;
            font-family: 'Press Start 2P', cursive;
            font-size: clamp(12px, 2vw, 16px);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .shuffle-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 51, 0, 0.5);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #16213e;
            padding: 30px;
            border-radius: 15px;
            border: 2px solid #ff3300;
            text-align: center;
            max-width: 90%;
            width: 300px;
        }

        .modal h2 {
            color: #ff3300;
            font-size: clamp(18px, 1vw, 24px);
            margin-bottom: 20px;
            text-shadow: 0 0 10px rgba(255, 51, 0, 0.5);
        }

        .modal button {
            background: linear-gradient(45deg, #ff3300, #ff6600);
            border: none;
            padding: 12px 25px;
            color: white;
            font-family: 'Press Start 2P', cursive;
            font-size: clamp(12px, 2vw, 14px);
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .modal button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 51, 0, 0.5);
        }

        @media (max-height: 600px) {
            .puzzle-container {
                margin: 10px auto;
            }
            
            .moves {
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div id="game-container">
        <h1>SLIDING PUZZLE</h1>
        <p class="descripcion-juego"><?php echo htmlspecialchars($descripcion); ?></p>
        
        <div class="puzzle-container">
            <div class="puzzle-grid" id="puzzle"></div>
        </div>
        
        <div class="controls">
            <div class="moves" id="moves">Movimientos: 0</div>
            <button onclick="shufflePuzzle()" class="shuffle-btn">Mezclar</button>
        </div>
    </div>

    <div id="winModal" class="modal">
        <div class="modal-content">
            <h2>¡Felicitaciones!</h2>
            <p id="modal-mensaje"></p>
            <button onclick="window.location.href='../views/evento.php'">Aceptar</button>
        </div>
    </div>

    <script>
        let tiles = [1, 2, 3, 4, 5, 6, 7, 8, null];
        let moveCount = 0;
        let gameStarted = false;
        
        function createPuzzle() {
            const puzzleElement = document.getElementById('puzzle');
            puzzleElement.innerHTML = '';
            
            tiles.forEach((tile, index) => {
                const tileElement = document.createElement('div');
                tileElement.className = `puzzle-tile ${!tile ? 'empty' : ''}`;
                if (tile) {
                    const row = Math.floor((tile - 1) / 3);
                    const col = (tile - 1) % 3;
                    tileElement.style.backgroundPosition = `${-col * 100}% ${-row * 100}%`;
                }
                tileElement.addEventListener('click', () => moveTile(index));
                puzzleElement.appendChild(tileElement);
            });
        }

        function moveTile(index) {
            const emptyIndex = tiles.indexOf(null);
            
            if (isValidMove(index, emptyIndex)) {
                [tiles[index], tiles[emptyIndex]] = [tiles[emptyIndex], tiles[index]];
                moveCount++;
                document.getElementById('moves').textContent = `Movimientos: ${moveCount}`;
                createPuzzle();
                
                if (checkWin()) {
                    finalizarJuego();
                }
            }
        }

        function isValidMove(index, emptyIndex) {
            const row = Math.floor(index / 3);
            const col = index % 3;
            const emptyRow = Math.floor(emptyIndex / 3);
            const emptyCol = emptyIndex % 3;
            
            return (
                (Math.abs(row - emptyRow) === 1 && col === emptyCol) ||
                (Math.abs(col - emptyCol) === 1 && row === emptyRow)
            );
        }

        function shufflePuzzle() {
            for (let i = 0; i < 200; i++) {
                const emptyIndex = tiles.indexOf(null);
                const validMoves = [];
                
                for (let j = 0; j < tiles.length; j++) {
                    if (isValidMove(j, emptyIndex)) {
                        validMoves.push(j);
                    }
                }
                
                const randomMove = validMoves[Math.floor(Math.random() * validMoves.length)];
                [tiles[randomMove], tiles[emptyIndex]] = [tiles[emptyIndex], tiles[randomMove]];
            }
            
            moveCount = 0;
            document.getElementById('moves').textContent = `Movimientos: ${moveCount}`;
            createPuzzle();
        }

        function checkWin() {
            for (let i = 0; i < tiles.length - 1; i++) {
                if (tiles[i] !== i + 1) {
                    return false;
                }
            }
            return true;
        }

        function finalizarJuego() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'actualizarPuntaje',
                    movimientos: moveCount
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('modal-mensaje').innerHTML = 
                        `¡Completaste el puzzle en ${moveCount} movimientos!<br><br>
                        Puntos ganados: ${data.puntos}<br>
                        Puntaje total: ${data.nuevoPuntaje}`;
                    
                    // Mostrar el modal
                    document.getElementById('winModal').style.display = 'flex';
                    
                    // Redirigir al siguiente juego después de cerrar el modal
                    document.querySelector('#winModal button').onclick = function() {
                        window.location.href = '../views/evento.php';
                    };
                }
            });
        }

        // Inicializar el juego
        createPuzzle();
    </script>
</body>
</html>