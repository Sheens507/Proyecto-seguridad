<?php
    require_once 'respuestas.class.php';

    class seguridad {
        public function rateLimit($ip, $limit = 100, $duration = 3600) {
            // Define el archivo para almacenar los datos de la IP
            $file = 'rate_limit.json';
            
            // Cargar los datos actuales si el archivo existe
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
            } else {
                $data = [];
            }
            // Limpiar datos antiguos
            $currentTime = time();
            foreach ($data as $key => $entry) {
                if ($currentTime - $entry['time'] > $duration) {
                    unset($data[$key]);
                }
            }
            // Verificar si la IP ya tiene un registro
            if (isset($data[$ip])) {
                // Si el límite ha sido alcanzado
                if ($data[$ip]['count'] >= $limit) {
                    return 0; // Bloquea la solicitud
                }
                // Incrementa el contador de solicitudes
                $data[$ip]['count']++;
            } else {
                // Nuevo registro para la IP
                $data[$ip] = ['count' => 1, 'time' => $currentTime];
            }
            // Guardar los datos actualizados
            file_put_contents($file, json_encode($data));
            return 1; // Permite la solicitud
        }
    }

    // cuando devuelve algo lo que sea tanto error como codigo 200 guardarlo junto al token y el ip

?>