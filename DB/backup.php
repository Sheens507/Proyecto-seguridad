<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db = "BDHotel1";
$backupFile = "backup/BDHotel1" . $db . "_" . date("Y-m-d_H-i-s") . ".sql";

// Comando de exportación
$command = "mysqldump -h $host -u $user -p$pass $db > $backupFile";

// Ejecutar el comando
system($command, $output);

if ($output === 0) {
    echo "Respaldo realizado con éxito: $backupFile";
} else {
    echo "Error al realizar el respaldo.";
}
?>
