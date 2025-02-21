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

// Obtener el ID del juego actual desde evento.php
$juego_id = $_GET['juego_id'] ?? null;

// Obtener la descripción del juego desde la base de datos (tabla "juegos", columna "descripcion")
$sql = "SELECT descripcion FROM juegos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $juego_id);
$stmt->execute();
$result = $stmt->get_result();
$descripcion = $result->fetch_assoc()['descripcion'] ?? 'Descripción no disponible';

// Obtener puntaje actual del jugador de la BD, se asume que evento.php ya estableció la sesión del jugador
$currentScore = 0;
if (isset($_SESSION['jugador_actual']['id'])) {
    $jugador_id = $_SESSION['jugador_actual']['id'];
    $sql2 = "SELECT puntaje FROM jugadores WHERE id = ?";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param("i", $jugador_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($row = $result2->fetch_assoc()) {
        $currentScore = $row['puntaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Color Rush</title>
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
        background-color: #333;
        color: #fff;
        text-align: center;
        padding: 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-sizing: border-box;
    }

    #game-container {
        height: 100%;
        max-width: 450px;
        margin: 0 auto;
        background: rgba(0,0,0,0.8);
        padding: 10px 20px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,255,255,0.5);
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-sizing: border-box;
    }

    /* Asegurarse de que todos los elementos usen border-box */
    * {
        box-sizing: border-box;
    }

    h1 {
      font-size: 22px;
      text-shadow: 0 0 10px #0ff;
      display: border-box;  
    }
    p{
      font-size: 10px;
    }
    #round-info, #click-info {
      font-size: 10px;
      margin: 5px 0;
    }
    #progress-bar {
      width: 100%;
      background-color: #444;
      border-radius: 5px;
      height: 4px;
      overflow: hidden;
    }
    #progress {
      height: 100%;
      width: 0%;
      background-color: #0ff;
      transition: width 0.5s;
    }
    .color-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 7px;
      margin: 10px 0;
      max-width: 420px;
    }
    .color-cell {
      position: relative;
      cursor: pointer;
      border-radius: 5px;
      width: 100%;
      padding-bottom: 100%;
      transition: opacity 0.3s ease, transform 0.3s ease;
      opacity: 1;
    }
    .color-cell-inner {
      position: absolute;
      top: 0;
      left: 0;
      right:0;
      bottom: 0;
      border-radius: 5px;
    }
    .color-cell.active {
      transform: scale(1.1);
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
      z-index: 1;
    }
    
    #start-button, .back-btn {
      font-size: 12px;
      padding: 10px 20px;
      margin: 0 5px;
      border: none;
    }
    
    .back-btn {
      background-color: #6c757d;
      color: white;
      border: none;
    }
    
    .back-btn:hover {
      background-color: #5a6268;
      color: white;
    }

    @media (max-height: 600px) {
        #game-container {
            padding: 5px 10px;
        }
        
        h1 {
            font-size: 18px;
            margin: 5px 0;
        }

        p, #round-info, #click-info {
            font-size: 8px;
            margin: 2px 0;
        }

        .color-grid {
            gap: 5px;
            margin: 5px 0;
        }

        #start-button, .back-btn {
            padding: 5px 10px;
            font-size: 10px;
        }
    }
    /* Modal de Resultado */
    #result-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width:100%;
      height:100%;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
      z-index: 999;
    }
    #modal-content {
      background: #222;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 20px #0ff;
      max-width: 90%;
    }
    #modal-content p {
      font-size: 18px;
    }
    #modal-content button {
      margin-top: 10px;
      font-family: 'Press Start 2P', cursive;
      font-size: 16px;
      padding: 10px 20px;
    }
  </style>
