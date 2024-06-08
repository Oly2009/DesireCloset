<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_GET['id'])) {
    echo 'Producto no encontrado.';
    exit();
}

$productId = $_GET['id'];
$userId = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

// Fetch product details and category details
$query = "SELECT p.*, c.nombreCategoria
          FROM productos p
          JOIN categorias c ON p.idCategoria = c.idCategoria
          WHERE p.idProducto = ?";
$stmt = $db->prepare($query);
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo 'Producto no encontrado.';
    exit();
}

// Fetch buyer details
$userQuery = "SELECT nombre, apellidos1, apellidos2, email FROM usuarios WHERE idUsuario = ?";
$userStmt = $db->prepare($userQuery);
$userStmt->execute([$userId]);
$buyer = $userStmt->fetch(PDO::FETCH_ASSOC);

// Determine category page based on the category name
$categoryPage = '';
switch ($product['nombreCategoria']) {
    case 'Bragas y Tangas':
        $categoryPage = 'braga.php';
        break;
    case 'Sujetadores':
        $categoryPage = 'sujetadores.php';
        break;
    case 'Fotos de pies':
        $categoryPage = 'fotosdepie.php';
        break;
    case 'Juguetes sexuales':
        $categoryPage = 'juguetessexuales.php';
        break;
    default:
        $categoryPage = 'todos.php';
        break;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    try {
        $db->beginTransaction();
        
        // Update transaction status
        $updateQuery = "UPDATE transacciones SET idComprador = ?, estado = 'comprado', fechaTransaccion = CURDATE(), hora = CURTIME() WHERE idProducto = ? AND estado = 'enventa'";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$userId, $productId]);
        
        // Set the product as sold
        $soldQuery = "UPDATE transacciones SET estado = 'vendido' WHERE idProducto = ? AND idVendedor = ?";
        $soldStmt = $db->prepare($soldQuery);
        $soldStmt->execute([$productId, $product['idUsuario']]);
        
        $db->commit();
        
        $successMessage = "Producto comprado con éxito!";
    } catch (Exception $e) {
        $db->rollBack();
        $errorMessage = "Error al actualizar el estado de la transacción: " . $e->getMessage();
    }
}

include '../includes/header.php'; 
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
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($buyer['nombre'] . ' ' . $buyer['apellidos1'] . ' ' . $buyer['apellidos2']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($buyer['email']); ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Detalles del Producto
                </div>
                <div class="card-body">
                    <p><strong>Nombre del Producto:</strong> <?php echo htmlspecialchars($product['nombreProducto']); ?></p>
                    <p><strong>Precio:</strong> €<?php echo htmlspecialchars($product['precio']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center position-relative">
                    Información de Pago
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-2" aria-label="Close" onclick="window.location.href = '<?php echo $categoryPage; ?>';"></button>
                </div>
                <div class="card-body">
                    <form id="paymentForm" method="post" novalidate>
                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">Número de Tarjeta</label>
                            <input type="text" class="form-control" id="cardNumber" name="cardNumber" maxlength="19" placeholder="XXXX XXXX XXXX XXXX" required>
                            <div class="invalid-feedback">Por favor, ingrese un número de tarjeta válido (16 dígitos).</div>
                        </div>
                        <div class="mb-3">
                            <label for="cardName" class="form-label">Nombre en la Tarjeta</label>
                            <input type="text" class="form-control" id="cardName" name="cardName" placeholder="Nombre Completo" required>
                            <div class="invalid-feedback">Por favor, ingrese el nombre tal como aparece en la tarjeta.</div>
                        </div>
                        <div class="mb-3">
                            <label for="expiryDate" class="form-label">Fecha de Expiración</label>
                            <input type="text" class="form-control" id="expiryDate" name="expiryDate" placeholder="MM/YY" required>
                            <div class="invalid-feedback">Por favor, ingrese una fecha de expiración válida (MM/YY).</div>
                        </div>
                        <div class="mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" maxlength="4" placeholder="CVV" required>
                            <div class="invalid-feedback">Por favor, ingrese un CVV válido (3 o 4 dígitos).</div>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="pay" class="btn btn-primary w-100">Pagar</button>
                           
                        </div>
                    </form>
                    <?php if (isset($successMessage)): ?>
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        <script>
                            Swal.fire({
                                title: 'Éxito',
                                text: "<?php echo $successMessage; ?>",
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = "<?php echo $categoryPage; ?>";
                            });
                        </script>
                    <?php endif; ?>
                    <?php if (isset($errorMessage)): ?>
                        <div class="alert alert-danger mt-3"><?php echo $errorMessage; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.getElementById('paymentForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var form = this;

    // Validación de los campos del formulario
    var cardNumber = document.getElementById('cardNumber').value;
    var cardName = document.getElementById('cardName').value;
    var expiryDate = document.getElementById('expiryDate').value;
    var cvv = document.getElementById('cvv').value;

    var cardNumberRegex = /^\d{16}$/;
    var expiryDateRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;
    var cvvRegex = /^\d{3,4}$/;

    if (!cardNumberRegex.test(cardNumber.replace(/\s+/g, ''))) {
        document.getElementById('cardNumber').classList.add('is-invalid');
        Swal.fire('Error', 'Número de tarjeta inválido', 'error');
        return;
    } else {
        document.getElementById('cardNumber').classList.remove('is-invalid');
    }

    if (!expiryDateRegex.test(expiryDate)) {
        document.getElementById('expiryDate').classList.add('is-invalid');
        Swal.fire('Error', 'Fecha de expiración inválida', 'error');
        return;
    } else {
        document.getElementById('expiryDate').classList.remove('is-invalid');
    }

    if (!cvvRegex.test(cvv)) {
        document.getElementById('cvv').classList.add('is-invalid');
        Swal.fire('Error', 'CVV inválido', 'error');
        return;
    } else {
        document.getElementById('cvv').classList.remove('is-invalid');
    }

    Swal.fire({
        title: 'Confirmar pago',
        text: "Confirmar pago de €" + "<?php echo htmlspecialchars($product['precio']); ?>",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, pagar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Pago realizado!',
                'Tu pago ha sido realizado con éxito.',
                'success'
            ).then(() => {
                form.submit(); // Enviar el formulario al backend para completar el registro
            });
        }
    });
});
</script>
