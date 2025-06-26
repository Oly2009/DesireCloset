<?php
// --- CONFIGURACIÓN INICIAL ---
// Asegúrate de que las rutas a tus archivos son correctas
// Usaremos Composer para PHPMailer, que es la práctica estándar
require_once '../vendor/autoload.php';
require_once '../config/conexion.php'; // Tu archivo de conexión PDO para DesireCloset
include '../includes/header.php';     // Tu header de DesireCloset

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mensaje = '';
$tipoMensaje = ''; // 'success', 'error', 'warning'

// --- LÓGICA DEL FORMULARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'La dirección de correo electrónico no es válida.';
        $tipoMensaje = 'error';
    } else {
        $database = new Database();
        $conn = $database->getConnection();

        // 1. Verificar si el correo existe usando PDO
        $stmt = $conn->prepare("SELECT idUsuario, nombreUsuario FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // 2. Generar una nueva contraseña segura
            $nuevaPass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()'), 0, 12);
            
            // 3. Hashear la nueva contraseña (¡MÉTODO SEGURO!)
            $hashedPassword = password_hash($nuevaPass, PASSWORD_DEFAULT);
            
            // 4. Actualizar la contraseña en la base de datos
            $updateStmt = $conn->prepare("UPDATE usuarios SET password = :password WHERE idUsuario = :id");
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $usuario['idUsuario']);
            $updateStmt->execute();

            // 5. Enviar el email con la nueva contraseña
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->CharSet = 'UTF-8';

                // ===== LÓGICA PARA LOCAL Y HOSTING (COMO EN TU SCRIPT DE AGROSKY) =====
                if ($_SERVER['HTTP_HOST'] === 'localhost') {
                    // Configuración para entorno local (ej. MailHog)
                    $mail->Host = 'localhost';
                    $mail->Port = 1025;
                    $mail->SMTPAuth = false;
                } else {
                    // Configuración para producción (hosting real con Gmail)
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'olydaw2022@gmail.com'; // <<< TU EMAIL
                    $mail->Password   = 'xvmf misg ygyg dxwx';   // <<< TU CONTRASEÑA DE APLICACIÓN
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;
                }

                // Remitente y destinatario
                $mail->setFrom('no-reply@desirecloset.com', 'Soporte DesireCloset');
                $mail->addAddress($email, $usuario['nombreUsuario']);

                // Contenido del email adaptado a DesireCloset
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de Contraseña - DesireCloset';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                        <h2 style='color: #d9534f;'>Recuperación de Contraseña en DesireCloset</h2>
                        <p>Hola <strong>" . htmlspecialchars($usuario['nombreUsuario']) . "</strong>,</p>
                        <p>Hemos recibido una solicitud para restablecer tu contraseña. Tu nueva contraseña temporal es:</p>
                        <p style='background-color: #f2f2f2; border: 1px solid #ddd; padding: 10px; font-size: 18px; text-align: center; letter-spacing: 2px;'>
                            <strong>" . $nuevaPass . "</strong>
                        </p>
                        <p>Por favor, inicia sesión con esta nueva contraseña. Te recomendamos que la cambies por una de tu elección desde tu perfil una vez hayas accedido.</p>
                        <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                        <p>Atentamente,<br>El equipo de DesireCloset</p>
                    </div>
                ";

                $mail->send();
                $mensaje = '¡Revisa tu correo! Te hemos enviado una nueva contraseña para que puedas acceder.';
                $tipoMensaje = 'success';

            } catch (Exception $e) {
                // Para depuración, puedes descomentar la siguiente línea
                // $mensaje = 'No se pudo enviar el correo. Error: ' . $mail->ErrorInfo;
                $mensaje = 'No se pudo enviar el correo de recuperación. Por favor, inténtalo más tarde.';
                $tipoMensaje = 'error';
            }
        } else {
            // Por seguridad, no revelamos si el correo existe o no
            $mensaje = 'Si tu correo está registrado, recibirás una nueva contraseña en breve.';
            $tipoMensaje = 'info';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - DesireCloset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Estilos básicos para la página, adáptalos a tu style.css de DesireCloset */
        body { background-color: #f8f9fa; }
        .form-container { max-width: 500px; }
        .btn-custom-desire { background-color: #d9534f; border-color: #d9534f; color: white; }
        .btn-custom-desire:hover { background-color: #c9302c; border-color: #ac2925; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<main class="container flex-grow-1 py-5">
    <div class="form-container mx-auto p-4 p-md-5 border rounded bg-white shadow-sm">
        <h2 class="text-center mb-4" style="color: #d9534f;">Recuperar Contraseña</h2>
        <p class="text-center text-muted mb-4">Ingresa tu correo electrónico y te enviaremos una nueva contraseña para que puedas volver a acceder.</p>
        
        <form method="post" action="recuperar.php">
            <div class="mb-3">
                <label for="email" class="form-label fw-bold">Correo electrónico</label>
                <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="tuemail@ejemplo.com">
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-custom-desire btn-lg">Enviar</button>
                <a href="login.php" class="btn btn-secondary btn-lg">Volver al Login</a>
            </div>
        </form>
    </div>
</main>

<?php if (!empty($mensaje)): ?>
<script>
    (function() {
        Swal.fire({
            title: '<?php echo ($tipoMensaje === "success") ? "¡Éxito!" : "Aviso"; ?>',
            text: '<?php echo addslashes($mensaje); ?>',
            icon: '<?php echo $tipoMensaje; ?>',
            confirmButtonColor: '#d9534f'
        });
    })();
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

</body>
</html>