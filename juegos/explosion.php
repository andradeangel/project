<?php
require_once('../database.php');
custom_session_start('player_session');

if (!isset($_SESSION['jugador_actual']) || !isset($_SESSION['evento_actual'])) {
    error_log("Redirección a evento.php por falta de datos de sesión");
    header('Location: ../views/evento.php');
    exit;
}

// Manejar la solicitud AJAX para actualizar puntaje
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
    
    $sql = "SELECT puntaje, juego_actual FROM jugadores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $jugadorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $puntajeActual = $row['puntaje'];
    $juegoActual = $row['juego_actual'];
    
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

$preguntas = [
    [
        'pregunta' => '¿Cuál es el único continente que no tiene reptiles nativos?',
        'opciones' => ['África', 'Antártida', 'Australia'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Cuál es el órgano más grande del cuerpo humano?',
        'opciones' => ['Hígado', 'Piel', 'Pulmones'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Qué animal tiene la mordida más fuerte del reino animal?',
        'opciones' => ['León', 'Cocodrilo', 'Tiburón blanco'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Qué animal es famoso por reírse y pertenece a la familia de los felinos?',
        'opciones' => ['Hiena', 'Leopardo', 'Gato montés'],
        'correcta' => 0
    ],
    [
        'pregunta' => '¿Cuál es el planeta más grande del sistema solar?',
        'opciones' => ['Saturno', 'Júpiter', 'Neptuno'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Quién pintó la famosa obra La última cena?',
        'opciones' => ['Leonardo da Vinci', 'Miguel Ángel', 'Rafael'],
        'correcta' => 0
    ],
    [
        'pregunta' => '¿Cuál es la capital de Australia?',
        'opciones' => ['Sídney', 'Canberra', 'Melbourne'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Cual es el continente mas grande del mundo?',
        'opciones' => ['Asia', 'América', 'África'],
        'correcta' => 0
    ],
    [
        'pregunta' => '¿Qué científico propuso la teoría de la relatividad?',
        'opciones' => ['Isaac Newton', 'Albert Einstein', 'Nikola Tesla'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Cuál es el río más largo del mundo?',
        'opciones' => ['Nilo', 'Amazonas', 'Yangtsé'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Cuál es el elemento químico más abundante en el universo?',
        'opciones' => ['Oxígeno', 'Hidrógeno', 'Carbono'],
        'correcta' => 1
    ],
    [
        'pregunta' => '¿Cuál es el país más grande del mundo en superficie?',
        'opciones' => ['China', 'Rusia', 'Canadá'],
        'correcta' => 1
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explosión</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Press Start 2P', cursive;
            background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        #game-container {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #ff3300;
            border-radius: 15px;
            padding: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 0 20px rgba(255, 51, 0, 0.3);
            text-align: center;
        }

        h1 {
            color: #ff3300;
            text-shadow: 0 0 10px rgba(255, 51, 0, 0.5);
            margin-bottom: 20px;
            font-size: 24px;
        }

        #timer {
            font-size: 36px;
            color: #ff3300;
            margin: 20px 0;
            text-shadow: 0 0 10px rgba(255, 51, 0, 0.5);
        }

        #pregunta {
            font-size: 18px;
            margin-bottom: 20px;
            color: #fff;
            line-height: 1.5;
        }

        .opcion-btn {
            background: linear-gradient(45deg, #ff3300, #ff6600);
            border: none;
            color: white;
            padding: 15px 25px;
            margin: 10px;
            border-radius: 10px;
            cursor: pointer;
            width: 80%;
            max-width: 300px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .opcion-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 51, 0, 0.5);
        }

        .opcion-btn:active {
            transform: scale(0.95);
        }

        #modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        #mosal-message{
            font-size: 12px;
        }
        #modal-content {
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid #ff3300;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            max-width: 80%;
            color: #fff;
        }

        #aceptar-btn {
            background: linear-gradient(45deg, #00ff00, #00cc00);
            border: none;
            color: white;
            padding: 15px 30px;
            margin-top: 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        #aceptar-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
        }

        @media (max-width: 768px) {
            #game-container {
                padding: 15px;
            }

            h1 {
                font-size: 20px;
            }

            #timer {
                font-size: 28px;
            }

            #pregunta {
                font-size: 14px;
            }

            .opcion-btn {
                padding: 10px 20px;
                font-size: 12px;
            }
        }

        .bomb-container {
            position: relative;
            width: 100px;
            height: 150px;
            margin: 20px auto;
        }

        .bomb {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 60px;
            background: #000;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(255, 51, 0, 0.5);
        }

        .fuse {
            position: absolute;
            bottom: 50px;
            left: 50%;
            width: 4px;
            background: #8B4513;
            transform-origin: bottom;
            transform: translateX(-50%);
            transition: height 0.3s ease;
        }

        .spark {
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 10px;
            height: 10px;
            background: #ff3300;
            border-radius: 50%;
            box-shadow: 0 0 10px #ff3300;
            animation: spark 0.5s infinite alternate;
        }

        @keyframes spark {
            from {
                transform: translateX(-50%) scale(1);
                opacity: 1;
            }
            to {
                transform: translateX(-50%) scale(1.5);
                opacity: 0.5;
            }
        }

        #modal-title {
            margin-bottom: 20px;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div id="game-container">
        <h1>EXPLOSIÓN</h1>
        <div class="bomb-container">
            <div class="fuse">
                <div class="spark"></div>
            </div>
            <div class="bomb"></div>
        </div>
        <div id="timer">60</div>
        <div id="pregunta"></div>
        <div id="opciones"></div>
    </div>

    <div id="modal">
        <div id="modal-content">
            <p><h2 id="modal-title"></h2>
            <p id="modal-mensaje"></p>
            <button id="aceptar-btn" onclick="window.location.href='../views/evento.php'">Aceptar</button>
        </div>
    </div>

    <script>
        const preguntas = <?php echo json_encode($preguntas); ?>;
        let preguntaActual = 0;
        let tiempo = 60;
        let temporizador;

        function mostrarPregunta() {
            const pregunta = preguntas[preguntaActual];
            document.getElementById('pregunta').textContent = pregunta.pregunta;
            
            const opcionesDiv = document.getElementById('opciones');
            opcionesDiv.innerHTML = '';
            
            pregunta.opciones.forEach((opcion, index) => {
                const button = document.createElement('button');
                button.className = 'opcion-btn';
                button.textContent = opcion;
                button.onclick = () => verificarRespuesta(index);
                opcionesDiv.appendChild(button);
            });
        }

        function verificarRespuesta(respuestaIndex) {
            const pregunta = preguntas[preguntaActual];
            
            if (respuestaIndex === pregunta.correcta) {
                tiempo += 15; // Aumentar 15 segundos por respuesta correcta
                actualizarTimer();
                
                preguntaActual++;
                if (preguntaActual >= preguntas.length) {
                    finalizarJuego(true);
                } else {
                    mostrarPregunta();
                }
            } else {
                tiempo -= 10; // Restar 10 segundos por respuesta incorrecta
                if (tiempo <= 0) {
                    finalizarJuego(false);
                } else {
                    actualizarTimer();
                }
            }
        }

        function actualizarTimer() {
            document.getElementById('timer').textContent = Math.max(0, tiempo);
            actualizarMecha();
        }

        function actualizarMecha() {
            const maxHeight = 80; // altura máxima de la mecha en píxeles
            const minHeight = 10; // altura mínima de la mecha
            const tiempoMaximo = 60; // tiempo máximo del juego
            
            // Calcular altura proporcional al tiempo restante
            const altura = Math.max(minHeight, (tiempo / tiempoMaximo) * maxHeight);
            
            const mecha = document.querySelector('.fuse');
            mecha.style.height = `${altura}px`;
        }

        function finalizarJuego(victoria) {
            clearInterval(temporizador);
            const modalTitle = document.getElementById('modal-title');
            
            if (victoria) {
                // Calcular puntos basados en el tiempo restante
                const puntos = Math.ceil(tiempo / 10);
                modalTitle.textContent = '¡Felicitaciones!';
                
                fetch(window.location.href, {
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
                        document.getElementById('modal-mensaje').innerHTML = 
                            `¡Has escapado!<br>
                            Puntos ganados: ${puntos}<br>
                            Puntaje total: ${data.nuevoPuntaje}`;
                    } else {
                        document.getElementById('modal-mensaje').innerHTML = 
                            `¡Has escapado!<br>
                            Error al actualizar puntaje.`;
                    }
                    document.getElementById('modal').style.display = 'flex';
                });
            } else {
                modalTitle.textContent = ''; // Sin título para mensaje de derrota
                document.getElementById('modal-mensaje').textContent = 'El tiempo se ha acabado';
                document.getElementById('modal').style.display = 'flex';
            }
        }

        // Iniciar el juego
        window.onload = function() {
            mostrarPregunta();
            actualizarMecha(); // Inicializar la mecha
            temporizador = setInterval(() => {
                tiempo--;
                actualizarTimer();
                if (tiempo <= 0) {
                    finalizarJuego(false);
                }
            }, 1000);
        };
    </script>
</body>
</html>