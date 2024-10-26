<?php 
    require_once '../controllers/eventoController.php';
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andrade Ángel">
    <title>Evento</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="evento-forms text-center">
            <div id="evento-container" class="card p-4 bg-dark text-light">
                <h2 class="text-light">Evento: <?= $_SESSION['evento_nombre'] ?></h2>
                <h2 class="text-light">Temática: <?= $tematica ?></h2>                
                <h2 class="text-light">Jugador: <?= $_SESSION['jugador_actual']['nombres'] ?></h2>
                <div class="mt-4">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($juegos as $juego): ?>
                        <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-success btn-sm" onclick="window.location.href='<?= $juego['direccion'] ?>?juego_id=<?= $juego['id'] ?>&descripcion=<?= urlencode($juego['descripcion']) ?>'">
                                <i class="fas fa-play"></i>
                            </button>
                            <span><img src="../images/key.png" alt="Imagen de llave"> <?= $juego['nombre'] ?></span>
                            <span>
                                <img src="../images/padlock-closed.png" alt="Candado cerrado" class="img-fluid">
                                <img src="../images/padlock-open.png" alt="Candado abierto" class="img-fluid">
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <form id="evento-form" class="mt-4" method="POST" action="">
                    <button type="button" onclick="mostrarTablaPosiciones()" class="btn btn-primary btn-block btn-lg mt-2">Mostrar tabla de posiciones</button>
                    <button type="button" onclick="confirmarAbandonar()" class="btn btn-secondary btn-block btn-lg mt-2">Abandonar Evento</button>
                </form>
            </div>  
        </div>
    </div>

    <!-- Modal de tabla de posiciones -->
    <div class="modal fade" id="modalTablaPosiciones" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tabla de posiciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            <th>Nombre</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPosicionesBody" class="top-group">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarTablaPosiciones() {
            $('#modalTablaPosiciones').modal('show');
            var tabla = $('#tablaPosicionesBody');
            tabla.empty();
            var jugadores = <?= json_encode($_SESSION['jugadores']) ?>;
            jugadores.sort(function(a, b) {
                return b.puntaje - a.puntaje; // Ordenar de mayor a menor según puntaje
            });
            $.each(jugadores, function(index, jugador) {
                tabla.append('<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + jugador.nombres + '</td>' +
                    '<td><img src="../images/key.png" alt="Imagen de llave" class="img-fluid"> ' + jugador.puntaje + '</td>' +
                    '</tr>');
            });
        }

        function confirmarAbandonar() {
            if (confirm("¿Estás seguro de abandonar el evento?")) {
                window.location.href = '../index.html';
                // Agregamos un parámetro para evitar que se guarde la página en el historial del navegador
                window.location.replace('/', '_self');
            }
        }
    </script>
</body>
</html>
