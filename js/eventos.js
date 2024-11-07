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
    showConfirmationModal(id);
});

function showConfirmationModal(eventId) {
    showCustomMessage('Confirmación', '¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.', function() {
        // Si el usuario confirma, proceder a eliminar el evento
        $.ajax({
            url: 'eventos.php',
            method: 'POST',
            data: {
                id: eventId,
                accion: 'eliminar'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showCustomMessage('Éxito', 'Evento eliminado con éxito', () => {
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
                showCustomMessage('Error', 'Error al eliminar el evento: ' + error);
            }
        });
    }, function() {
        // Acción a realizar si el usuario cancela
        console.log('Eliminación cancelada');
    });
}

document.getElementById('crearEventoBtn').addEventListener('click', function() {
    var form = document.getElementById('crearEventoForm');
    if (form.checkValidity()) {
        var formData = new FormData(form);
        formData.append('accion', 'crear');
        
        fetch('eventos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showCustomMessage('Éxito', 'Evento creado con éxito', () => {
                    location.reload();
                });
            } else {
                showCustomMessage('Error', 'Error al crear el evento: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCustomMessage('Error', 'Ocurrió un error al crear el evento');
        });
    } else {
        showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
    }
});

$('#editarEventoBtn').on('click', function() {
    var form = $('#editarEventoForm')[0];
    if (form.checkValidity()) {
        var formData = new FormData(form);
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
                    showCustomMessage('Éxito', 'Evento actualizado con éxito', () => {
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', 'Error al actualizar el evento: ' + response.message);
                }
            },
            error: function() {
                showCustomMessage('Error', 'Error al actualizar el evento');
            }
        });
    } else {
        showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
    }
});

function showCustomMessage(title, message, confirmCallback, cancelCallback) {
    // Remover modal anterior si existe
    const existingModal = document.querySelector('.custom-modal');
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement('div');
    modal.className = 'custom-modal';
    modal.innerHTML = `
        <h3>${title}</h3>
        <div>${message}</div>
        <button onclick="closeCustomModal(this, true)">Aceptar</button>
        <button onclick="closeCustomModal(this, false)">Cancelar</button>
    `;
    document.body.appendChild(modal);

    window.closeCustomModal = function(button, isConfirmed) {
        button.parentElement.remove();
        if (isConfirmed && confirmCallback) {
            confirmCallback();
        } else if (!isConfirmed && cancelCallback) {
            cancelCallback();
        }
    };
}