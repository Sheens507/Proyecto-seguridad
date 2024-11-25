<?php
require_once 'respuestas.class.php';
require_once 'conexion/conexion.php';

class reservacion extends conexion{
    private $table = "reservacion";
    // Cambiar para los datos de la tabla
    private $id_cliente = "";
    private $nombre = "";
    private $apellido = "";
    private $pais = "";
    private $telefono = "";
    private $email = "";
    private $checkIn = "";
    private $checkOut = "";
    private $estado = "";
    private $num_adultos = "";
    private $num_ninos = "";
    private $total = "";
    private $token = "";
    private $pago_titular = "";
    private $pago_numtar = "";
    private $pago_mesven = "";
    private $pago_anoven = "";

    public function listaReservaciones($pagina = 1){
        $inicio = 0;
        $cantidad = 100;
        if($pagina > 1){
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT * FROM " . $this->table . " LIMIT ?, ?";
        $params = array($inicio, $cantidad);
        // print_r($query); // Debug: verificar la consulta
        $datos = parent::obtenerDatos($query, $params);
        return $datos;
    }

    public function obtenerReservacion($id){
        // Cambiar
        $query = "SELECT * FROM ". $this->table . " WHERE ID_reserv = ?";
        $params = array($id);
        return parent::obtenerDatos($query, $params);
    }

    public function post($json){
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        $isql = $this->antiSQL($datos);
        if(!$isql){
            return $_respuestas->error_422();
        }
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->checkToken($this->token);
            if($arrayToken){
                // Cambiar por datos requeridos / obligatorios
                if(!isset($datos['usu_nombre']) || !isset($datos['usu_apellido']) || !isset($datos['usu_pais']) 
                || !isset($datos['usu_telefono']) || !isset($datos['usu_email']) || !isset($datos['tipo_pago']) 
                || !isset($datos['res_checkIn']) || !isset($datos['res_checkOut']) || !isset($datos['res_cant_adul']) 
                || !isset($datos['res_cant_ninos']) || !isset($datos['res_total']) || !isset($datos['res_estado'])
                || !isset($datos['res_numRoom'])){
                    return $_respuestas->error_400();
                }else{
                    // datos de clientes
                    $this->nombre = $datos['usu_nombre'];
                    $this->apellido = $datos['usu_apellido'];
                    $this->pais = $datos['usu_pais'];
                    $this->telefono = $datos['usu_telefono'];
                    $this->email = $datos['usu_email'];
                
                    // datos de pago
                    if ($datos['tipo_pago'] == 'tarjeta') {
                        if (!isset($datos['pago_titular']) || !isset($datos['pago_numtar']) || 
                        !isset($datos['pago_mesven']) || !isset($datos['pago_anoven'])) {
                            return $_respuestas->error_400();
                        }
                        $this->titular = $datos['pago_titular'];
                        $this->numtar = openssl_encrypt($datos['pago_numtar'], "AES-256-CBC", "G4!d9x@L2#mQ", 0, "1234567890123456");
                        $this->mesven = $datos['pago_mesven'];
                        $this->anoven = $datos['pago_anoven'];
                        // hacer el llamado a la funcion de pago en efectivo
                        
                    } elseif ($datos['tipo_pago'] == 'yappy') {
                        $this->titular = "YAPPY";
                        // cambiar,preguntar a brit
                        $this->numtar = "9999999999";
                        $this->mesven = "00";
                        $this->anoven = "00";
                    }else{
                        return $_respuestas->error_400();
                    }
                    
                    // datos de la reservacion
                    $this->checkIn = $datos['res_checkIn'];
                    $this->checkOut = $datos['res_checkOut'];
                    $this->num_adultos = $datos['res_cant_adul'];
                    $this->num_ninos = $datos['res_cant_ninos'];
                    $this->total = $datos['res_total'];
                    $this->estado = $datos['res_estado'];
                    $this->numero_habitacion = $datos['res_numRoom'];
                    if(isset($datos['res_nota'])){ $this->nota = $datos['res_nota']; }
                    // verificar si el estado es valido
                    if ($this->estado != 'Pendiente' && $this->estado != 'Pagada') {
                        return $_respuestas->error_400();
                    }
                    $id_cliente = $this->insertarcliente();
                    if($id_cliente){
                        $this->id_cliente = $id_cliente;
                        $id_res = $this->insertarReservacion();
                        $resp_asignar = $this->asignar($id_res[0]);
                        if($id_res && $resp_asignar){
                            $resp = $this->addPago($id_res[0]);
                            if($resp){
                                $respuesta = $_respuestas->response;
                                $respuesta['result'] = array(
                                    "reservacionId" => $id_res[1]
                                );
                                return $respuesta;
                            }else{
                                return $_respuestas->error_500("Error interno del servidor. Se creo la reservacion pero no se pudo registrar el pago");
                            }
                        }else{
                            return $_respuestas->error_500();
                        }
                        // return $respuesta;
                    }else{
                        return $_respuestas->error_500();
                    }
                }
            }else{
                return $_respuestas->error_401("El token es invalido o ha expirado");
            }
        }
    }

