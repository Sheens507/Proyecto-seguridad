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

// Cargar la clave desde clave.json
let clave = null;

async function cargarClave() {
    try {
        const response = await fetch('../module/isql y cifrado/claves.json');
        if (!response.ok) throw new Error("Error al cargar la clave.");
        const data = await response.json();
        clave = data.clave;
        console.log("Clave cargada correctamente.");
    } catch (error) {
        console.error("Error al cargar la clave:", error);
    }
}

// Llama a cargarClave al cargar la página
cargarClave();

// Función para cifrar o descifrar el texto
function crypto_js(opcion, texto) {
    if (!clave) {
        // document.getElementById("resultado").textContent = "Clave no cargada. Intente nuevamente.";
        console.log("Clave no cargada. Intente nuevamente.");
        return;
    }

    let generacion;

    // Validar que el texto no esté vacío
    if (texto.trim() === "" || texto === null || texto === undefined || texto.length <= 4) {
        document.getElementById("resultado").textContent = "Campo vacío o muy corto";
        return;
    }

    // Procesar según la opción
    if (opcion === 1) {
        generacion = cifrarTexto(texto, clave);
        return generacion;
        // document.getElementById("resultado").textContent = `Cifrado: ${generacion}`;
        if (generacion) {
            return generacion;
        }
    } else if (opcion === 2) {
        generacion = descifrarTexto(texto, clave);
        // document.getElementById("resultado").textContent = `Descifrado: ${generacion}`;
    } else {
        // document.getElementById("resultado").textContent = "Error: opción no válida";
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

window.crypto_js = crypto_js;
