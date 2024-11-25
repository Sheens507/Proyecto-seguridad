<?php
session_start(); // Iniciar la sesión para acceder al token guardado

// Verificar si el token está presente en la sesión
if (!isset($_SESSION['token']) || empty($_SESSION['token'])) {
    // Si no hay token, redirigir a login
    header("Location: login.html");
    exit();
}

// Obtener el token desde la sesión
$token = $_SESSION['token'];

// Datos de la API para validar el token
$url = "http://localhost/Proyecto-seguridad/api/auth";
$data = array(
    "action" => "validar_token",
    "token" => $token
);

// Inicializar cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json"
));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Ejecutar la solicitud
$response = curl_exec($ch);
curl_close($ch);

// Decodificar la respuesta
$responseData = json_decode($response, true);

// Verificar si la validación fue exitosa
if (isset($responseData['status']) && $responseData['status'] == 'ok') {
    // Token válido, redirigir a resume.html
    header("Location: resume.html");
    exit();
} else {
    // Token inválido o expirado, redirigir a login.html
    header("Location: login.html");
    exit();
}
?>
