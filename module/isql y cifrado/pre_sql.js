// Función para validar el input y prevenir inyecciones SQL
function validarInput(usuario, contrasena, correo = "") {
    const regexSQL = /('|--|;|\/\*|\*\/|select|insert|delete|update|drop|union|or|and)/i;

    // Validar que al menos uno de los campos (usuario o correo) esté presente
    if (!usuario && !correo) {
        document.getElementById("mensaje").textContent = "Debe ingresar un nombre de usuario o un correo electrónico.";
        return false;
    }

    // Validar si el nombre de usuario contiene caracteres maliciosos (si se ingresó)
    if (usuario && regexSQL.test(usuario)) {
        document.getElementById("mensaje").textContent = "Nombre de usuario inválido. Posible intento de inyección SQL detectado.";
        return false;
    }

    // Validar si el correo electrónico contiene caracteres maliciosos (si se ingresó)
    if (correo && regexSQL.test(correo)) {
        document.getElementById("mensaje").textContent = "Correo electrónico inválido. Posible intento de inyección SQL detectado.";
        return false;
    }

    // Validar si la contraseña contiene caracteres maliciosos
    if (regexSQL.test(contrasena)) {
        document.getElementById("mensaje").textContent = "Contraseña inválida. Posible intento de inyección SQL detectado.";
        return false;
    }

    return true;
}

// Función para validar el correo electrónico
function validarEmail(email) {
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Expresión regular para validar formato de correo electrónico
    return regexEmail.test(email);
}

// Función para procesar el login
function procesarLogin() {
    event.preventDefault();

    const usuario = document.getElementById("usuario_I").value;
    const contrasena = document.getElementById("contrasena_I").value;

    // Validar que se ingrese al menos el usuario o el correo
    if (validarInput(usuario, contrasena)) {
        document.getElementById("mensaje").textContent = "Validación correcta. Enviando datos de login...";
        // Aquí iría el código para enviar el formulario al servidor
    } else {
        // document.getElementById("mensaje").textContent = "Validación fallida. No se enviarán los datos.";
    }
}