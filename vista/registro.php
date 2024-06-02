<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreUsuario = $_POST['nombreUsuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $nombre = $_POST['nombre'];
    $apellidos1 = $_POST['apellidos1'];
    $apellidos2 = $_POST['apellidos2'];
    $sexo = $_POST['sexo'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $descripcion = $_POST['descripcion'];
    $fotoPerfil = $_FILES['foto']['name'];
    $dniFoto = $_FILES['dni']['name'];
    $idRol = 2; // ID del rol "usuario"

    // Subir foto de perfil
    if (!empty($fotoPerfil)) {
        $fotoTempPerfil = $_FILES['foto']['tmp_name'];
        $hashedFotoPerfil = md5_file($fotoTempPerfil) . "_" . basename($fotoPerfil);
        $rutaFotoPerfil = "../assets/uploads/$hashedFotoPerfil";
        if (!move_uploaded_file($fotoTempPerfil, $rutaFotoPerfil)) {
            $error = "Error al subir la foto de perfil.";
            header("Location: ../vista/registro.php?error=" . urlencode($error));
            exit();
        }
    } else {
        $rutaFotoPerfil = null;
    }

    // Subir foto del DNI
    if (!empty($dniFoto)) {
        $fotoTempDNI = $_FILES['dni']['tmp_name'];
        $hashedFotoDNI = md5_file($fotoTempDNI) . "_" . basename($dniFoto);
        $rutaFotoDNI = "../assets/uploads/$hashedFotoDNI";
        if (!move_uploaded_file($fotoTempDNI, $rutaFotoDNI)) {
            $error = "Error al subir la foto del DNI.";
            header("Location: ../vista/registro.php?error=" . urlencode($error));
            exit();
        }
    } else {
        $rutaFotoDNI = null;
    }

    // Conectar a la base de datos
    try {
        $database = new Database();
        $conn = $database->getConnection();
    } catch (PDOException $e) {
        die("Error en la conexión: " . $e->getMessage());
    }

    try {
        // Verificar si el correo electrónico ya está registrado
        $stmt = $conn->prepare("SELECT idUsuario, fechaBaja FROM Usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            if (!is_null($usuario['fechaBaja'])) {
                // Usuario está dado de baja, reactivar la cuenta
                $stmt = $conn->prepare("UPDATE Usuarios SET nombreUsuario = ?, nombre = ?, apellidos1 = ?, apellidos2 = ?, password = ?, sexo = ?, fechaNacimiento = ?, descripcion = ?, foto = ?, fechaBaja = NULL WHERE idUsuario = ?");
                $stmt->execute([$nombreUsuario, $nombre, $apellidos1, $apellidos2, $password, $sexo, $fechaNacimiento, $descripcion, $rutaFotoPerfil, $usuario['idUsuario']]);

                // Actualizar el DNI en la tabla ValidacionDNI
                $estado = 'pendiente';
                $fechaValidacion = date('Y-m-d');
                $stmt = $conn->prepare("UPDATE ValidacionDNI SET dni = ?, estado = ?, fechaValidacion = ? WHERE idUsuario = ?");
                $stmt->execute([$rutaFotoDNI, $estado, $fechaValidacion, $usuario['idUsuario']]);
                
                // Redirigir a la página de pago
                $_SESSION['user_id'] = $usuario['idUsuario'];
                header('Location: pago.php');
                exit();
            } else {
                throw new Exception("El correo electrónico ya está registrado. Por favor, utiliza otro correo electrónico.");
            }
        } else {
            // Iniciar una transacción
            $conn->beginTransaction();

            // Insertar usuario en la tabla Usuarios
            $stmt = $conn->prepare("INSERT INTO Usuarios (nombreUsuario, nombre, apellidos1, apellidos2, email, password, sexo, fechaNacimiento, descripcion, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombreUsuario, $nombre, $apellidos1, $apellidos2, $email, $password, $sexo, $fechaNacimiento, $descripcion, $rutaFotoPerfil]);
            $idUsuario = $conn->lastInsertId();

            // Asignar rol al usuario
            $stmt = $conn->prepare("INSERT INTO Usuarios_Roles (idUsuario, idRol) VALUES (?, ?)");
            $stmt->execute([$idUsuario, $idRol]);

            // Insertar el DNI en la tabla ValidacionDNI
            $estado = 'pendiente';
            $fechaValidacion = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO ValidacionDNI (dni, estado, idUsuario, fechaValidacion) VALUES (?, ?, ?, ?)");
            $stmt->execute([$rutaFotoDNI, $estado, $idUsuario, $fechaValidacion]);

            // Confirmar la transacción
            $conn->commit();

            // Redirigir a la página de pago
            $_SESSION['user_id'] = $idUsuario;
            header('Location: pago.php');
            exit();
        }
    } catch (PDOException $e) {
        // En caso de error, revertir la transacción
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = "Error en el registro: " . $e->getMessage();
        header("Location: ../vista/registro.php?error=" . urlencode($error));
        exit();
    } catch (Exception $e) {
        $error = "Error en el registro: " . $e->getMessage();
        header("Location: ../vista/registro.php?error=" . urlencode($error));
        exit();
    }
}
?>

<?php include '../includes/header.php'; ?>

<section class="registro container py-5">
    <h2 class="text-center mb-4">REGÍSTRATE COMO NUEVO USUARIO</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center"><?php echo $_GET['error']; ?></div>
    <?php endif; ?>
    <form id="registrationForm" action="registro.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row">
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-3">DATOS DE CUENTA</h4>
                <div class="form-group mb-3">
                    <label for="nombreUsuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="nombreUsuario" name="nombreUsuario" required>
                    <div class="invalid-feedback">Por favor, ingrese su nombre de usuario.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Por favor, ingrese una contraseña.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label">Repetir Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div class="invalid-feedback">Por favor, confirme su contraseña.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Por favor, ingrese un correo electrónico válido.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                    <div class="invalid-feedback">Por favor, ingrese su nombre.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="apellidos1" class="form-label">Primer Apellido</label>
                    <input type="text" class="form-control" id="apellidos1" name="apellidos1" required>
                    <div class="invalid-feedback">Por favor, ingrese su primer apellido.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="apellidos2" class="form-label">Segundo Apellido</label>
                    <input type="text" class="form-control" id="apellidos2" name="apellidos2">
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">Acepto los <a href="#">términos y condiciones legales</a></label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="age" required>
                    <label class="form-check-label" for="age">Acepto que soy mayor de edad</label>
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="mb-3">DATOS PERFIL</h4>
                <div class="form-group mb-3">
                    <label for="foto" class="form-label">Foto de Perfil</label>
                    <input type="file" class="form-control" id="foto" name="foto" onchange="previewImage(event)">
                    <img id="fotoPreview" src="#" alt="Previsualización de Foto de Perfil" class="img-fluid rounded-circle mt-2" style="display: none; width: 150px; height: 150px;">
                </div>
                <div class="form-group mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Escríbenos un texto sobre ti, gustos y aficiones, saldrá publicado en tu perfil"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" required>
                    <div class="invalid-feedback">Por favor, ingrese su fecha de nacimiento.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select class="form-control" id="sexo" name="sexo" required>
                        <option value="" disabled selected>Seleccione su sexo</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                    </select>
                    <div class="invalid-feedback">Por favor, seleccione su sexo.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="dni" class="form-label">Foto de DNI</label>
                    <input type="file" class="form-control" id="dni" name="dni" required>
                    <div class="invalid-feedback">Por favor, suba una foto de su DNI.</div>
                </div>
                <button type="submit" class="btn btn-danger btn-block">REGISTRARSE</button>
            </div>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('fotoPreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