    private function insertarcliente(){
        $query = "INSERT INTO Cliente (Nombre_cli, Apellido_cli, Pais_cli, Tel_cli, Email_cli) VALUES (?, ?, ?, ?, ?);";
        $nombre = $this->nombre;
        $apellido = $this->apellido;
        $pais = $this->pais;
        $telefono = $this->telefono;
        $email = $this->email;
        $params = array($nombre, $apellido, $pais, $telefono, $email);
        $resp = parent::nonQueryId($query, $params);

        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }
    private function formatDate($date){
        // Detectar si la fecha está en formato MM/DD/YYYY
        if (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $date)) {
            $dateParts = explode("/", $date);
            return "{$dateParts[2]}-{$dateParts[0]}-{$dateParts[1]}"; // Convertir a YYYY-MM-DD
        }
        
        // Si ya está en formato YYYY-MM-DD, devolverla sin cambios
        if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            return $date;
        }
        
        // Si el formato no es válido, devolver una fecha nula o manejar el error
        return "0000-00-00";
    }
    private function insertarReservacion(){
        $query = "INSERT INTO Reservacion (Num_reserv, Fecha_ent, Fecha_sal, Estado, Num_adult, Num_ninos, Precio, ID_cliente_FK, Comentarios)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";
        
        $num_reserva = rand(100000, 999999);
        // Transformar las fechas al formato YYYY-MM-DD si es necesario
        $checkIn = $this->formatDate($this->checkIn);
        $checkOut = $this->formatDate($this->checkOut);
        $estado = $this->estado;
        $num_adultos = $this->num_adultos;
        $num_ninos = $this->num_ninos;
        $total = $this->total;
        $id_cliente = $this->id_cliente;
        if(isset($this->nota)){ $nota = $this->nota; } else { $nota = NULL; }
        $params = array($num_reserva, $checkIn, $checkOut, $estado, $num_adultos, $num_ninos, $total, $id_cliente, $nota);
        $resp = parent::nonQueryId($query, $params);
        if($resp){
            return array($resp, $num_reserva);
        }else{
            return 0;
        }
    }

    private function addPago($id_reservacion){
        $query = "INSERT INTO Pago (Titular_tar, Num_tar, Mes_ven, Ano_ven, ID_reserva_FK)
	    VALUES ( ?, ?, ?, ?, ?);";
        $titular = $this->titular;
        $numtar = $this->numtar;
        $mesven = $this->mesven;
        $anoven = $this->anoven;
        $id_reserva = $id_reservacion;
        $params = array($titular, $numtar, $mesven, $anoven, $id_reserva);
        $resp = parent::nonQuery($query, $params);
        return $resp >= 1 ? $resp : 0;
    }

    private function asignar($id_reservacion){
        $query = "INSERT INTO Asignacion(ID_reserv_FK, ID_hab_FK)
        VALUES (?, ?);";
        $numero_habitacion = $this->numero_habitacion;
        $id_reserva = $id_reservacion;
        $params = array($id_reserva, $numero_habitacion);
        $resp = parent::nonQuery($query, $params);
        return $resp >= 1 ? $resp : 0;
    }

    // Mostrar y preguntar a los pelaos si lo pongo funcional o no
    public function put($json) {
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        $isql = $this->antiSQL($datos);
        if(!$isql){
            return $_respuestas->error_422();
        }
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->checkToken($this->token);
            if($arrayToken){
                // Validación de datos requeridos
                if (!isset($datos['res_id'])) {
                    return $_respuestas->error_400();
                } else {
                    $this->id = $this->validateInput($datos['res_id']);
                    
                    // Validar y asignar campos opcionales
                    if (isset($datos['res_checkIn'])) {
                        $this->checkIn = $this->validateDate($datos['res_checkIn']);
                        if ($this->checkIn === 0) {
                            return $_respuestas->error_400();
                        }
                    }
                    
                    if (isset($datos['res_checkOut'])) {
                        $this->checkOut = $this->validateDate($datos['res_checkOut']);
                        if ($this->checkOut === 0) {
                            return $_respuestas->error_400();
                        }
                    }
            
                    // Ejemplo de insertar datos que no son obligatorios
                    // if (isset($datos['res_xxx'])) { $this->res_xxx = $this->validateInput($datos['res_xxx']); }
            
                    $resp = $this->modificarReservacion();
                    if ($resp) {
                        $respuesta = $_respuestas->response;
                        $respuesta['result'] = array(
                            "reservacionId" => $this->id
                        );
                        return $respuesta;
                    } else {
                        return $_respuestas->error_500("No se ha podido modificar la reservación");
                    }
                }
            }else{
                return $_respuestas->error_401("El token es invalido o ha expirado");
            }
        }
    }
    
    // Método privado para validar y sanear la entrada
    private function validateInput($input) {
        // Convertir a string y eliminar caracteres peligrosos
        return htmlspecialchars(strip_tags(trim($input)));
    }
    
    // Método privado para validar que la fecha esté en formato YYYY-MM-DD
    private function validateDate($date) {
        // Verificar que el formato de la fecha sea válido
        if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) && strtotime($date)) {
            return $date; // Retorna la fecha válida
        }
        return 0;
    }
    

    private function modificarReservacion(){
        $query = "UPDATE " . $this->table . " SET ";
        $fields = [];
        $params = [];
    
        // Solo añadir los campos si están definidos (no se envían como "0000-00-00")
        if ($this->checkIn !== "0000-00-00") {
            $fields[] = "res_checkIn = ?";
            $params[] = $this->checkIn;
        }
        if ($this->checkOut !== "0000-00-00") {
            $fields[] = "res_checkOut = ?";
            $params[] = $this->checkOut;
        }
    
        // Unir los campos que se van a actualizar con comas
        $query .= implode(", ", $fields);
        $query .= " WHERE res_id = ?";
        $params[] = $this->id;
    
        // Ejecutar la consulta
        $resp = parent::nonQuery($query, $params);

        return $resp >= 1 ? $resp : 0;
    }
    
    public function delete($json){
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        $isql = $this->antiSQL($datos);
        if(!$isql){
            return $_respuestas->error_422();
        }
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->checkToken($this->token);
            if($arrayToken){
                // Cambiar por datos requeridos / obligatorios
                
                // Cambiar para verificar que sea un dato tipo YYYY-MM-DD
                if(!isset($datos['res_id']) ){
                    return $_respuestas->error_400();
                }else{
                    // Cambiar por los campos de la tabla
                    $this->id = $datos['res_id'];
                    $resp = $this->eliminarReservacion();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta['result'] = array(
                            // cambiar para mandar tambien id aleatorio de la reservacion
                            "reservacionId" => $this->id
                        );
                        return $respuesta;
                    }else{
                        return $_respuestas->error_500();
                    }
                }
            }else{
                return $_respuestas->error_401("El token es invalido o ha expirado");
            }
        }
    }

    private function eliminarReservacion(){
        $query = "DELETE FROM " . $this->table . " WHERE res_id = ? ";
        $params = array($this->id);
        $resp = parent::nonQuery($query, $params);
        return $resp >= 1 ? $resp : 0;
    }

    private function checkToken($token){
        $query = "SELECT usuario_id,token,estado FROM usuarios_token WHERE token = ? AND estado = 'Activo'";
        $params = array($token);
        $resp = parent::obtenerDatos($query, $params);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }
    
    // Esta funcion que??
    private function actualizarToken($idUsuario, $token){
        $date = date("Y-m-d H:i");
        $query = "UPDATE usuario_token SET fecha = ? WHERE token = ? ";
        $params = array($idUsuario, $token);
        $resp = parent::nonQuery($query, $params);
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }

    public function consulta($json){
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        $isql = $this->antiSQL($datos);
        if(!$isql){
            return $_respuestas->error_422();
        }
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->checkToken($this->token);
            if($arrayToken){
                // Cambiar por datos requeridos / obligatorios
                if(!isset($datos['res_checkIn']) || !isset($datos['res_checkOut']) || !isset($datos['res_adult']) || !isset($datos['res_child'])){
                    return $_respuestas->error_400();
                }else{
                    // Cambiar por los campos de la tabla
                    $this->checkIn = $datos['res_checkIn'];
                    $this->checkOut = $datos['res_checkOut'];
                    $this->adult = $datos['res_adult'];
                    $this->child = $datos['res_child'];
                    // Ejemplo de insertar datos que no son obligatorios
                    // if(isset($datos['res_xxx'])){ $this->res_xxx = $datos['res_xxx']; }
                    $resp = $this->callDisponibilidad();
                    return $resp;
                }
            }else{
                return $_respuestas->error_401("El token es invalido o ha expirado");
            }
        }
    }

    private function callDisponibilidad(){
        // cambiar para codigo aleatorio para id de la reservacion
        // cambiar por datos de la tabla
        $query = "CALL Disponibilidad(?, ?, ?, ?)";
        $checkIn = $this->checkIn;
        $checkOut = $this->checkOut;
        $adult = $this->adult;
        $child = $this->child;
        $params = array($checkIn, $checkOut, $adult, $child);
        return parent::obtenerDatos($query, $params);
    }
}

?>