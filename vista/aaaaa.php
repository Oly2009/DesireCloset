<?php
require_once '../config/conexion.php';

$database = new Database();
$db = $database->getConnection();

// Consulta para obtener todos los mensajes con los nombres de los emisores y receptores
$query = "SELECT m.idMensaje, m.idEmisor, ue.nombreUsuario as nombreEmisor, m.idReceptor, ur.nombreUsuario as nombreReceptor, m.idProducto, m.contenido, m.visto
          FROM mensajes m
          JOIN usuarios ue ON m.idEmisor = ue.idUsuario
          JOIN usuarios ur ON m.idReceptor = ur.idUsuario";
$stmt = $db->prepare($query);
$stmt->execute();
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los Mensajes</title>
    <link rel="shortcut icon" href="../assets/img/logo.jpg" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Todos los Mensajes</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Mensaje</th>
                <th>ID Emisor</th>
                <th>Nombre Emisor</th>
                <th>ID Receptor</th>
                <th>Nombre Receptor</th>
                <th>ID Producto</th>
                <th>Contenido</th>
                <th>Visto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mensajes as $mensaje): ?>
                <tr>
                    <td><?php echo htmlspecialchars($mensaje['idMensaje']); ?></td>
                    <td><?php echo htmlspecialchars($mensaje['idEmisor']); ?></td>
                    <td><?php echo htmlspecialchars($mensaje['nombreEmisor']); ?></td>
                    <td><?php echo htmlspecialchars($mensaje['idReceptor']); ?></td>
                    <td><?php echo htmlspecialchars($mensaje['nombreReceptor']); ?></td>
                    <td><?php echo htmlspecialchars($mensaje['idProducto']); ?></td>
                    <td><?php echo htmlspecialchars($mensaje['contenido']); ?></td>
                    <td><?php echo htmlspecialchars($mensaje['visto']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
