document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('invitation-code-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const invitationCode = document.getElementById('invitation-code').value;
        // Aquí puedes agregar la lógica para verificar el código de invitación
        if (invitationCode) {
            // Mostrar el formulario de datos personales
            document.getElementById('personal-data-container').classList.remove('d-none');
            // Ocultar el contenedor de código de invitación
            document.getElementById('invitation-code-container').classList.add('d-none');
            // Ocultar el formulario de inicio de sesión si está visible
            document.getElementById('login-container').classList.add('d-none');
        } else {
            alert('Por favor, ingresa un código de invitación válido.');
        }
    });

    document.getElementById('personal-data-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const name = document.getElementById('name').value;
        const age = document.getElementById('age').value;
        const gender = document.getElementById('gender').value;
        // Aquí puedes agregar la lógica para manejar los datos personales
        console.log('Nombre:', name);
        console.log('Edad:', age);
        console.log('Género:', gender);
        alert('Datos enviados correctamente.');
        // Redirigir a la página de juego o realizar otra acción
    });

    document.getElementById('login-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const id = document.getElementById('login-id').value;
        const password = document.getElementById('login-password').value;
        // Aquí puedes agregar la lógica para manejar el inicio de sesión
        console.log('CI:', id);
        console.log('Contraseña:', password);
        // Verificar las credenciales y redirigir según el tipo de usuario
        if (id && password) {
            alert('Inicio de sesión exitoso.');
            // Redirigir a la página de administración o gestión de juegos
            // window.location.href = 'admin.html'; // Ejemplo de redirección
        } else {
            alert('Por favor, ingresa tus credenciales.');
        }
    });

    window.showLoginForm = function() {
        document.getElementById('login-container').classList.remove('d-none');
        document.getElementById('invitation-code-container').classList.add('d-none');
    }

    window.goBack = function() {
        document.getElementById('login-container').classList.add('d-none');
        document.getElementById('personal-data-container').classList.add('d-none');
        document.getElementById('invitation-code-container').classList.remove('d-none');
    }
});
