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

        // Establecer la fecha de baja y pagado en falso
        $fechaBaja = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE usuarios SET fechaBaja = ?, pagado = 0 WHERE idUsuario = ?");
        $stmt->execute([$fechaBaja, $userId]);

        // Obtener todos los productos del usuario para eliminar sus dependencias
        $stmt = $conn->prepare("SELECT idProducto FROM productos WHERE idUsuario = ?");
        $stmt->execute([$userId]);
        $productos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Eliminar dependencias de cada producto del usuario
        foreach ($productos as $idProducto) {
            $stmt = $conn->prepare("DELETE FROM megusta WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            $stmt = $conn->prepare("DELETE FROM fotos WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            $stmt = $conn->prepare("DELETE FROM transacciones WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            $stmt = $conn->prepare("DELETE FROM mensajes WHERE idProducto = ?");
            $stmt->execute([$idProducto]);
        }

        // Eliminar productos del usuario
        $stmt = $conn->prepare("DELETE FROM productos WHERE idUsuario = ?");
        $stmt->execute([$userId]);

        // Eliminar dependencias del usuario en las tablas relacionadas
        $stmt = $conn->prepare("DELETE FROM fotos WHERE idUsuario = ?");
        $stmt->execute([$userId]);

        $stmt = $conn->prepare("DELETE FROM transacciones WHERE idComprador = ? OR idVendedor = ?");
        $stmt->execute([$userId, $userId]);

        $stmt = $conn->prepare("DELETE FROM mensajes WHERE idEmisor = ? OR idReceptor = ?");
        $stmt->execute([$userId, $userId]);

        $stmt = $conn->prepare("DELETE FROM valoraciones WHERE idValorado = ? OR idValorador = ?");
        $stmt->execute([$userId, $userId]);

        $stmt = $conn->prepare("DELETE FROM validaciondni WHERE idUsuario = ?");
        $stmt->execute([$userId]);

        $stmt = $conn->prepare("DELETE FROM megusta WHERE idUsuario = ?");
        $stmt->execute([$userId]);

        $stmt = $conn->prepare("DELETE FROM usuarios_roles WHERE idUsuario = ?");
        $stmt->execute([$userId]);

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
