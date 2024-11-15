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

// Crear evento
$('#crearEventoBtn').on('click', function() {
    var form = $('#crearEventoForm')[0];
    if (!form.checkValidity()) {
        showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
        return;
    }

    var fechaInicio = new Date($('#fechaInicio').val());
    var fechaFin = new Date($('#fechaFin').val());
    
    if (fechaFin < fechaInicio) {
        showCustomMessage('Advertencia', 'La fecha de finalización no puede ser anterior a la fecha de inicio.');
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
            $('#crearEventoModal').modal('hide');
            if (response.success) {
                showCustomMessage('Éxito', 'Evento creado con éxito');
            } else {
                showCustomMessage('Error', response.message);
            }
        },
        error: function() {
            $('#crearEventoModal').modal('hide');
            showCustomMessage('Error', 'Error al crear el evento');
        }
    });
});

// Editar evento
$('#editarEventoBtn').on('click', function() {
    var form = $('#editarEventoForm')[0];
    if (!form.checkValidity()) {
        showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
        return;
    }

    var fechaInicio = new Date($('#editarFechaInicio').val());
    var fechaFin = new Date($('#editarFechaFin').val());
    
    if (fechaFin < fechaInicio) {
        showCustomMessage('Advertencia', 'La fecha de finalización no puede ser anterior a la fecha de inicio.');
        return;
    }

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
            $('#editarEventoModal').modal('hide');
            if (response.success) {
                showCustomMessage('Éxito', 'Evento actualizado con éxito');
            } else {
                showCustomMessage('Error', response.message);
            }
        },
        error: function() {
            $('#editarEventoModal').modal('hide');
            showCustomMessage('Error', 'Error al actualizar el evento');
        }
    });
});

// Eliminar evento
function showConfirmationModal(eventId) {
    showCustomMessage(
        'Confirmación',
        '¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.',
        () => {
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
                        showCustomMessage('Éxito', 'Evento eliminado con éxito');
                    } else {
                        showCustomMessage('Error', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showCustomMessage('Error', 'Error al eliminar el evento: ' + error);
                }
            });
        }
    );
}

function showCustomMessage(title, message, confirmCallback, cancelCallback) {
    const modal = document.getElementById('customModal');
    const overlay = document.getElementById('modalOverlay');
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    
    // Limpiar botones existentes
    while (modal.querySelector('button')) {
        modal.querySelector('button').remove();
    }
    
    // Si hay callback de confirmación, mostrar botones Aceptar y Cancelar
    if (confirmCallback) {
        const confirmButton = document.createElement('button');
        confirmButton.textContent = 'Aceptar';
        confirmButton.onclick = () => {
            closeCustomModal();
            confirmCallback();
        };
        modal.appendChild(confirmButton);

        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'Cancelar';
        cancelButton.onclick = () => {
            closeCustomModal();
            if (cancelCallback) cancelCallback();
        };
        modal.appendChild(cancelButton);
    } else {
        // Solo botón Aceptar para mensajes informativos
        const acceptButton = document.createElement('button');
        acceptButton.textContent = 'Aceptar';
        acceptButton.onclick = closeCustomModal;
        modal.appendChild(acceptButton);
    }
    
    modal.style.display = 'block';
    overlay.style.display = 'block';
}

function closeCustomModal() {
    const modal = document.getElementById('customModal');
    const overlay = document.getElementById('modalOverlay');
    modal.style.display = 'none';
    overlay.style.display = 'none';
    if (document.getElementById('modalTitle').textContent === 'Éxito') {
        location.reload();
    }
}