<?php
require_once 'clases/respuestas.class.php';
require_once 'clases/reservacion.class.php';
require_once 'clases/seguridad.class.php';

$_respuestas = new respuestas;
$_reservacion = new reservacion;
$_seguridad = new seguridad;
$ipUsuario = $_SERVER['REMOTE_ADDR'];
$limite = $_seguridad->rateLimit($ipUsuario);
if (!$limite) {
    // Si el límite ha sido alcanzado, muestra un mensaje de error
    http_response_code(429); // Código HTTP 429: Too Many Requests
    header('Content-Type: application/json');
    echo json_encode($_respuestas->error_429());
    exit();
}


if($_SERVER['REQUEST_METHOD'] == 'GET'){
    if(isset($_GET['page'])){
        $pagina = $_GET['page'];
        $listaReservacion = $_reservacion->listaReservaciones($pagina);
        header('Content-Type: application/json');
        echo json_encode($listaReservacion);
        http_response_code(200);
    }elseif(isset($_GET['id'])){
        $reservacionId = $_GET['id'];
        $datosReservacion = $_reservacion->obtenerReservacion($reservacionId);
        header('Content-Type: application/json');
        echo json_encode($datosReservacion);
        http_response_code(200);
    }elseif(isset($_GET['consulta'])){
        // receive data
        $postBody = file_get_contents("php://input");
        // enviar datos al manejador
        $datosArray = $_reservacion->consulta($postBody);
        // devolver respuesta
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
        $datosArray = $_respuestas->error_400();
        echo json_encode($datosArray);
    }
    
}elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
    // // receive data
    // $postBody = file_get_contents("php://input");
    // // enviar datos al manejador
    // $datosArray = $_reservacion->post($postBody);
    // // devolver respuesta
    // header('Content-Type: application/json');
    // if(isset($datosArray['result']['error_id'])){
    //     $responseCode = $datosArray['result']['error_id'];
    //     http_response_code($responseCode);
    // }else{
    //     http_response_code(200);
    // }
    // echo json_encode($datosArray);
    // recibir datos
    $postBody = file_get_contents("php://input");
    $data = json_decode($postBody, true);

    // verificar si $data contiene una acción para consulta
    if (isset($data['action']) && $data['action'] === "consulta") {
        // enviar datos al manejador para consulta
        $datosArray = $_reservacion->consulta($postBody);
    } else {
        // enviar datos al manejador para creación o actualización
        $datosArray = $_reservacion->post($postBody);
    }

    // devolver respuesta en JSON
    header('Content-Type: application/json');
    if (isset($datosArray['result']['error_id'])) {
        $responseCode = $datosArray['result']['error_id'];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);


}elseif($_SERVER['REQUEST_METHOD'] == 'PUT'){
    // receive data
    $postBody = file_get_contents("php://input");
    // enviamos datos al manejador
    $datosArray = $_reservacion->put($postBody);
    // print_r($postBody);
    // devolver respuesta
    header('Content-Type: application/json');
    if(isset($datosArray['result']['error_id'])){
        $responseCode = $datosArray['result']['error_id'];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);
    
}elseif($_SERVER['REQUEST_METHOD'] == 'DELETE'){
    // receive data
    // debo verificar si los datos no son iSQL
    $postBody = file_get_contents("php://input");
    // enviamos datos al manejador
    $datosArray = $_reservacion->delete($postBody);
    // print_r($postBody);
    // devolver respuesta
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
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}

?>