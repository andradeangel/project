<?php
require_once("../database.php");
require_once("../models/consultasModel.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipoConsulta'])) {
    try {
        $tipoConsulta = $_POST['tipoConsulta'];
        $eventoId = !empty($_POST['evento']) ? $_POST['evento'] : null;
        
        $consultasModel = new ConsultasModel();
        $resultado = $consultasModel->ejecutarConsultaEspecifica($tipoConsulta, $eventoId);
        
        if (!empty($resultado)) {
            ?>
            <table class="table table-dark table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <?php foreach (array_keys($resultado[0]) as $columna): ?>
                            <th><?php echo htmlspecialchars($columna); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultado as $fila): ?>
                        <tr>
                            <?php foreach ($fila as $valor): ?>
                                <td><?php echo htmlspecialchars($valor); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        } else {
            echo "<div class='alert alert-info'>No se encontraron resultados para la consulta.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    exit;
}
?> 