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
                    alert('Error al obtener el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
                alert('Error al obtener el sprint');
            }
        });
    });

    // Código para eliminar sprint
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        if (confirm('¿Está seguro de que desea eliminar este sprint?')) {
            $.ajax({
                url: '../controllers/sprintsController.php',
                method: 'POST',
                data: {
                    accion: 'eliminar',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Sprint eliminado con éxito');
                        location.reload();
                    } else {
                        alert('Error al eliminar el sprint: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    alert('Error al eliminar el sprint: ' + error);
                }
            });
        }
    });

    // Manejar el clic en el icono de ordenamiento
    $('#sortNombre').on('click', function(e) {
        e.preventDefault();
        var currentSort = $(this).data('sort');
        var newSort = currentSort === 'asc' ? 'desc' : 'asc';
        $(this).data('sort', newSort);
        
        // Cambiar el icono
        $(this).find('i').removeClass('fa-sort fa-sort-up fa-sort-down')
               .addClass(newSort === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        
        loadSprints('nombre', newSort);
    });

    $('#crearSprintBtn').on('click', function() {
        var form = document.getElementById('crearSprintForm');
        if (form.checkValidity()) {
            var formData = new FormData(form);
            formData.append('accion', 'crear');

            $.ajax({
                url: 'sprints.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Sprint creado con éxito');
                        location.reload();
                    } else {
                        alert('Error al crear el sprint: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Ocurrió un error al crear el sprint');
                }
            });
        } else {
            alert('Por favor, complete todos los campos requeridos.');
        }
    });

    $('#editarSprintBtn').on('click', function() {
        var formData = new FormData($('#editarSprintForm')[0]);
        formData.append('accion', 'editar');

        $.ajax({
            url: 'sprints.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Sprint actualizado con éxito');
                    location.reload();
                } else {
                    alert('Error al actualizar el sprint: ' + response.message);
                }
            },
            error: function() {
                alert('Error al actualizar el sprint');
            }
        });
    });

    $('#saveNewSprintBtn').on('click', function() {
        var formData = $('#createSprintForm').serialize();
        formData += '&accion=crear';

        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Sprint creado con éxito');
                    $('#createSprintModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error al crear el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
                alert('Error al crear el sprint');
            }
        });
    });

    // Manejar el guardado de cambios
    $('#saveSprintBtn').on('click', function() {
        var formData = $('#editSprintForm').serialize();
        formData += '&accion=editar';

        $.ajax({
            url: '../controllers/sprintsController.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Sprint actualizado con éxito');
                    $('#sprintModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error al actualizar el sprint: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
                alert('Error al actualizar el sprint');
            }
        });
    });
});