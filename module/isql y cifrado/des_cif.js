// Agregar dinámicamente el script de CryptoJS al documento
(function loadCryptoJS() {
    const script = document.createElement('script');
    script.src = "https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.js";
    script.onload = function() {
        console.log("CryptoJS cargado exitosamente.");
    };
    script.onerror = function() {
        console.error("Error al cargar CryptoJS.");
    };
    document.head.appendChild(script);
})();

// Función para cifrar o descifrar el texto
function crypto_js(opcion) {
    // Obtener el valor del textarea
    const texto = document.getElementById("Texto").value;
    let generacion;
    const clave = "miClaveSecreta"; // Esta es la clave para cifrar y descifrar

    // Validar que el texto no esté vacío
    if (texto.trim() === "" || texto === null || texto === undefined || texto.length <= 4) {
        document.getElementById("resultado").textContent = "Campo vacio o muy corto";
        return;
    }

    // Procesar según la opción
    if (opcion === 1) {
        generacion = cifrarTexto(texto, clave);
    } else if (opcion === 2) {
        generacion = descifrarTexto(texto, clave);
    } else {
        document.getElementById("resultado").textContent = "Error: opción no válida";
        return;
    }

    // Mostrar el resultado en el párrafo
    if (opcion === 1) {
        document.getElementById("resultado").textContent = `Cifrado: ${generacion}`;
        return;
    } else if (opcion === 2) {
        document.getElementById("resultado").textContent = `Decifrado: ${generacion}`;
    }
}

// Función para cifrar el texto
function cifrarTexto(texto, clave) {
    // Cifrar el texto usando AES
    const textoCifrado = CryptoJS.AES.encrypt(texto, clave).toString();
    return textoCifrado;
}

// Función para descifrar el texto
function descifrarTexto(texto, clave) { 
    // Descifrar el texto usando AES
    const bytes = CryptoJS.AES.decrypt(texto, clave);
    const textoDescifrado = bytes.toString(CryptoJS.enc.Utf8);

    return textoDescifrado;
}
