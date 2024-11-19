let reservationData = {};
const token = "d0fcbfc3d2abf89fcda41a892a1cf3bf";

function nextPage(pageNumber) {
    if (validatePage(pageNumber - 1)) {
        togglePages(pageNumber);
        if (pageNumber === 2) {
            collectReservationData();
            fetchAvailableRooms();
        } else if (pageNumber === 3) {
            generateSummary();
        }
    }
}

function togglePages(pageNumber) {
    const pages = document.querySelectorAll(".page");
    pages.forEach(page => page.style.display = "none");
    document.getElementById(`page${pageNumber}`).style.display = "block";
}

function collectReservationData() {
    reservationData.res_checkIn = document.getElementById("res_checkIn").value;
    reservationData.res_checkOut = document.getElementById("res_checkOut").value;
    reservationData.res_cant_adul = document.getElementById("res_cant_adul").value;
    reservationData.res_cant_ninos = document.getElementById("res_cant_ninos").value;
}

function validatePage(pageNumber) {
    let isValid = true;
    
    switch (pageNumber) {
        case 1:
            isValid = validatePage1();
            break;
        case 2:
            isValid = validatePage2();
            break;
        case 3:
            isValid = validatePage3();
            break;
    }

    return isValid;
}

function validatePage1() {
    const fields = [
        "res_checkIn", "res_checkOut", "res_cant_adul", "res_cant_ninos"
    ];
    if (fields.some(id => !document.getElementById(id).value)) {
        alert("Por favor, completa todos los campos obligatorios en esta página.");
        return false;
    }
    return true;
}

function validatePage2() {
    const selectedRoom = document.querySelector("input[name='selectedRoom']:checked");
    if (!selectedRoom) {
        alert("Por favor, selecciona una habitación para continuar.");
        return false;
    }
    return true;
}

function validatePage3() {
    const paymentMethod = document.querySelector("input[name='payment']:checked").value;
    let isValid = validatePaymentMethod(paymentMethod);
    if (!isValid) return false;

    const note = document.getElementById("note").value;
    if (note.length > 200) {
        alert("La nota no puede tener más de 200 caracteres.");
        return false;
    }

    const requiredFields = [
        "usu_nombre", "usu_apellido", "usu_pais", "usu_telefono", "usu_email"
    ];
    if (requiredFields.some(id => !document.getElementById(id).value)) {
        alert("Por favor, completa todos los campos obligatorios en esta página.");
        return false;
    }
    return true;
}

function validatePaymentMethod(paymentMethod) {
    if (paymentMethod === "tarjeta") {
        return validateCardPayment();
    }
    if (paymentMethod === "yappy") {
        return true;
    }
    alert("Por favor, selecciona un método de pago para continuar.");
    return false;
}

function validateCardPayment() {
    const cardNumber = document.getElementById("cardNumber").value;
    const cardHolder = document.getElementById("cardHolder").value;
    const expiration = document.getElementById("expiration").value;

    if (!cardNumber || !cardHolder || !expiration) {
        alert("Por favor, completa todos los campos de tarjeta.");
        return false;
    }
    if (!validateCardNumber(cardNumber)) {
        alert("El número de tarjeta no es válido.");
        return false;
    }
    if (expiration.length !== 5 || expiration.charAt(2) !== "/") {
        alert("La fecha de expiración no es válida.");
        return false;
    }
    const [month, year] = expiration.split("/");
    if (parseInt(month) < 1 || parseInt(month) > 12 || parseInt(year) < 23) {
        alert("La fecha de expiración no es válida.");
        return false;
    }
    return true;
}

function validateCardNumber(cardNumber) {
    cardNumber = cardNumber.replace(/\s+/g, '');
    if (!/^\d+$/.test(cardNumber)) {
        return false;
    }

    let sum = 0;
    let shouldDouble = false;
    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i));
        if (shouldDouble) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }
        sum += digit;
        shouldDouble = !shouldDouble;
    }
    return sum % 10 === 0;
}

function fetchAvailableRooms() {
    const url = `http://localhost/hoteles/api/reservacion`;
    const params = {
        action: "consulta",
        token: token,
        res_checkIn: reservationData.res_checkIn,
        res_checkOut: reservationData.res_checkOut,
        res_adult: reservationData.res_cant_adul,
        res_child: reservationData.res_cant_ninos
    };

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(params)
    })
    .then(response => response.json())
    .then(data => displayAvailableRooms(data))
    .catch(error => console.error("Error:", error));
}

function displayAvailableRooms(rooms) {
    const tableBody = document.getElementById("roomsTableBody");
    tableBody.innerHTML = "";
    
    const displayedRoomTypes = {};
    rooms.forEach(room => {
        if (!displayedRoomTypes[room.Tipo]) {
            displayedRoomTypes[room.Tipo] = true;
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${room["N° Habitación"]}</td>
                <td>${room.Tipo}</td>
                <td>${room.Precio}</td>
                <td>${room.Adultos}</td>
                <td>${room.Niños}</td>
                <td><input type="radio" name="selectedRoom" value="${room["N° Habitación"]}"></td>
            `;
            tableBody.appendChild(row);
        }
    });
}

function generateSummary() {
    const selectedRoom = document.querySelector("input[name='selectedRoom']:checked");
    const summary = `
        Fecha de Entrada: ${reservationData.res_checkIn} <br>
        Fecha de Salida: ${reservationData.res_checkOut} <br>
        Adultos: ${reservationData.res_cant_adul} <br>
        Niños: ${reservationData.res_cant_ninos} <br>
        Habitación Seleccionada: ${selectedRoom.value} <br>
    `;
    document.getElementById("reservationSummary").innerHTML = summary;
}

function submitForm() {
    if (validatePage(3)) {
        const expiration = document.getElementById("expiration").value;
        const [pago_mesven, pago_anoven] = expiration.split("/");

        const reservationDetails = {
            token: token,
            usu_nombre: document.getElementById("usu_nombre").value,
            usu_apellido: document.getElementById("usu_apellido").value,
            usu_pais: document.getElementById("usu_pais").value,
            usu_telefono: document.getElementById("usu_telefono").value,
            usu_email: document.getElementById("usu_email").value,
            tipo_pago: document.querySelector("input[name='payment']:checked").value,
            pago_titular: document.getElementById("cardHolder").value,
            pago_numtar: document.getElementById("cardNumber").value,
            pago_mesven: pago_mesven,
            pago_anoven: pago_anoven,
            res_checkIn: reservationData.res_checkIn,
            res_checkOut: reservationData.res_checkOut,
            res_cant_adul: reservationData.res_cant_adul,
            res_cant_ninos: reservationData.res_cant_ninos,
            res_estado: "Pagada",
            res_numRoom: document.querySelector("input[name='selectedRoom']:checked").value,
            res_nota: document.getElementById("note").value,
            res_total: "111.00"
        };

        const url = `http://localhost/hoteles/api/reservacion`;
        const params = {
            action: "insertar",
            ...reservationDetails
        };

        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(params)
        })
        .then(response => response.json())
        .then(data => {
            console.log("Reservacion ID:", data.result.reservacionId);
            alert("Reservación confirmada con éxito.");
            
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Hubo un error al procesar la solicitud. Intenta nuevamente.");
        });
    }
}
