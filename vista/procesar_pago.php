<?php
session_start();
require_once '../config/conexion.php';
require_once '../paypal/PaypalPro.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['productId']) || !isset($_POST['precio'])) {
        echo 'Producto no encontrado.';
        exit();
    }

    $productId = $_POST['productId'];
    $precio = $_POST['precio'];
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        echo 'Debes estar registrado y logueado para comprar.';
        exit();
    }

    // Información del comprador
    $nombreTarjeta = $_POST['name_on_card'] ?? '';
    $numeroTarjeta = $_POST['card_number'] ?? '';
    $mesExpiracion = $_POST['expiry_month'] ?? '';
    $añoExpiracion = $_POST['expiry_year'] ?? '';
    $cvv = $_POST['cvv'] ?? '';

    if (empty($nombreTarjeta) || empty($numeroTarjeta) || empty($mesExpiracion) || empty($añoExpiracion) || empty($cvv)) {
        echo 'Todos los campos son obligatorios.';
        exit();
    }

    // Obtener los detalles del producto
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT * FROM productos WHERE idProducto = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo 'Producto no encontrado.';
        exit();
    }

    // Crear una instancia de la clase PaypalPro
    $paypal = new PaypalPro();

    // Datos del pago
    $paymentData = [
        'AMT' => $precio,
        'CREDITCARDTYPE' => 'Visa', // Cambia esto según el tipo de tarjeta
        'ACCT' => $numeroTarjeta,
        'EXPDATE' => $mesExpiracion . $añoExpiracion,
        'CVV2' => $cvv,
        'FIRSTNAME' => $nombreTarjeta,
        'LASTNAME' => 'Doe', // Asegúrate de proporcionar un apellido
        'STREET' => '123 Any Street',
        'CITY' => 'San Jose',
        'STATE' => 'CA',
        'ZIP' => '95131',
        'COUNTRYCODE' => 'US',
        'CURRENCYCODE' => 'USD'
    ];

    try {
        $response = $paypal->doDirectPayment($paymentData);

        if (strtoupper($response['ACK']) == 'SUCCESS' || strtoupper($response['ACK']) == 'SUCCESSWITHWARNING') {
            $paymentId = $response['TRANSACTIONID'];

            // Actualizar el estado del producto a "vendido" y registrar la transacción en la base de datos
            $updateProductQuery = "UPDATE productos SET estado = 'vendido' WHERE idProducto = ?";
            $updateProductStmt = $db->prepare($updateProductQuery);
            $updateProductStmt->execute([$productId]);

            $transaccionQuery = "INSERT INTO transacciones (idComprador, idVendedor, idProducto, fechaTransaccion, cantidad) VALUES (?, ?, ?, NOW(), ?)";
            $transaccionStmt = $db->prepare($transaccionQuery);
            $transaccionStmt->execute([$userId, $product['idUsuario'], $productId, $precio]);

            echo 'Pago completado con éxito. ID de la transacción: ' . $paymentId;
        } else {
            echo 'El pago no se completó. Error: ' . $response['L_LONGMESSAGE0'];
        }
    } catch (Exception $e) {
        echo 'El pago no se completó. Error: ' . $e->getMessage();
    }
} else {
    echo 'Método no permitido.';
}
