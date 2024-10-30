function showPopup() {
    document.getElementById("yappyPopup").style.display = "flex";
}

function processPayment() {
    const phoneInput = document.getElementById("phoneNumber").value;
    const phonePattern = /^6\d{3}-\d{4}$/; // Formato 6000-0000 y debe iniciar con 6

    if (phonePattern.test(phoneInput)) {
        alert("Pago aceptado");
        document.getElementById("yappyPopup").style.display = "none";
        // Simulación de redirección a la página principal después del pago
        window.location.href = "pagina_principal.html"; 
    } else {
        alert("Número de teléfono inválido. Debe comenzar con 6 y seguir el formato 6000-0000");
    }
}
