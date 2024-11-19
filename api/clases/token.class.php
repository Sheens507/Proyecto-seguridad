<?php
require_once('conexion/conexion.php');

class token extends conexion{
    public function actualizarToken($fecha){
        // $query = "UPDATE usuarios_token SET estado = 'Inactivo' WHERE fecha < '$fecha' AND estado = 'Activo'";
        $query = "UPDATE usuarios_token SET estado = 'Inactivo' WHERE fecha < ? AND estado = 'Activo'";
        $params = array($fecha);
        $verificar = parent::nonQuery($query, $params);
        if($verificar > 0){
            return 1;
        }else{
            return 0;
        }
    }
}

?>