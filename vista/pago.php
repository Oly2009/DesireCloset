<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

$database = new Database();
$conn = $database->getConnection();

// Obtener detalles del usuario
$query = "SELECT * FROM Usuarios WHERE idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'Usuario no encontrado.';
    exit();
}

// Simulación de pago completado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Actualizar el estado de pago del usuario
    $query = "UPDATE Usuarios SET pagado = 1 WHERE idUsuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);

    // Redirigir a la página de confirmación
    header('Location: confirmacion.php');
    exit();
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar Suscripción</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center mb-4">Pago de Suscripción</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Detalles de la Suscripción</h4>
                        <p><strong>Nombre de Usuario:</strong> <?php echo htmlspecialchars($user['nombreUsuario']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Precio:</strong> €106.00</p>
                        <form action="pago.php" method="post">
                            <button type="submit" class="btn btn-primary btn-block">Pagar Ahora</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>
