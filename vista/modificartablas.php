<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'];
    $roleId = $_POST['role_id'];

    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Verificar si el rol ya está asignado
        $query = "SELECT * FROM usuarios_roles WHERE idUsuario = ? AND idRol = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$userId, $roleId]);

        if ($stmt->rowCount() > 0) {
            $message = "El usuario ya tiene este rol asignado.";
            $messageClass = "alert-warning";
        } else {
            // Asignar el rol al usuario
            $query = "INSERT INTO usuarios_roles (idUsuario, idRol) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$userId, $roleId]);

            $message = "Rol asignado correctamente.";
            $messageClass = "alert-success";
        }
    } catch (Exception $e) {
        $message = "Error al asignar el rol: " . $e->getMessage();
        $messageClass = "alert-danger";
    }
}

// Obtener lista de usuarios
$database = new Database();
$conn = $database->getConnection();
$query = "SELECT idUsuario, email FROM usuarios";
$stmt = $conn->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de roles
$query = "SELECT idRol, nombreRol FROM roles";
$stmt = $conn->prepare($query);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Asignar Rol a Usuario</h2>
    <?php if (isset($message)): ?>
        <div class="alert <?php echo $messageClass; ?> text-center"><?php echo $message; ?></div>
    <?php endif; ?>
    <form action="modificartablas.php" method="post" class="needs-validation" novalidate>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="user_id" class="form-label">Usuario</label>
                    <select class="form-control" id="user_id" name="user_id" required>
                        <option value="" disabled selected>Seleccione un usuario</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?php echo $usuario['idUsuario']; ?>"><?php echo $usuario['idUsuario'] . ' - ' . $usuario['email']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, seleccione un usuario.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="role_id" class="form-label">Rol</label>
                    <select class="form-control" id="role_id" name="role_id" required>
                        <option value="" disabled selected>Seleccione un rol</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['idRol']; ?>"><?php echo $rol['idRol'] . ' - ' . $rol['nombreRol']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, seleccione un rol.</div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Asignar Rol</button>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
