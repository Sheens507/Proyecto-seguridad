function autoFormatPhone() {
    const input = document.getElementById("phoneNumber");
    let value = input.value.replace(/\D/g, ""); // Elimina caracteres no numéricos

    if (value.length > 4) {
        input.value = value.slice(0, 4) + "-" + value.slice(4, 8); // Formato 6000-0000
    } else {
        input.value = value;
    }
}

function processPayment() {
    const phoneInput = document.getElementById("phoneNumber").value;
    const phonePattern = /^6\d{3}-\d{4}$/; // Formato que empieza con 6

    if (phonePattern.test(phoneInput)) {
        // Crear un pop-up de confirmación
        alert("✅ Pago aceptado");
        // Redirigir a la página principal después de la confirmación
        window.location.href = "index.html";
    } else {
        alert("Número de teléfono inválido. Debe comenzar con 6 y seguir el formato 6000-0000");
    }
}
