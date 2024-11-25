<?php
require_once 'clases/conexion/conexion.php';

$conexion = new Conexion();

// echo "Hola mundo";

$query = "INSERT INTO usuario(usua_nombre, usua_correo, usua_password, usua_roll, usua_estado) VALUES( ?, ?, ?, ?, ?)";
$name = "Pedro";
$email = "consultas@xxxx.com";
$password = "149f00036617f2c10c9457af10540bf1";
$estado = "Activo";
$roll = "1";

$params = array($name, $email, $password, $roll, $estado);
print_r($conexion->nonQueryId($query, $params));

?>