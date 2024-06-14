<?php
session_start();
require_once '../config/conexion.php';

// Conectar a la base de datos
$database = new Database();
$conn = $database->getConnection();

// Obtener el nombre de la base de datos
$dbName = 'DesireCloset';

// Obtener todas las tablas de la base de datos
$tables = array();
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$sql = "-- Base de datos: `$dbName`\n\n";

foreach ($tables as $table) {
    $sql .= "-- Estructura de tabla para la tabla `$table`\n\n";
    $result = $conn->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch(PDO::FETCH_NUM);
    $sql .= $row[1] . ";\n\n";

    $sql .= "-- Volcado de datos para la tabla `$table`\n\n";
    $result = $conn->query("SELECT * FROM `$table`");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $sql .= "INSERT INTO `$table` VALUES (";
        $sql .= "'" . implode("','", array_map([$conn, 'quote'], $row)) . "'";
        $sql .= ");\n";
    }
    $sql .= "\n\n";
}

// Escribir el contenido SQL en un archivo
$file = fopen('backup.sql', 'w');
fwrite($file, $sql);
fclose($file);

// Forzar la descarga del archivo SQL
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=backup.sql');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize('backup.sql'));
readfile('backup.sql');
exit;
?>