</head>
<body>
  <div id="game-container">
    <!-- Título con estilo llamativo -->
    <h1>Color Rush</h1>
    <!-- Descripción obtenida de la BD -->
    <p><?php echo $descripcion; ?></p>
    <!-- Mostrar "Ronda 0/5" inicialmente -->
    <div id="round-info">Ronda 0/5</div>
    <!-- Barra de progreso -->
    <div id="progress-bar">
      <div id="progress"></div>
    </div>
    <!-- Información de los colores presionados -->
    <div id="click-info">Colores presionados: 0/0</div>
    <!-- Cuadro de colores: Matriz 5x5 (25 celdas) -->
    <div class="color-grid">
      <?php
      // Definir 25 colores (se incluyen blanco y gris)
      $colores = [
          '#FF5733', '#FFBD33', '#33FF57', '#33FFBD', '#DBFF33',
          '#75FF33', '#33DBFF', '#3375FF', '#5733FF', '#BD33FF',
          '#FF33DB', '#E4FF33', '#33FF4D', '#FF8833', '#33E4FF',
          '#FFC133', '#FFFFFF', '#8FFF33', '#FF3333', '#808080',
          '#33FFC1', '#FF3375', '#3388FF', '#33FF88', '#C133FF'
      ];
      // Renderizar 25 celdas con los colores definidos
      foreach ($colores as $index => $color) {
          echo '<div class="color-cell" data-index="'.$index.'">
                  <div class="color-cell-inner" style="background-color: '.$color.';"></div>
                </div>';
      }
      ?>
    </div>
    <!-- Botón para iniciar el juego -->
    <div class="button-container">
      <button id="start-button" class="btn btn-success">Empezar</button>
      <button onclick="window.location.href='../views/evento.php'" class="btn back-btn">Volver</button>
    </div>
  </div>

  <!-- Modal de resultado (se mostrará al fallar o completar la última ronda) -->
  <div id="result-modal">
    <div id="modal-content">
      <p id="modal-text"></p>
      <button id="modal-accept" class="btn btn-primary">Aceptar</button>
    </div>
  </div>

  <script>
    // Variables de control del juego
    let round = 0;
    const totalRounds = 5;
    const colorsPerRound = [1, 3, 4, 6, 7]; // Cantidad correcta de colores por ronda
    let currentClicks = 0;
    let gameRunning = false;
    let currentPlayerScore = <?php echo $currentScore; ?>;
    
    let sequence = [];
    let playerSequence = [];
    let isShowingSequence = false;
    
    // Elementos de la página
    const roundInfo = document.getElementById("round-info");
    const clickInfo = document.getElementById("click-info");
    const progressBar = document.getElementById("progress");
    const startButton = document.getElementById("start-button");
    const cells = document.querySelectorAll(".color-cell");
    const modal = document.getElementById("result-modal");
    const modalText = document.getElementById("modal-text");
    const modalAccept = document.getElementById("modal-accept");
    
    // Al presionar "Empezar"
    startButton.addEventListener("click", () => {
      if (!gameRunning) {
        gameRunning = true;
        round = 0;
        currentClicks = 0;
        progressBar.style.width = "0%";
        updateRoundInfo();
        updateClickInfo();
        startRound();
      }
    });
    
    // Agregar listener a cada celda
    cells.forEach(cell => {
      cell.addEventListener("click", cellClicked);
    });
    
    // Actualiza el texto de la ronda
    function updateRoundInfo() {
      roundInfo.textContent = "Ronda " + round + "/" + totalRounds;
    }
    
    // Actualiza la cuenta de interacciones (colores presionados)
    function updateClickInfo() {
      const required = (round > 0 && round <= totalRounds) ? colorsPerRound[round - 1] : 0;
      clickInfo.textContent = "Colores presionados: " + currentClicks + "/" + required;
    }
    
    // Iniciar cada ronda
    function startRound() {
      round++;
      if (round > totalRounds) {
        endGame(true);
        return;
      }
      currentClicks = 0;
      playerSequence = [];
      updateRoundInfo();
      updateClickInfo();
      updateProgress();
      
      // Generar nueva secuencia para la ronda actual
      generateSequence();
      // Mostrar la secuencia
      showSequence();
    }
    
    // Modificar generateSequence para generar la cantidad correcta de colores por ronda
    function generateSequence() {
        // Limpiar la secuencia anterior
        sequence = [];
        // Generar la cantidad correcta de colores para esta ronda
        for(let i = 0; i < colorsPerRound[round - 1]; i++) {
            sequence.push(Math.floor(Math.random() * cells.length));
        }
    }
    
    // Modificar showSequence con animaciones más suaves
    function showSequence() {
        isShowingSequence = true;
        // Resetear todas las celdas
        cells.forEach(cell => {
            cell.style.opacity = "1";
            cell.classList.remove("active");
            cell.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        });
        
        let i = 0;
        const intervalId = setInterval(() => {
            // Opacar todas las celdas
            cells.forEach(cell => {
                cell.style.opacity = "0.2";
                cell.classList.remove("active");
            });
            
            // Iluminar la celda de la secuencia con animación suave
            const activeCell = cells[sequence[i]];
            activeCell.style.opacity = "1";
            activeCell.classList.add("active");
            
            // Después de 1 segundo, volver a estado normal
            setTimeout(() => {
                activeCell.classList.remove("active");
                cells.forEach(cell => {
                    cell.style.opacity = "1";
                });
            }, 1000);
            
            i++;
            if (i >= sequence.length) {
                clearInterval(intervalId);
                setTimeout(() => {
                    isShowingSequence = false;
                }, 1000);
            }
        }, 1500); // Aumentado a 1.5s para dar más tiempo entre colores
    }
    
    // Modificar cellClicked para agregar animación al hacer clic
    function cellClicked(e) {
        if (!gameRunning || isShowingSequence) return;
        
        const cell = e.currentTarget;
        const clickedIndex = parseInt(cell.dataset.index);
        
        // Agregar animación al hacer clic
        cell.classList.add("active");
        cells.forEach(c => {
            if (c !== cell) c.style.opacity = "0.2";
        });
        
        // Remover la animación después de 1 segundo
        setTimeout(() => {
            cell.classList.remove("active");
            cells.forEach(c => c.style.opacity = "1");
        }, 1000);
        
        // Agregar el color clickeado a la secuencia del jugador
        playerSequence.push(clickedIndex);
        
        // Verificar si el color clickeado es correcto
        if (clickedIndex !== sequence[playerSequence.length - 1]) {
            endGame(false);
            return;
        }
        
        currentClicks++;
        updateClickInfo();
        
        // Si completó la secuencia correctamente
        if (playerSequence.length === sequence.length) {
            setTimeout(() => {
                startRound();
            }, 1500);
        }
    }
    
    // Actualiza la barra de progreso, en función de las rondas completas
    function updateProgress() {
      let progressPercent = ((round) / totalRounds) * 100;
      progressBar.style.width = progressPercent + "%";
    }
    
    // Termina el juego y muestra el modal con el mensaje
    function endGame(success) {
      gameRunning = false;
      let pointsEarned;
      if(success) {
        // Si completa todas las rondas exitosamente, se otorgan 5 puntos.
        pointsEarned = 5;
      } else {
        // Si falla, la calificación es según la ronda fallida: (ronda - 1) puntos.
        pointsEarned = round - 1;
      }

      // Enviar puntuación al servidor
      fetch(window.location.href, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({
              action: 'actualizarPuntaje',
              puntos: pointsEarned
          })
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              modalText.innerHTML = `Has llegado a la ronda ${round}.<br>
                  Llaves conseguidas: ${pointsEarned}.<br>
                  Puntaje total: ${data.nuevoPuntaje}`;
          } else {
              modalText.innerHTML = `Error al actualizar puntaje.<br>
                  Has llegado a la ronda ${round}.<br>
                  Llaves conseguidas: ${pointsEarned}`;
          }
          modal.style.display = "flex";
      })
      .catch(error => {
          console.error('Error:', error);
          modalText.innerHTML = `Error al procesar el resultado.<br>
              Has llegado a la ronda ${round}.<br>
              Llaves conseguidas: ${pointsEarned}`;
          modal.style.display = "flex";
      });
    }
    
    // Al aceptar, se redirige nuevamente a evento.php para continuar con el siguiente juego
    modalAccept.addEventListener("click", () => {
      window.location.href = "../views/evento.php";
    });
  </script>
</body>
</html>