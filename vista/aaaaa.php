<?php
session_start();
require_once '../config/conexion.php';

// Verifica si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$queryCategorias = "SELECT * FROM categorias";
$stmtCategorias = $conn->prepare($queryCategorias);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

$queryUsuarios = "SELECT * FROM usuarios";
$stmtUsuarios = $conn->prepare($queryUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-5">
    <h2 class="text-center text-danger">Subir Productos</h2>
    <form action="subir_productos.php" method="POST" enctype="multipart/form-data">
        <div id="productos-container">
            <div class="producto mb-4 p-4 border">
                <div class="form-group">
                    <label for="nombreProducto1">Nombre del Producto</label>
                    <input type="text" class="form-control" id="nombreProducto1" name="nombreProducto[]" required>
                </div>
                <div class="form-group">
                    <label for="talla1">Talla</label>
                    <input type="text" class="form-control" id="talla1" name="talla[]" required>
                </div>
                <div class="form-group">
                    <label for="descripcion1">Descripción</label>
                    <textarea class="form-control" id="descripcion1" name="descripcion[]" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="precio1">Precio (€)</label>
                    <input type="number" class="form-control" id="precio1" name="precio[]" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="condicion1">Condición</label>
                    <input type="text" class="form-control" id="condicion1" name="condicion[]" required>
                </div>
                <div class="form-group">
                    <label for="categoria1">Categoría</label>
                    <select class="form-control" id="categoria1" name="idCategoria[]" required>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['idCategoria'] ?>"><?= $categoria['nombreCategoria'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="usuario1">Usuario</label>
                    <select class="form-control" id="usuario1" name="idUsuario[]" required>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['idUsuario'] ?>"><?= $usuario['nombreUsuario'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fotos1">Fotos del Producto</label>
                    <input type="file" class="form-control" id="fotos1" name="fotos1[]" multiple required>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary mb-4" id="agregarProducto">Agregar otro producto</button>
        <button type="submit" class="btn btn-danger">Subir Productos</button>
    </form>
</div>

<script>
document.getElementById('agregarProducto').addEventListener('click', function() {
    var productosContainer = document.getElementById('productos-container');
    var productosCount = productosContainer.getElementsByClassName('producto').length;
    var productoIndex = productosCount + 1;

    var productoDiv = document.createElement('div');
    productoDiv.className = 'producto mb-4 p-4 border';
    productoDiv.innerHTML = `
        <div class="form-group">
            <label for="nombreProducto${productoIndex}">Nombre del Producto</label>
            <input type="text" class="form-control" id="nombreProducto${productoIndex}" name="nombreProducto[]" required>
        </div>
        <div class="form-group">
            <label for="talla${productoIndex}">Talla</label>
            <input type="text" class="form-control" id="talla${productoIndex}" name="talla[]" required>
        </div>
        <div class="form-group">
            <label for="descripcion${productoIndex}">Descripción</label>
            <textarea class="form-control" id="descripcion${productoIndex}" name="descripcion[]" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="precio${productoIndex}">Precio (€)</label>
            <input type="number" class="form-control" id="precio${productoIndex}" name="precio[]" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="condicion${productoIndex}">Condición</label>
            <input type="text" class="form-control" id="condicion${productoIndex}" name="condicion[]" required>
        </div>
        <div class="form-group">
            <label for="categoria${productoIndex}">Categoría</label>
            <select class="form-control" id="categoria${productoIndex}" name="idCategoria[]" required>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['idCategoria'] ?>"><?= $categoria['nombreCategoria'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="usuario${productoIndex}">Usuario</label>
            <select class="form-control" id="usuario${productoIndex}" name="idUsuario[]" required>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['idUsuario'] ?>"><?= $usuario['nombreUsuario'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="fotos${productoIndex}">Fotos del Producto</label>
            <input type="file" class="form-control" id="fotos${productoIndex}" name="fotos${productoIndex}[]" multiple required>
        </div>
    `;
    productosContainer.appendChild(productoDiv);
});
</script>

<?php include '../includes/footer.php'; ?>
