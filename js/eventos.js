$(document).ready(function() {
    // Usar delegación de eventos para los botones de editar y eliminar
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        $.ajax({
            url: 'eventos.php',
            method: 'POST',
            data: {
                id: id,
                accion: 'obtener'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var evento = response.data;
                    $('#editarEventoId').val(evento.id);
                    $('#editarNombre').val(evento.nombre);
                    $('#editarCodigo').val(evento.codigo);
                    $('#editarFechaInicio').val(evento.fechaInicio);
                    $('#editarFechaFin').val(evento.fechaFin);
                    $('#editarSprint').val(evento.idSprint);
                    $('#editarDescripcion').val(evento.descripcion);
                    $('#editarEventoModal').modal('show');
                } else {
                    alert('Error al obtener el evento: ' + response.message);
                }
            },
            error: function() {
                alert('Error al obtener el evento');
            }
        })
    });
});

$(document).on('click', '.delete-btn', function() {
    var id = $(this).data('id');
    if (confirm('¿Está seguro de que desea eliminar este evento?')) {
        $.ajax({
            url: 'eventos.php',
            method: 'POST',
            data: {
                id: id,
                accion: 'eliminar'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Evento eliminado con éxito');
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
                alert('Error al eliminar el evento: ' + error);
            }
        });
    }
});

$('#crearEventoBtn').on('click', function() {
    var form = document.getElementById('crearEventoForm');
    if (form.checkValidity()) {
        var fechaInicio = new Date(document.getElementById('fechaInicio').value);
        var fechaFin = new Date(document.getElementById('fechaFin').value);

        if (fechaFin < fechaInicio) {
            alert('La fecha de finalización no puede ser anterior a la fecha de inicio.');
            return;
        }

        var formData = new FormData(form);
        formData.append('accion', 'crear');

        $.ajax({
            url: 'eventos.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Evento creado con éxito');
                    location.reload();
                } else {
                    alert('Error al crear el evento: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Ocurrió un error al crear el evento');
            }
        });
    } else {
        alert('Por favor, complete todos los campos requeridos.');
    }
});

$('#editarEventoBtn').on('click', function() {
    var formData = new FormData($('#editarEventoForm')[0]);
    formData.append('accion', 'editar');
    $.ajax({
        url: 'eventos.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Evento actualizado con éxito');
                location.reload();
            } else {
                alert('Error al actualizar el evento: ' + response.message);
            }
        },
        error: function() {
            alert('Error al actualizar el evento');
        }
    });
});