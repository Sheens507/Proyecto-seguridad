<?php
session_start();

// Verificar si el token de sesión está disponible
if (!isset($_SESSION['token'])) {
    echo json_encode(['error' => 'Token no válido']);
    exit();
}

$apiUrl = "http://localhost/Proyecto-seguridad/api/auth";
$token = $_SESSION['token'];

// Datos para el logout
$logoutData = json_encode([
    "action" => "logout",
    "token" => $token
]);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            "Content-Type: application/json"
        ],
        'content' => $logoutData
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);

// Verificar si la respuesta es válida
if ($response === false) {
    echo json_encode(['error' => 'Error al hacer logout']);
    exit();
}

// Decodificar la respuesta de la API
$data = json_decode($response, true);

// Si el logout es exitoso, destruir la sesión
if (isset($data['status']) && $data['status'] === 'ok') {
    session_destroy(); // Cerrar sesión en PHP
    echo json_encode(['status' => 'ok', 'result' => []]);
} else {
    echo json_encode(['error' => 'Error al cerrar sesión']);
}
?>
