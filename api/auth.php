<?php
// Permitir solicitudes desde cualquier origen (esto puede ser más restrictivo según sea necesario)
header('Access-Control-Allow-Origin: *');
// Permitir los métodos HTTP necesarios
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
// Permitir los encabezados específicos
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once 'clases/auth.class.php';
require_once 'clases/respuestas.class.php';
require_once 'clases/seguridad.class.php';

// Instanciar las clases necesarias
$_auth = new Auth;
$_respuesta = new respuestas;
$_seguridad = new seguridad;

// Obtener la IP del usuario
$ipUsuario = $_SERVER['REMOTE_ADDR'];

// Aplicar limitador de solicitudes
$limite = $_seguridad->rateLimit($ipUsuario);
if (!$limite) {
    // Si se excede el límite, retornar error 429 (Too Many Requests)
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode($_respuesta->error_429());
    exit();
}

// Validar método de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener cuerpo de la solicitud
    $postBody = file_get_contents("php://input");

    // Verificar si la acción está definida
    $data = json_decode($postBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        header('Content-Type: application/json');
        http_response_code(400); // Error 400: Solicitud incorrecta
        echo json_encode($_respuesta->error_400());
        exit();
    }

    if (isset($data['action'])) {
        switch ($data['action']) {
            case "reservaciones_list":
                // Manejar acción para listar reservaciones
                $pagina = isset($data['pagina']) ? intval($data['pagina']) : 1;
                $listaReservacion = $_auth->listaReservaciones($pagina);
                // print_r($listaReservacion);
                header('Content-Type: application/json');
                if (isset($datosArray['result']['error_id'])) {
                    $responseCode = $datosArray['result']['error_id'];
                    http_response_code($responseCode);
                } else {
                    http_response_code(200);
                }
                echo json_encode($listaReservacion);
                break;

            case "reservaciones_id":
                // Acción para manejar reservaciones por ID (pendiente de implementar)
                header('Content-Type: application/json');
                http_response_code(501); // Error 501: No implementado
                echo json_encode(["status" => "error", "message" => "Acción no implementada."]);
                break;

            case "login":
                // Acción para login
                $datosArray = $_auth->login($postBody);
                header('Content-Type: application/json');
                if (isset($datosArray['result']['error_id'])) {
                    $responseCode = $datosArray['result']['error_id'];
                    http_response_code($responseCode);
                } else {
                    http_response_code(200);
                }
                echo json_encode($datosArray);
                break;

            case "logout":
                // Acción para logout
                $datosArray = $_auth->logout($postBody);
                header('Content-Type: application/json');
                if (isset($datosArray['result']['error_id'])) {
                    $responseCode = $datosArray['result']['error_id'];
                    http_response_code($responseCode);
                } else {
                    http_response_code(200);
                }
                echo json_encode($datosArray);
                break;

            case "validar_token":
                // Acción para validar token
                $datosArray = $_auth->validarToken($postBody);
                header('Content-Type: application/json');
                if (isset($datosArray['result']['error_id'])) {
                    $responseCode = $datosArray['result']['error_id'];
                    http_response_code($responseCode);
                } else {
                    http_response_code(200);
                }
                echo json_encode($datosArray);
                break;

            default:
                // Acción no reconocida
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode($_respuesta->error_400());
                break;
        }
    } else {
        // Acción no especificada en los datos
        header('Content-Type: application/json');
        http_response_code(400); // Error 400: Solicitud incorrecta
        echo json_encode($_respuesta->error_400());
    }
} else {
    // Manejar métodos no permitidos
    header('Content-Type: application/json');
    http_response_code(405); // Error 405: Método no permitido
    echo json_encode($_respuesta->error_405());
}

?>