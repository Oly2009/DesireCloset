<?php
require_once '../config/conexion.php';
require_once '../config/email.php'; // Asegúrate de que la ruta a email.php es correcta

$success_message = '';
$error_message = '';

// Procesar formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $emailUsuario = $_POST['email'];
    $action = $_POST['action'];

    $database = new Database();
    $conn = $database->getConnection();

    if ($action == 'validar') {
        $estado = 'validado';
        $mensaje = "Su verificación de DNI ha sido validada exitosamente.";
        $success_message = "La verificación del DNI ha sido validada exitosamente.";
        enviarEmail($emailUsuario, "DNI Validado", $mensaje);
    } else if ($action == 'rechazar') {
        $estado = 'rechazado';
        $mensaje = "Lo sentimos, su verificación de DNI ha sido rechazada. Por favor, vuelva a intentarlo.";
        $success_message = "La verificación del DNI ha sido rechazada.";
        enviarEmail($emailUsuario, "DNI Rechazado", $mensaje);

        // Dar de baja al usuario
        try {
            $stmt = $conn->prepare("UPDATE Usuarios SET fechaBaja = NOW() WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);
        } catch (Exception $e) {
            $error_message = "Error al dar de baja al usuario: " . $e->getMessage();
        }
    } else {
        $error_message = "Acción no válida.";
    }

    if (empty($error_message)) {
        try {
            $stmt = $conn->prepare("UPDATE ValidacionDNI SET estado = ?, fechaValidacion = NOW() WHERE idUsuario = ?");
            $stmt->execute([$estado, $idUsuario]);
        } catch (Exception $e) {
            $error_message = "Error al actualizar el estado: " . $e->getMessage();
        }
    }
}

$database = new Database();
$conn = $database->getConnection();

// Obtener usuarios con rol 2 (usuario) y sus estados de validación
$query = "SELECT u.idUsuario, u.nombreUsuario, u.email, u.fechaNacimiento, u.foto, v.dni AS fotoDNI, v.estado 
          FROM Usuarios u
          JOIN Usuarios_Roles ur ON u.idUsuario = ur.idUsuario
          JOIN ValidacionDNI v ON u.idUsuario = v.idUsuario
          WHERE ur.idRol = 2";
$stmt = $conn->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-white">DesireCloset Admin</div>
        <div class="list-group list-group-flush">
            <a href="admin.php" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
            <a href="ver_usuarios.php" class="list-group-item list-group-item-action bg-dark text-white">Usuarios</a>
            <a href="ver_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Productos</a>
            <a href="estadistica_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Estadística</a>
            <a href="verificar_dni.php" class="list-group-item list-group-item-action bg-dark text-white">Verificar DNI</a>
            <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-white">Cerrar Sesión</a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <button class="btn btn-primary" id="menu-toggle">Toggle Menu</button>
        </nav>

        <div class="container mt-5">
            <h2>Verificación de DNI</h2>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID Usuario</th>
                        <th>Nombre de Usuario</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Foto</th>
                        <th>Foto DNI</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['idUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombreUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['fechaNacimiento']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Foto de perfil" style="width: 50px; height: 50px;"></td>
                            <td><img src="<?php echo htmlspecialchars($usuario['fotoDNI']); ?>" alt="Foto del DNI" style="width: 100px; height: 50px;"></td>
                            <td style="color: <?php echo $usuario['estado'] == 'pendiente' ? 'red' : 'black'; ?>;">
                                <?php echo htmlspecialchars($usuario['estado']); ?>
                            </td>
                            <td>
                                <?php if ($usuario['estado'] == 'pendiente'): ?>
                                    <form method="post" action="verificar_dni.php">
                                        <input type="hidden" name="idUsuario" value="<?php echo $usuario['idUsuario']; ?>">
                                        <input type="hidden" name="email" value="<?php echo $usuario['email']; ?>">
                                        <button type="submit" name="action" value="validar" class="btn btn-success btn-sm">Validar</button>
                                        <button type="submit" name="action" value="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                                    </form>
                                <?php else: ?>
                                    No hay acciones disponibles
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<script>
    // Toggle the side navigation
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
</script>

<?php include '../includes/footer.php'; ?>
