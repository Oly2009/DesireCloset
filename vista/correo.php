<?php
session_start();
require_once '../config/conexion.php';
require '../vendor/autoload.php'; // Asegúrate de que esta ruta es correcta

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'olydaw2022@gmail.com'; // Tu correo de Gmail
        $mail->Password = 'mihaila1612A*'; // Tu contraseña de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatarios
        $mail->setFrom('olydaw2022@gmail.com', 'DesireCloset');
        $mail->addAddress($to);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}

if (!isset($_SESSION['user_id'])) {
    echo "Debes iniciar sesión para realizar una compra.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Producto no encontrado.";
    exit();
}

$productId = $_GET['id'];
$buyerId = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

try {
    // Obtener detalles del producto y vendedor
    $query = "SELECT p.nombreProducto, p.precio, u.email as vendedorEmail, u.nombreUsuario as vendedorNombre
              FROM Productos p
              JOIN Usuarios u ON p.idUsuario = u.idUsuario
              WHERE p.idProducto = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Producto no encontrado.";
        exit();
    }

    // Obtener email del comprador
    $buyerQuery = "SELECT email, nombreUsuario FROM Usuarios WHERE idUsuario = ?";
    $buyerStmt = $db->prepare($buyerQuery);
    $buyerStmt->execute([$buyerId]);
    $buyer = $buyerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$buyer) {
        echo "Comprador no encontrado.";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Preparar datos para el correo
        $to = $product['vendedorEmail'];
        $subject = "Solicitud de compra para " . $product['nombreProducto'];
        $message = "Hola " . $product['vendedorNombre'] . ",<br><br>"
                 . "El usuario " . $buyer['nombreUsuario'] . " (" . $buyer['email'] . ") está interesado en comprar tu producto: <strong>"
                 . $product['nombreProducto'] . "</strong> por <strong>€" . $product['precio'] . "</strong>.<br><br>"
                 . "Por favor, ponte en contacto con el comprador para coordinar la compra.<br><br>"
                 . "Saludos,<br>DesireCloset";

        // Enviar el correo
        $resultado = enviarEmail($to, $subject, $message);

        if ($resultado === true) {
            echo "Se ha enviado un correo al vendedor. Pronto se pondrá en contacto contigo.";
        } else {
            echo $resultado;
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php include '../includes/header.php'; ?>

<div class="ver container mt-5">
    <h2 class="mb-4" style="text-transform: uppercase; font-weight: bold; text-align: center;">Confirmar Compra</h2>
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" style="font-weight: bold;"><?php echo htmlspecialchars($product['nombreProducto']); ?></h5>
                    <p class="card-text"><strong>Precio:</strong> €<?php echo htmlspecialchars($product['precio']); ?></p>
                    <p class="card-text"><strong>Vendedor:</strong> <?php echo htmlspecialchars($product['vendedorNombre']); ?></p>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="nombreComprador" class="form-label">Nombre del Comprador</label>
                            <input type="text" class="form-control" id="nombreComprador" value="<?php echo htmlspecialchars($buyer['nombreUsuario']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="emailComprador" class="form-label">Email del Comprador</label>
                            <input type="email" class="form-control" id="emailComprador" value="<?php echo htmlspecialchars($buyer['email']); ?>" disabled>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar Compra</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
