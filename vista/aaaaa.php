<?php
session_start();
require_once '../config/conexion.php';

// Verificar si el usuario es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Eliminar usuario si se ha enviado una solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idUsuario'])) {
    $idUsuario = $_POST['idUsuario'];

    // Verificar la conexión a la base de datos
    if ($conn) {
        try {
            // Iniciar una transacción
            $conn->beginTransaction();

            // Eliminar el usuario de las tablas dependientes
            $query = "DELETE FROM usuarios_roles WHERE idUsuario = :idUsuario";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':idUsuario', $idUsuario);
            $stmt->execute();

            $query = "DELETE FROM valoraciones WHERE idValorado = :idUsuario OR idValorador = :idUsuario";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':idUsuario', $idUsuario);
            $stmt->execute();

            $query = "DELETE FROM validaciondni WHERE idUsuario = :idUsuario";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':idUsuario', $idUsuario);
            $stmt->execute();

            // Eliminar el usuario
            $query = "DELETE FROM usuarios WHERE idUsuario = :idUsuario";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':idUsuario', $idUsuario);
            $stmt->execute();

            // Confirmar la transacción
            $conn->commit();

            // Mensaje de éxito
            $mensaje = "Usuario eliminado con éxito.";
        } catch (PDOException $e) {
            // Revertir la transacción en caso de error
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error: " . $e->getMessage());
            $error = "No se pudo eliminar el usuario.";
        }
    } else {
        error_log("Error: No se pudo establecer la conexión con la base de datos.");
        $error = "No se pudo establecer la conexión con la base de datos.";
    }
}

// Obtener todos los usuarios
if ($conn) {
    $query = "SELECT idUsuario, nombreUsuario, email, nombre, apellidos1, apellidos2 FROM usuarios";
    $stmt = $conn->prepare($query);

    if ($stmt->execute()) {
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $usuarios = [];
        error_log("Error: No se pudo ejecutar la consulta.");
    }
} else {
    $usuarios = [];
    error_log("Error: No se pudo establecer la conexión con la base de datos.");
}

include '../includes/header.php';
?>

<main class="container mt-5">
    <h2 class="text-center mb-4">Listado de Usuarios</h2>
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success text-center"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($usuarios)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de Usuario</th>
                    <th>Email</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['idUsuario'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombreUsuario'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars(($usuario['apellidos1'] ?? '') . ' ' . ($usuario['apellidos2'] ?? '')); ?></td>
                        <td>
                            <form action="aaaaa.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                <input type="hidden" name="idUsuario" value="<?php echo $usuario['idUsuario']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-danger">No se encontraron usuarios.</p>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
