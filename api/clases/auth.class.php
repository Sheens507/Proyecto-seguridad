<?php
    require_once 'conexion/conexion.php';
    require_once 'respuestas.class.php';


    class auth extends conexion{
        public function login($json){
            $_respuesta = new respuestas;
            $datos = json_decode($json, true);
            $isql = $this->antiSQL($datos);
            if(!$isql){
                return $_respuesta->error_422();
            }
            if(!isset($datos['usuario']) || !isset($datos["password"])){
                return $_respuesta->error_400();
            }else{
                //Todo esta bien
                $usuario = $datos['usuario'];
                $password = $datos['password'];
                $password = parent::encriptar($password);
                $datos  = $this->obtenerDatosUsuario($usuario);
                if($datos){
                    if($password == $datos[0]['usua_password']){
                        if($datos[0]['usua_estado'] == "Activo"){
                            $verificar = $this->insertarToken($datos[0]['id']);
                            if($verificar){
                                $result = $_respuesta->response;
                                $result["result"] = array(
                                    "token" => $verificar
                                );
                                return $result;
                            }else{
                                return $_respuesta->error_500("Error interno, por favor intente mas tarde");
                            }
                        }else{
                            return $_respuesta->error_200("El usuario esta inactivo");
                        }
                    }else{
                        return $_respuesta->error_200("La contraseña es incorrecta");
                    }
                }else{
                    return $_respuesta->error_200("El usuario $usuario no existe");
                }
            }
        }

        private function obtenerDatosUsuario($correo){
            $query = "SELECT id, usua_nombre, usua_password, usua_roll, usua_estado FROM usuario WHERE usua_correo = ?";
            $params = array($correo);
            $datos = parent::obtenerDatos($query, $params);
            if(isset($datos[0]['id'])){
                return $datos;
            }else{
                return 0;
            }
        }

        private function insertarToken($idusuario){
            $val = true;
            $token = bin2hex(openssl_random_pseudo_bytes(16, $val));
            $date = date("Y-m-d H:i");
            $estado = "Activo";
            $query = "INSERT INTO usuarios_token (usuario_id, token, estado, fecha) VALUES (?, ?, ?, '$date')";
            $params = array($idusuario, $token, $estado);
            $verificar = parent::nonQuery($query, $params);
            if($verificar){
                return $token;
            }else{
                return 0;
            }
        }

        public function logout($json) {
            $_respuesta = new respuestas;
            $datos = json_decode($json, true);
            $isql = $this->antiSQL($datos);
            if(!$isql){
                return $_respuesta->error_422();
            }
            if(!isset($datos['token'])){
                return $_respuesta->error_400();
            }else{
                // Verificar si el token existe
                $query = "SELECT usuario_id FROM usuarios_token WHERE token = ? AND estado = 'Activo'";
                $params = array($datos['token']);
                $resp = parent::obtenerDatos($query, $params);
        
                if ($resp) {
                    // Cambiar el estado a "Inactivo"
                    $usuarioId = $resp[0]['usuario_id'];
                    $updateQuery = "UPDATE usuarios_token SET estado = 'Inactivo' WHERE token = ?";
                    $updateParams = array($datos['token']);
                    $verificar = parent::nonQuery($updateQuery, $updateParams);
        
                    if ($verificar) {
                        return $_respuesta->response;
                    } else {
                        return $_respuesta->error_500("Error al cerrar sesión. Intente nuevamente.");
                    }
                } else {
                    return $_respuesta->error_200("Token inválido o ya inactivo.");
                }
            }
        }

        public function validarToken($json){
            $_respuesta = new respuestas;
            $datos = json_decode($json, true);
            $isql = $this->antiSQL($datos);
            if(!$isql){
                return $_respuesta->error_422();
            }
            if(!isset($datos['token'])){
                return $_respuesta->error_400();
            }else{
                $token = $datos['token'];
                $verificar = $this->verificarToken($token);
                if($verificar){
                    return $verificar;
                }else{
                    return $_respuesta->error_401("Token inválido o expirado.");
                }
            }
        }

        private function verificarToken($token){
            $_respuesta = new respuestas;
            // Consulta para verificar el token
            $query = "SELECT usuario_id FROM usuarios_token WHERE token = ? AND estado = 'Activo'";
            $params = array($token);
            $datos = parent::obtenerDatos($query, $params);

            if ($datos) {
                // Si el token existe y está activo, devolver el ID del usuario
                return array(
                    "status" => "ok",
                    "usuario_id" => $datos[0]['usuario_id']
                );
            } else {
                // Si el token no es válido o está inactivo
                return $_respuesta->error_401("Token inválido o expirado.");
            }
        }

        public function listaReservaciones($pagina = 1){
            $inicio = 0;
            $cantidad = 100;
            if($pagina > 1){
                $inicio = ($cantidad * ($pagina - 1)) + 1;
                $cantidad = $cantidad * $pagina;
            }
            // $query = "SELECT * FROM reservacion LIMIT ?, ?";
            $query = "SELECT r.*, c.Nombre_cli, c.Apellido_cli FROM reservacion r JOIN cliente c ON r.ID_cliente_FK = c.ID_cliente LIMIT ?, ?";
            $params = array($inicio, $cantidad);
            // print_r($query); // Debug: verificar la consulta
            $datos = parent::obtenerDatos($query, $params);
            return $datos;
        }
    }
?>