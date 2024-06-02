<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$userId = $_SESSION['user_id'];

// Obtener información del usuario
$query = "SELECT * FROM Usuarios WHERE idUsuario = ?";
$stmt = $db->prepare($query);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'Usuario no encontrado.';
    exit();
}

include '../includes/header.php';
?>

<div class="info container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-dark text-white">
            <h2 class="mb-0">Información Personal</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <?php 
                    $profileImagePath = htmlspecialchars($user['foto']);
                    if (!empty($user['foto']) && file_exists($profileImagePath)): ?>
                        <img src="<?php echo $profileImagePath; ?>" alt="Foto de perfil" class="img-fluid rounded-circle mb-3 shadow" style="width: 150px; height: 150px;">
                    <?php else: ?>
                        <img src="../assets/uploads/default-profile.png" alt="Foto de perfil" class="img-fluid rounded-circle mb-3 shadow" style="width: 150px; height: 150px;">
                    <?php endif; ?>
                    <h4 class="text-danger"><?php echo htmlspecialchars($user['nombre']); ?></h4>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Nombre de Usuario</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['nombreUsuario']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['nombre']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Primer Apellido</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['apellidos1']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Segundo Apellido</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['apellidos2']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sexo</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['sexo']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['descripcion']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['fechaNacimiento']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Registro</label>
                        <p class="form-control"><?php echo htmlspecialchars($user['fechaRegistro']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
