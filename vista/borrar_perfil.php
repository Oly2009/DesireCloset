<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    require_once '../config/conexion.php';

    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Obtener ID del usuario
        $userId = $_SESSION['user_id'];

        // Establecer la fecha de baja
        $fechaBaja = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE Usuarios SET fechaBaja = ? WHERE idUsuario = ?");
        $stmt->execute([$fechaBaja, $userId]);

        // Confirmar transacción
        $conn->commit();

        // Cerrar sesión
        session_destroy();

        // Redirigir al inicio de sesión
        header('Location: ../vista/login.php?mensaje=Perfil dado de baja exitosamente.');
        exit();
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        echo "Error al dar de baja el perfil: " . $e->getMessage();
    }
}
?>

<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Borrar Perfil</h2>
    <div class="alert alert-danger text-center" role="alert">
        ¿Estás seguro de que deseas borrar tu perfil? Esta acción no se puede deshacer.
    </div>
    <div class="d-flex justify-content-center">
        <form action="../controlador/borrar_perfil_controlador.php" method="post">
            <input type="hidden" name="confirm_delete" value="yes">
            <button type="submit" class="btn btn-danger mx-2">Sí, borrar mi perfil</button>
            <a href="miperfil.php" class="btn btn-secondary mx-2">Cancelar</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
