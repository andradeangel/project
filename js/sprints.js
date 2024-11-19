$(document).ready(function() {
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: {
                accion: 'obtener',
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var sprint = response.data;
                    $('#sprintId').val(sprint.id);
                    $('#sprintNombre').val(sprint.nombre);
                    for (var i = 1; i <= 6; i++) {
                        $('#juego' + i).val(sprint['idJuego' + i]);
                    }
                    $('#sprintModal').modal('show');
                } else {
                    showCustomMessage('Error', 'Error al obtener el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
                showCustomMessage('Error', 'Error al obtener el sprint');
            }
        });
    });

    // Eliminar sprint
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).data('id');
        sprintToDelete = id;
        document.getElementById('confirmModal').style.display = 'block';
        return false;
    });

    // Manejar el clic en el enlace de ordenamiento
    $('.sort-btn').on('click', function(e) {
        // El ordenamiento ahora se maneja a través del href del enlace
        // No necesitamos prevenir el comportamiento por defecto
    });

    $('#crearSprintBtn').on('click', function() {
        var formData = $('#createSprintForm').serialize();
        if (!$('#sprintNombre').val() || !$('#juego1').val() || !$('#juego2').val() || !$('#juego3').val() || !$('#juego4').val() || !$('#juego5').val() || !$('#juego6').val()) {
            showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
            return;
        }
        formData += '&accion=crear';

        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showCustomMessage('Éxito', 'Sprint creado con éxito', () => {
                        $('#createSprintModal').modal('hide');
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', 'Error al crear el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al crear el sprint:', error);
                showCustomMessage('Error', 'Error al crear el sprint');
            }
        });
    });

    $('#saveSprintBtn').on('click', function() {
        var formData = $('#editSprintForm').serialize();
        if (!$('#sprintNombre').val() || !$('#juego1').val() || !$('#juego2').val() || !$('#juego3').val() || !$('#juego4').val() || !$('#juego5').val() || !$('#juego6').val()) {
            showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
            return;
        }
        formData += '&accion=editar';

        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showCustomMessage('Éxito', 'Sprint actualizado con éxito', () => {
                        $('#sprintModal').modal('hide');
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', 'Error al actualizar el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar el sprint:', error);
                showCustomMessage('Error', 'Error al actualizar el sprint');
            }
        });
    });

    $('#saveNewSprintBtn').on('click', function() {
        var formData = $('#createSprintForm').serialize();
        if (!$('#createSprintNombre').val() || !$('#juego1').val() || !$('#juego2').val() || !$('#juego3').val() || !$('#juego4').val() || !$('#juego5').val() || !$('#juego6').val()) {
            showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
            return;
        }
        formData += '&accion=crear';

        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showCustomMessage('Éxito', 'Sprint creado con éxito', () => {
                        $('#createSprintModal').modal('hide');
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', 'Error al crear el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al crear el sprint:', error);
                showCustomMessage('Error', 'Error al crear el sprint');
            }
        });
    });

    // Manejar el guardado de cambios
    $('#saveSprintBtn').on('click', function() {
        var formData = $('#editSprintForm').serialize();
        if (!$('#sprintNombre').val() || !$('#juego1').val() || !$('#juego2').val() || !$('#juego3').val() || !$('#juego4').val() || !$('#juego5').val() || !$('#juego6').val()) {
            showCustomMessage('Advertencia', 'Por favor, complete todos los campos requeridos.');
            return;
        }
        formData += '&accion=editar';

        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showCustomMessage('Éxito', 'Sprint actualizado con éxito', () => {
                        $('#sprintModal').modal('hide');
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', 'Error al actualizar el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar el sprint:', error);
                showCustomMessage('Error', 'Error al actualizar el sprint');
            }
        });
    });
});

// Asegurarse de que estas funciones estén disponibles globalmente
window.confirmDelete = function() {
    if (sprintToDelete) {
        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: {
                accion: 'eliminar',
                id: sprintToDelete
            },
            dataType: 'json',
            success: function(response) {
                document.getElementById('confirmModal').style.display = 'none';
                if (response.success) {
                    showCustomMessage('Éxito', 'Sprint eliminado con éxito', () => {
                        location.reload();
                    });
                } else {
                    showCustomMessage('Error', 'No se puede eliminar este sprint porque ya ha sido utilizado por un evento.');
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('confirmModal').style.display = 'none';
                showCustomMessage('Error', 'Error al eliminar el sprint: ' + error);
            }
        });
    }
};

window.closeConfirmModal = function() {
    document.getElementById('confirmModal').style.display = 'none';
    sprintToDelete = null;
};