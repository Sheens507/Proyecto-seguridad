<?php
    require_once 'clases/auth.class.php';
    require_once 'clases/respuestas.class.php';
    require_once 'clases/seguridad.class.php';

    $_auth = new Auth;
    $_respuesta = new respuestas;
    $_seguridad = new seguridad;

    $ipUsuario = $_SERVER['REMOTE_ADDR'];
    $limite = $_seguridad->rateLimit($ipUsuario);
    if (!$limite) {
        // Si el límite ha sido alcanzado, muestra un mensaje de error
        http_response_code(429); // Código HTTP 429: Too Many Requests
        header('Content-Type: application/json');
        echo json_encode($_respuesta->error_429());
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        
        // Recibir datos
        $postBody = file_get_contents("php://input");
        // print_r($postBody);

        // Enviar datos al manejador
        $datosArray = $_auth->login($postBody);
        // print_r(json_encode($datosArray));

        // Devolver respuesta
        header('Content-Type: application/json');
        if(isset($datosArray['result']['error_id'])){
            $responseCode = $datosArray['result']['error_id'];
            http_response_code($responseCode);
        }else{
            http_response_code(200);
        }

        echo json_encode($datosArray);

    }else{
        header('Content-Type: application/json');
        $datosArray = $_respuesta->error_405();
        echo json_encode($datosArray);
    }

?>