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
    }

?>