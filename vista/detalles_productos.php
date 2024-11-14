<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/conexion.php';

if (!isset($_GET['id'])) {
    echo 'Producto no encontrado.';
    exit();
}

$idProducto = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

// Fetch product details and photos
$query = "SELECT p.*, u.nombreUsuario, u.foto as fotoUsuario, f.nombreFoto 
          FROM productos p
          JOIN usuarios u ON p.idUsuario = u.idUsuario
          LEFT JOIN fotos f ON p.idProducto = f.idProducto
          WHERE p.idProducto = ?";
$stmt = $db->prepare($query);
$stmt->execute([$idProducto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo 'Producto no encontrado.';
    exit();
}

// Fetch all photos of the product
$queryFotos = "SELECT nombreFoto FROM fotos WHERE idProducto = ?";
$stmtFotos = $db->prepare($queryFotos);
$stmtFotos->execute([$idProducto]);
$fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

// Check user role and ownership
$idUsuario = $_SESSION['user_id'] ?? null;
$puedeComprar = false;
$esPropietario = false;

if ($idUsuario) {
    $queryRolUsuario = "SELECT idRol FROM usuarios_roles WHERE idUsuario = ?";
    $stmtRolUsuario = $db->prepare($queryRolUsuario);
    $stmtRolUsuario->execute([$idUsuario]);
    $rolUsuario = $stmtRolUsuario->fetch(PDO::FETCH_ASSOC);

    if ($rolUsuario && $rolUsuario['idRol'] == 2) {
        $puedeComprar = true;
    }

    if ($producto['idUsuario'] == $idUsuario) {
        $esPropietario = true;
    }
}
?>

<div class="modal-header">
    <h5 class="modal-title" style="font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars($producto['nombreProducto']); ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($fotos as $index => $foto): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($foto['nombreFoto']); ?>" class="d-block w-100" alt="Foto del producto" style="height: 500px; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <form>
                <div class="mb-3">
                    <label for="nombreProducto" class="form-label">Nombre del Producto</label>
                    <input type="text" class="form-control" id="nombreProducto" value="<?php echo htmlspecialchars($producto['nombreProducto']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio</label>
                    <input type="text" class="form-control" id="precio" value="€<?php echo htmlspecialchars($producto['precio']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="talla" class="form-label">Talla</label>
                    <input type="text" class="form-control" id="talla" value="<?php echo htmlspecialchars($producto['talla']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" rows="3" disabled><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>
                <?php if ($puedeComprar && !$esPropietario): ?>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='pago_producto.php?id=<?php echo $idProducto; ?>'">Comprar</button>
                <?php elseif ($esPropietario): ?>
                    <p class="text-warning">No puedes comprar tu propio producto.</p>
                <?php else: ?>
                    <p class="text-danger">Solo si estás suscrito puedes comprar este producto.</p>
                <?php endif; ?>
                <?php if (!$esPropietario): ?>
                    <button type="button" class="btn btn-dark" onclick="window.location.href='chat.php?id=<?php echo $idProducto; ?>&usuario=<?php echo $producto['idUsuario']; ?>'">Chat</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
