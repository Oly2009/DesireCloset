<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_GET['id'])) {
    echo 'Producto no encontrado.';
    exit();
}

$idProducto = $_GET['id'];
$idUsuario = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

// Obtener detalles del producto y categoría
$query = "SELECT p.*, c.nombreCategoria
          FROM productos p
          JOIN categorias c ON p.idCategoria = c.idCategoria
          WHERE p.idProducto = ?";
$stmt = $db->prepare($query);
$stmt->execute([$idProducto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo 'Producto no encontrado.';
    exit();
}

// Obtener detalles del comprador
$queryUsuario = "SELECT nombre, apellidos1, apellidos2, email FROM usuarios WHERE idUsuario = ?";
$stmtUsuario = $db->prepare($queryUsuario);
$stmtUsuario->execute([$idUsuario]);
$comprador = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

// Determinar la página de categoría basada en el nombre de la categoría
$paginaCategoria = '';
switch ($producto['nombreCategoria']) {
    case 'Bragas y Tangas':
        $paginaCategoria = 'braga.php';
        break;
    case 'Sujetadores':
        $paginaCategoria = 'sujetadores.php';
        break;
    case 'Fotos de pies':
        $paginaCategoria = 'fotosdepie.php';
        break;
    case 'Juguetes sexuales':
        $paginaCategoria = 'juguetessexuales.php';
        break;
    default:
        $paginaCategoria = 'todos.php';
        break;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pagar'])) {
    try {
        $db->beginTransaction();

        // Aquí llamamos a la función que simula el pago con PayPal
        $resultadoPago = procesarPagoConPaypal($idUsuario, $idProducto, $producto['precio']);

        if ($resultadoPago == 1) {
            // Actualizar estado de la transacción
            $queryActualizar = "UPDATE transacciones SET idComprador = ?, estado = 'comprado', fechaTransaccion = CURDATE(), hora = CURTIME() WHERE idProducto = ? AND estado = 'enventa'";
            $stmtActualizar = $db->prepare($queryActualizar);
            $stmtActualizar->execute([$idUsuario, $idProducto]);

            // Establecer el producto como vendido
            $queryVendido = "UPDATE transacciones SET estado = 'vendido' WHERE idProducto = ? AND idVendedor = ?";
            $stmtVendido = $db->prepare($queryVendido);
            $stmtVendido->execute([$idProducto, $producto['idUsuario']]);

            $db->commit();
            $mensajeExito = "Producto comprado con éxito!";
        } else {
            $db->rollBack();
            $mensajeError = "El pago no se pudo realizar.";
        }
    } catch (Exception $e) {
        $db->rollBack();
        $mensajeError = "Error al actualizar el estado de la transacción: " . $e->getMessage();
    }
}

include '../includes/header.php';

// Función simulada para procesar el pago con PayPal
function procesarPagoConPaypal($idUsuario, $idProducto, $monto) {
    // Aquí se simula el resultado del pago. En una integración real, aquí se haría la llamada a la API de PayPal.
    // Por ahora, simplemente devolvemos 1 para simular un pago exitoso.
    return 1;
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Detalles del Producto y Pago</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    Información del Comprador
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($comprador['nombre'] . ' ' . $comprador['apellidos1'] . ' ' . $comprador['apellidos2']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($comprador['email']); ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Detalles del Producto
                </div>
                <div class="card-body">
                    <p><strong>Nombre del Producto:</strong> <?php echo htmlspecialchars($producto['nombreProducto']); ?></p>
                    <p><strong>Precio:</strong> €<?php echo htmlspecialchars($producto['precio']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    Información de Pago
                    <button type="button" class="btn-close" aria-label="Close" onclick="window.location.href='<?php echo $paginaCategoria; ?>'" style="position: absolute; right: 15px;"></button>
                </div>
                <div class="card-body">
                    <form method="post" id="formularioPago">
                        <div class="mb-3">
                            <label for="numeroTarjeta" class="form-label">Número de Tarjeta</label>
                            <input type="text" class="form-control" id="numeroTarjeta" name="numeroTarjeta" maxlength="19" placeholder="XXXX XXXX XXXX XXXX" required>
                            <div class="invalid-feedback">Por favor, ingrese un número de tarjeta válido (16 dígitos).</div>
                        </div>
                        <div class="mb-3">
                            <label for="nombreTarjeta" class="form-label">Nombre en la Tarjeta</label>
                            <input type="text" class="form-control" id="nombreTarjeta" name="nombreTarjeta" placeholder="Nombre Completo" required>
                            <div class="invalid-feedback">Por favor, ingrese el nombre tal como aparece en la tarjeta.</div>
                        </div>
                        <div class="mb-3">
                            <label for="fechaExpiracion" class="form-label">Fecha de Expiración</label>
                            <input type="text" class="form-control" id="fechaExpiracion" name="fechaExpiracion" placeholder="MM/YY" required>
                            <div class="invalid-feedback">Por favor, ingrese una fecha de expiración válida (MM/YY).</div>
                        </div>
                        <div class="mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" maxlength="4" placeholder="CVV" required>
                            <div class="invalid-feedback">Por favor, ingrese un CVV válido (3 o 4 dígitos).</div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="pagar" class="btn btn-primary">Pagar</button>
                        </div>
                    </form>
                    <?php if (isset($mensajeExito)): ?>
                      
                        <script>
                            Swal.fire({
                                title: 'Éxito',
                                text: "<?php echo $mensajeExito; ?>",
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = "<?php echo $paginaCategoria; ?>";
                            });
                        </script>
                    <?php endif; ?>
                    <?php if (isset($mensajeError)): ?>
                        <div class="alert alert-danger mt-3"><?php echo $mensajeError; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.getElementById('formularioPago').addEventListener('submit', function(event) {
    var numeroTarjeta = document.getElementById('numeroTarjeta').value.replace(/\s+/g, '');
    var nombreTarjeta = document.getElementById('nombreTarjeta').value;
    var fechaExpiracion = document.getElementById('fechaExpiracion').value;
    var cvv = document.getElementById('cvv').value;

    var regexNumeroTarjeta = /^\d{16}$/;
    var regexFechaExpiracion = /^(0[1-9]|1[0-2])\/\d{2}$/;
    var regexCVV = /^\d{3,4}$/;

    if (!regexNumeroTarjeta.test(numeroTarjeta)) {
        event.preventDefault();
        Swal.fire('Error', 'Número de tarjeta inválido', 'error');
        return;
    }

    if (!regexFechaExpiracion.test(fechaExpiracion)) {
        event.preventDefault();
        Swal.fire('Error', 'Fecha de expiración inválida', 'error');
        return;
    }

    if (!regexCVV.test(cvv)) {
        event.preventDefault();
        Swal.fire('Error', 'CVV inválido', 'error');
        return;
    }
});
</script>
