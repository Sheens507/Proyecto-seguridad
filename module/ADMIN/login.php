<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si los datos han sido enviados
    if (!isset($_POST['usuario']) || !isset($_POST['password'])) {
        exit('Acceso no autorizado.');
    }

    // Sanitizar los datos de entrada
    $usuario = filter_var($_POST['usuario'], FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

    // Validar el formato del correo electrónico
    if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Formato de correo inválido.'); window.location.href = 'login.html';</script>";
        exit();
    }

    // Crear el cuerpo de la solicitud
    $data = json_encode([
        "action" => "login",
        "usuario" => $usuario,
        "password" => $password
    ]);

    // Configurar cURL para la solicitud a la API
    $url = "http://localhost/Proyecto-seguridad/api/auth";
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    // Ejecutar la solicitud
    $response = curl_exec($ch);

    // Verificar errores de cURL
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        error_log("Error en la solicitud cURL: $error_msg");
        echo "<script>alert('Error interno al conectar con la API.'); window.location.href = 'login.html';</script>";
        exit();
    }

    curl_close($ch);

    // Procesar la respuesta
    if ($response !== false) {
        $decodedResponse = json_decode($response, true);

        // Validar la respuesta
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Respuesta inválida de la API: $response");
            echo "<script>alert('Error en la respuesta de la API.'); window.location.href = 'login.html';</script>";
            exit();
        }

        if (isset($decodedResponse['status']) && $decodedResponse['status'] === 'ok') {
            session_start();

            // Proteger la sesión
            session_regenerate_id(true);

            // Almacenar el token de forma segura
            $_SESSION['token'] = htmlspecialchars($decodedResponse['result']['token'], ENT_QUOTES, 'UTF-8');

            // Redirigir al usuario
            header("Location: validacion.php");
            exit();
        } else {
            print_r($decodedResponse);
            echo "<script>alert('No se pudo iniciar sesión: Datos incorrectos o error en la API.'); window.location.href = 'login.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Error al comunicarse con la API.'); window.location.href = 'login.html';</script>";
        exit();
    }
} else {
    echo "<script>alert('Método de solicitud no permitido.'); window.location.href = 'login.html';</script>";
    exit();
}
