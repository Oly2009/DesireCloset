<?php
require_once '../config/conexion.php';

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación del producto si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'borrarProducto') {
    $idProducto = $_POST['idProducto'];
    
    // Eliminar fotos asociadas al producto
    $stmt = $conn->prepare("DELETE FROM Fotos WHERE idProducto = ?");
    $stmt->execute([$idProducto]);

    // Eliminar transacciones asociadas al producto
    $stmt = $conn->prepare("DELETE FROM Transacciones WHERE idProducto = ?");
    $stmt->execute([$idProducto]);

    // Eliminar el producto
    $stmt = $conn->prepare("DELETE FROM Productos WHERE idProducto = ?");
    $stmt->execute([$idProducto]);
}

// Obtener productos, sus fotos, estado de transacción y usuario correspondiente
$query = "SELECT p.*, u.nombreUsuario, c.nombreCategoria, f.nombreFoto, t.estado 
          FROM Productos p
          JOIN Usuarios u ON p.idUsuario = u.idUsuario
          JOIN Categorias c ON p.idCategoria = c.idCategoria
          LEFT JOIN Fotos f ON p.idProducto = f.idProducto
          LEFT JOIN Transacciones t ON p.idProducto = t.idProducto";
$stmt = $conn->prepare($query);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-white">DesireCloset Admin</div>
        <div class="list-group list-group-flush">
            <a href="admin.php" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
            <a href="ver_usuarios.php" class="list-group-item list-group-item-action bg-dark text-white">Usuarios</a>
            <a href="ver_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Productos</a>
            <a href="estadistica_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Estadística</a>
            <a href="verificar_dni.php" class="list-group-item list-group-item-action bg-dark text-white">Verificar DNI</a>
            <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-white">Cerrar Sesión</a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <button class="btn btn-primary" id="menu-toggle">Toggle Menu</button>
        </nav>

        <div class="container mt-5">
            <h2>Gestión de Productos</h2>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Producto</th>
                        <th>Talla</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Condición</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th>Categoría</th>
                        <th>Fotos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Agrupar productos por ID de producto
                    $productosAgrupados = [];
                    foreach ($productos as $producto) {
                        $idProducto = $producto['idProducto'];
                        if (!isset($productosAgrupados[$idProducto])) {
                            $productosAgrupados[$idProducto] = $producto;
                            $productosAgrupados[$idProducto]['fotos'] = [];
                        }
                        if ($producto['nombreFoto']) {
                            $productosAgrupados[$idProducto]['fotos'][] = $producto['nombreFoto'];
                        }
                    }
                    foreach ($productosAgrupados as $producto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['idProducto']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombreProducto']); ?></td>
                            <td><?php echo htmlspecialchars($producto['talla']); ?></td>
                            <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                            <td>€<?php echo htmlspecialchars($producto['precio']); ?></td>
                            <td><?php echo htmlspecialchars($producto['condicion']); ?></td>
                            <td><?php echo htmlspecialchars($producto['estado']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombreUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombreCategoria']); ?></td>
                            <td>
                                <?php if (!empty($producto['fotos'])): ?>
                                    <?php foreach ($producto['fotos'] as $foto): ?>
                                        <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto de producto" style="width: 50px; height: 50px;">
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    No hay fotos
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                    <input type="hidden" name="idProducto" value="<?php echo $producto['idProducto']; ?>">
                                    <input type="hidden" name="action" value="borrarProducto">
                                    <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<script>
    // Toggle the side navigation
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
</script>

<?php include '../includes/footer.php'; ?>
