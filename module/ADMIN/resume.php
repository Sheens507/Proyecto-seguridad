<?php
session_start();

// Verificar si existe el token en la sesión
if (!isset($_SESSION['token'])) {
    // Si no se ha iniciado sesión, redirigir a la página de login
    header("Location: login.html");
    exit();
}

// Llamada a la API para obtener las reservaciones
$apiUrl = "http://localhost/Proyecto-seguridad/api/auth";
$token = $_SESSION['token'];

$payload = json_encode([
    "action" => "reservaciones_list",
    "pagina" => 1
]);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        ],
        'content' => $payload
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);

// Verificar si la respuesta es válida
if ($response === false) {
    echo json_encode(['error' => 'Error al llamar a la API']);
    exit();
}

$data = json_decode($response, true);

// Retornar los datos como JSON
echo json_encode($data);

?>
