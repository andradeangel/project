$(document).ready(function() {
    // Establecer fecha mínima para los inputs de fecha
    function setMinDates() {
        const now = new Date();
        // Formatear fecha actual a formato datetime-local (YYYY-MM-DDThh:mm)
        const formattedDate = now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0') + 'T' +
            String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0');

        // Establecer fecha mínima para el formulario de crear
        $('#fechaInicio').attr('min', formattedDate);
        $('#fechaFin').attr('min', formattedDate);

        // Establecer fecha mínima para el formulario de editar
        $('#editarFechaInicio').attr('min', formattedDate);
        $('#editarFechaFin').attr('min', formattedDate);
    }

    // Establecer fechas mínimas al cargar la página
    setMinDates();

    // Actualizar fechas mínimas cuando se abren los modales
    $('#crearEventoModal').on('show.bs.modal', setMinDates);
    $('#editarEventoModal').on('show.bs.modal', setMinDates);

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
                    $('#editarPersonas').val(evento.personas);
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
    var fechaInicio = new Date($('#fechaInicio').val());
    var fechaFin = new Date($('#fechaFin').val());
    var ahora = new Date();
    
    // Validar primero las fechas manuales
    if ($('#fechaInicio').val() && fechaInicio < ahora) {
        showCustomMessage('Advertencia', 'La fecha y hora no pueden ser antes de la fecha y hora actual');
        return;
    }

    if ($('#fechaFin').val() && fechaFin < ahora) {
        showCustomMessage('Advertencia', 'La fecha y hora no pueden ser antes de la fecha y hora actual');
        return;
    }

    // Luego validar el formulario completo
    if (!form.checkValidity()) {
        showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
        return;
    }
    
    if (fechaFin < fechaInicio) {
        showCustomMessage('Advertencia', 'La fecha de finalización no puede ser anterior a la fecha de inicio.');
        return;
    }

    if ($('#personas').val() < 2) {
        showCustomMessage('Advertencia', 'La capacidad de personas debe ser al menos 2');
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
    var fechaInicio = new Date($('#editarFechaInicio').val());
    var fechaFin = new Date($('#editarFechaFin').val());
    var ahora = new Date();
    
    // Validar primero las fechas manuales
    if ($('#editarFechaInicio').val() && fechaInicio < ahora) {
        showCustomMessage('Advertencia', 'La fecha y hora no pueden ser antes de la fecha y hora actual');
        return;
    }

    if ($('#editarFechaFin').val() && fechaFin < ahora) {
        showCustomMessage('Advertencia', 'La fecha y hora no pueden ser antes de la fecha y hora actual');
        return;
    }

    // Luego validar el formulario completo
    if (!form.checkValidity()) {
        showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
        return;
    }
    
    if (fechaFin < fechaInicio) {
        showCustomMessage('Advertencia', 'La fecha de finalización no puede ser anterior a la fecha de inicio.');
        return;
    }

    if ($('#editarPersonas').val() < 2) {
        showCustomMessage('Advertencia', 'La capacidad de personas debe ser al menos 2 y no puede ser un número negativo');
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
                    showCustomMessage('Información', response.message);
                    if (response.success) {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function() {
                    showCustomMessage('Error', 'No se puede eliminar este evento porque está siendo utilizado');
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