<?php
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $database = new Database();
    $conn = $database->getConnection();

    // Verificar si el correo electrónico existe en la base de datos
    $query = "SELECT idUsuario, nombreUsuario FROM Usuarios WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $idUsuario = $row['idUsuario'];
        $nombreUsuario = $row['nombreUsuario'];

        // Generar un token único
        $token = bin2hex(random_bytes(50));

        // Guardar el token en la sesión
        session_start();
        $_SESSION['token'] = $token;
        $_SESSION['idUsuario'] = $idUsuario;

        // Enviar el correo electrónico
        $resetLink = "http://yourdomain.com/vista/restablecer.php?token=" . $token;
        $subject = "Restablecimiento de Contraseña";
        $message = "<p>Hola $nombreUsuario,</p><p>Haga clic en el siguiente enlace para restablecer su contraseña: <a href='$resetLink'>Restablecer Contraseña</a></p>";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: DesireCloset <your-email@example.com>' . "\r\n";

        if (mail($email, $subject, $message, $headers)) {
            $mensaje = "Se ha enviado un correo electrónico con las instrucciones para restablecer su contraseña.";
        } else {
            $mensaje = "Hubo un error al enviar el correo electrónico. Inténtalo de nuevo más tarde.";
        }
    } else {
        $mensaje = "El correo electrónico no está registrado.";
    }

    header("Location: recuperar.php?mensaje=" . urlencode($mensaje));
    exit();
}
?>

<?php
include '../includes/header.php';
?>

<section class="recuperar container py-5">
    <h2 class="text-center mb-4">Recuperar Contraseña</h2>
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success text-center"><?php echo $_GET['mensaje']; ?></div>
    <?php endif; ?>
    <form id="recuperarForm" action="enviar.php" method="post" class="needs-validation" novalidate>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Por favor, ingrese su correo electrónico.</div>
                </div>
                <button type="submit" class="btn btn-danger btn-block">Enviar</button>
            </div>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>