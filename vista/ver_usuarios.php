<?php
require_once '../config/conexion.php';

$database = new Database();
$conn = $database->getConnection();

// Manejar la baja de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idUsuario']) && $_POST['action'] == 'bajaUsuario') {
    $idUsuario = $_POST['idUsuario'];
    $fechaBaja = date('Y-m-d'); // Fecha de baja actual

    try {
        $stmt = $conn->prepare("UPDATE Usuarios SET fechaBaja = ? WHERE idUsuario = ?");
        $stmt->execute([$fechaBaja, $idUsuario]);
        $success_message = "Usuario dado de baja con éxito.";
    } catch (Exception $e) {
        $error_message = "Error al dar de baja al usuario: " . $e->getMessage();
    }
}

// Obtener usuarios
$query = "SELECT u.idUsuario, u.nombreUsuario, u.email, u.sexo, u.fechaNacimiento, u.fechaRegistro, u.fechaBaja, u.descripcion, u.foto, r.nombreRol 
          FROM Usuarios u 
          JOIN Usuarios_Roles ur ON u.idUsuario = ur.idUsuario
          JOIN Roles r ON ur.idRol = r.idRol
          WHERE r.nombreRol = 'usuario'";
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
            <h2>Gestión de Usuarios</h2>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Usuario</th>
                        <th>Email</th>
                        <th>Sexo</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Fecha de Registro</th>
                        <th>Fecha de Baja</th>
                        <th>Descripción</th>
                        <th>Foto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['idUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombreUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['sexo']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['fechaNacimiento']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['fechaRegistro']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['fechaBaja']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['descripcion']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Foto de perfil" style="width: 50px; height: 50px;"></td>
                            <td>
                                <form method="post" onsubmit="return confirm('¿Estás seguro de que deseas dar de baja este usuario?');">
                                    <input type="hidden" name="idUsuario" value="<?php echo $usuario['idUsuario']; ?>">
                                    <input type="hidden" name="action" value="bajaUsuario">
                                    <button type="submit" class="btn btn-danger btn-sm">Dar de Baja</button>
                                </form>
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

<?php include '../includes/footer.php'; ?>

<script>
    // Toggle the side navigation
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
</script>
