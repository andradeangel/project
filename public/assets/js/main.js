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
    const surname = document.getElementById('surname').value;
    const age = document.getElementById('age').value;
    // Aquí puedes agregar la lógica para manejar los datos personales
    console.log('Nombre:', name);
    console.log('Apellido:', surname);
    console.log('Edad:', age);
    alert('Datos enviados correctamente.');
    // Redirigir a la página de juego o realizar otra acción
});


document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    // Aquí puedes agregar la lógica para manejar el inicio de sesión
    console.log('Correo:', email);
    console.log('Contraseña:', password);
    // Verificar las credenciales y redirigir según el tipo de usuario
    if (email && password) {
        alert('Inicio de sesión exitoso.');
        // Redirigir a la página de administración o gestión de juegos
        // window.location.href = 'admin.html'; // Ejemplo de redirección
    } else {
        alert('Por favor, ingresa tus credenciales.');
    }
});

function showLoginForm() {
    document.getElementById('login-container').classList.remove('d-none');
    document.getElementById('invitation-code-container').classList.add('d-none');
}