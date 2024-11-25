<?php
class Conexion{
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $conexion;

    function __construct(){
        $listadatos = $this->datosConexion();
        foreach($listadatos as $key => $value){
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->database = $value['database'];
            $this->port = $value['port'];
        }
        $this->conexion = new mysqli($this->server, $this->user, $this->password, $this->database, $this->port);
        if($this->conexion->connect_errno){
            echo "algo va mal con la conexion";
            die();
        }
    }

    private function datosConexion(){
        $direccion = dirname(__FILE__);
        $jsondata = file_get_contents($direccion . "/" . "config");
        return json_decode($jsondata, true);
    }

    private function convertirUTF8($array){
        array_walk_recursive($array, function(&$item, $key){
            if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
            }
        });
        return $array;
    }

    public function obtenerDatos($sqlstr, $params){
        // Preparar la consulta
        $stmt = $this->conexion->prepare($sqlstr);
        
        // Verificar si la consulta fue preparada correctamente
        if ($stmt === false) {
            // Si la preparación falla, devolver un error o manejarlo de alguna manera
            return 0;
        }
        // Enlazar los parámetros si se proporcionan
        if (!empty($params)) {
            // Enlazar los parámetros de forma dinámica
            $types = str_repeat('s', count($params)); // Asumir que todos los parámetros son cadenas
            $stmt->bind_param($types, ...$params);
        }
        // Ejecutar la consulta
        $stmt->execute();
        // Obtener el resultado de la consulta
        $result = $stmt->get_result();
        $resultArray = [];
        // Procesar los resultados
        while ($row = $result->fetch_assoc()) {
            $resultArray[] = $row;
        }
        // Cerrar el statement
        $stmt->close();
        // Retornar los resultados convertidos a UTF-8
        return $this->convertirUTF8($resultArray);
    }

    public function nonQuery($sqlstr, $params) {
        // Preparar la consulta
        $stmt = $this->conexion->prepare($sqlstr);
        
        if ($stmt === false) {
            return 0;
        }
        // Vincular los parámetros
        if (!empty($params)) {
            // Crear una cadena con los tipos de datos para bind_param
            $types = str_repeat("s", count($params)); // Suponiendo que todos los parámetros son strings
            $stmt->bind_param($types, ...$params);
        }
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener las filas afectadas
        $affectedRows = $stmt->affected_rows;
        
        // Cerrar la declaración
        $stmt->close();
        
        return $affectedRows;
    }

    public function nonQueryId($sqlstr, $params) {
        // Preparar la consulta
        $stmt = $this->conexion->prepare($sqlstr);
        
        if ($stmt === false) {
            return 0;
        }
        // Vincular los parámetros
        if (!empty($params)) {
            $types = str_repeat("s", count($params));
            $stmt->bind_param($types, ...$params);
        }
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener el ID de la última inserción si se insertaron filas
        $affectedRows = $stmt->affected_rows;
        $insertId = ($affectedRows >= 1) ? $this->conexion->insert_id : 0;
        
        // Cerrar la declaración
        $stmt->close();
        
        return $insertId;
    }

    protected function encriptar($string) {
        return md5($string);
    }

    public function antiSQL($datos) {
        // Verificar si la decodificación fue exitosa
        if ($datos === null && json_last_error() !== JSON_ERROR_NONE) {
            return 0; // JSON no válido
        }
        // Lista de patrones típicos de inyección SQL
        $patrones = [
            '/\bselect\b/i',                // SELECT
            '/\binsert\b/i',                // INSERT
            '/\bupdate\b/i',                // UPDATE
            '/\bdelete\b/i',                // DELETE
            '/\bdrop\b/i',                  // DROP
            '/\bunion\b/i',                 // UNION
            '/\bor\b/i',                    // OR
            '/\b--\b/',                     // Comentario SQL --
            '/\b#\b/',                      // Comentario SQL #
            '/\b;/',                        // Punto y coma ;
            '/\b\'\b/',                     // Comilla simple '
            '/\b\"/',                       // Comilla doble "
            
            // Nuevos patrones de inyección SQL
            '/\'\s*or\s*\'[01]\'\s*=\s*\'[01]\'/i',  // ' OR '1'='1'
            '/\'\s*or\s*1=1\s*--/i',                // ' OR 1=1 --
            '/\'\s*or\s*\'[a-z]\'=\s*\'[a-z]\'/i',  // ' OR 'x'='x'
            '/\bunion\b\s*select\s*.*--/i',         // UNION SELECT null, username, password FROM users --
            '/;\s*drop\s+table\s+.*--/i',           // ; DROP TABLE users --
            '/\bexists\b.*--/i',                    // OR EXISTS(SELECT * FROM users WHERE username='admin') --
            '/\bexec\b\s*.*xp_cmdshell/i',          // EXEC xp_cmdshell('net user malicious user /add') --
            '/\bshutdown\b\s*--/i',                 // SHUTDOWN --
            '/\bsleep\b\s*\(\d+\)/i',               // sleep(5)
            '/\bhaving\b\s+1=1/i',                  // 1=1 HAVING 1=1
            '/\bupdate\b\s+users\b.*--/i',          // UPDATE users SET password = 'malicious'
            '/\binsert\b\s+into\s+users\b.*--/i',   // INSERT INTO users (username, password) VALUES
            '/\bcreate\b\s+table\b/i',              // CREATE TABLE test (id INT, name VARCHAR(100)) --
            '/\border\s+by\s+1/i',                  // ORDER BY 1 --
            '/\bshow\s+databases\b/i',              // SHOW DATABASES --
            '/\bwaitfor\s+delay\b/i',               // WAITFOR DELAY '00:00:10'
            '/\btruncate\s+table\b/i',              // TRUNCATE TABLE users
            '/\bgrant\s+all\s+privileges\b/i',      // GRANT ALL PRIVILEGES TO 'attacker'
            '/\bexecute\s+immediate\b/i',           // EXECUTE IMMEDIATE 'DROP TABLE users'
            '/\bselect\b.*from\s+information_schema/i', // SELECT * FROM information_schema.tables
            '/\bselect\b.*from\s+sysdatabases/i',   // SELECT TOP 1 name FROM sysdatabases
        ];
        
        
    
        // Función auxiliar para verificar si un valor es seguro
        function esValorSeguro($valor, $patrones) {
            $valor = strtolower($valor); // Convertir a minúsculas para facilitar la detección
            foreach ($patrones as $patron) {
                if (preg_match($patron, $valor)) {
                    return false; // Inyección SQL detectada
                }
            }
            return true; // Valor seguro
        }
    
        // Recorrer cada valor en el array decodificado
        foreach ($datos as $key => $valor) {
            // Verificar si el valor es un array o un string
            if (is_array($valor)) {
                // Si es un array, hacer una llamada recursiva
                if (antiSQL(json_encode($valor)) === 0) {
                    return 0; // Inyección SQL detectada en un valor anidado
                }
            } elseif (!esValorSeguro($valor, $patrones)) {
                return 0; // Inyección SQL detectada en un valor
            }
        }
    
        return 1; // Todos los valores son seguros
    }

}


?>