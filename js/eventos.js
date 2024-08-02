$(document).ready(function() {
    $('.edit-btn').on('click', function() {
        var id = $(this).data('id');
        $.ajax({
            url: 'eventosController.php',
            method: 'POST',
            data: {
                id: id,
                accion: 'obtener'
            },
            success: function(response) {
                var evento = JSON.parse(response);
                $('#editarEventoId').val(evento.id);
                $('#editarNombre').val(evento.nombre);
                $('#editarCodigo').val(evento.codigo);
                $('#editarFechaInicio').val(evento.fechaInicio);
                $('#editarFechaFin').val(evento.fechaFin);
                $('#editarSprint').val(evento.sprint_id);
                $('#editarDescripcion').val(evento.descripcion);
            }
        });
    });

    $('#crearEventoBtn').on('click', function() {
        $('#crearEventoForm').append('<input type="hidden" name="accion" value="crear">');
        $('#crearEventoForm').submit();
    });

    $('#editarEventoBtn').on('click', function() {
        $('#editarEventoForm').append('<input type="hidden" name="accion" value="editar">');
        $('#editarEventoForm').submit();
    });

    $('.delete-btn').on('click', function() {
        var id = $(this).data('id');
        $('#eliminarEventoId').val(id);
    });

    $('#eliminarEventoBtn').on('click', function() {
        $('#eliminarEventoForm').append('<input type="hidden" name="accion" value="eliminar">');
        $('#eliminarEventoForm').submit();
    });
});
