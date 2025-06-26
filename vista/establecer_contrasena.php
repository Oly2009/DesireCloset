<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. VERIFICAR QUE EL USUARIO HA INICIADO SESIÓN
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/conexion.php';

$mensaje = '';
$tipoMensaje = ''; 
$updateExitoso = false; // <<<--- CAMBIO 1: Nueva variable para controlar el modal

// 2. PROCESAR EL FORMULARIO CUANDO SE ENVÍA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_SESSION['user_id'];
    $contrasenaActual = $_POST['contrasena_actual'];
    $nuevaContrasena = $_POST['nueva_contrasena'];
    $confirmarContrasena = $_POST['confirmar_contrasena'];

    if (empty($contrasenaActual) || empty($nuevaContrasena) || empty($confirmarContrasena)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipoMensaje = 'danger';
    } elseif ($nuevaContrasena !== $confirmarContrasena) {
        $mensaje = "La nueva contraseña y su confirmación no coinciden.";
        $tipoMensaje = 'danger';
    } else {
        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contrasenaActual, $usuario['password'])) {
            
            $hashedPassword = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
            
            $updateStmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE idUsuario = ?");
            if ($updateStmt->execute([$hashedPassword, $idUsuario])) {
                // <<<--- CAMBIO 2: Activamos la variable de éxito en lugar de poner el mensaje aquí
                $updateExitoso = true; 
            } else {
                $mensaje = "Hubo un error al actualizar la contraseña. Inténtalo de nuevo.";
                $tipoMensaje = 'danger';
            }

        } else {
            $mensaje = "La contraseña actual es incorrecta.";
            $tipoMensaje = 'danger';
        }
    }
}

include '../includes/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Cambiar Contraseña</h2>
                    
                    <?php if (!empty($mensaje) && !$updateExitoso): ?>
                        <div class="alert alert-<?php echo $tipoMensaje; ?> text-center">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>

                    <form action="establecer_contrasena.php" method="post" novalidate>
                        <div class="form-group mb-3">
                            <label for="contrasena_actual" class="form-label fw-bold">Contraseña Actual</label>
                            <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="nueva_contrasena" class="form-label fw-bold">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="confirmar_contrasena" class="form-label fw-bold">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg">Actualizar Contraseña</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<?php if ($updateExitoso): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Este código se ejecutará solo si la contraseña se cambió correctamente
    Swal.fire({
        title: '¡Éxito!',
        text: 'Tu contraseña ha sido actualizada correctamente.',
        icon: 'success',
        confirmButtonText: 'Salir',
        confirmButtonColor: '#d9534f' // Color rojo de tu tema
    }).then((result) => {
        // Si el usuario hace clic en el botón "Volver a mi Perfil"
        if (result.isConfirmed) {
            // Redirigir a la página de miperfil.php
            window.location.href = 'principal.php';
        }
    });
</script>
<?php endif; ?>