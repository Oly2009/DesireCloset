<?php
session_start();
require_once '../config/conexion.php';

// Verificar si el usuario es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Obtener datos de la base de datos
try {
    // Total de usuarios no administradores (excluyendo admins)
    $stmt = $conn->query("
        SELECT COUNT(*) 
        FROM usuarios u
        JOIN usuarios_roles ur ON u.idusuario = ur.idusuario
        WHERE ur.idrol = 2
    ");
    $totalUsuarios = $stmt->fetchColumn();

    // Total de productos en venta
    $stmt = $conn->query("SELECT COUNT(*) FROM productos p LEFT JOIN transacciones t ON p.idProducto = t.idProducto WHERE t.estado IS NULL OR t.estado = 'enventa'");
    $totalProductosEnVenta = $stmt->fetchColumn();

    // Total de productos vendidos
    $stmt = $conn->query("SELECT COUNT(*) FROM transacciones WHERE estado = 'vendido'");
    $totalProductosVendidos = $stmt->fetchColumn();

    // Total de ingresos de todos los usuarios (excepto admins)
    $stmt = $conn->query("
        SELECT COUNT(*) * 106 AS totalIngresos
        FROM usuarios u
        JOIN usuarios_roles ur ON u.idusuario = ur.idusuario
        WHERE ur.idrol != 1
    ");
    $totalIngresos = $stmt->fetchColumn();

    // Obtener el número de usuarios suscritos por mes con el rol de usuario (idRol = 2)
    $stmt = $conn->query("
        SELECT 
            MONTH(u.fecharegistro) as mes,
            COUNT(*) as suscritos
        FROM usuarios u
        JOIN usuarios_roles ur ON u.idusuario = ur.idusuario
        WHERE ur.idrol = 2
        GROUP BY MONTH(u.fecharegistro)
    ");
    $suscripcionesMensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular ingresos mensuales basados en los usuarios suscritos
    $ingresosMensuales = array_fill(0, 12, 0);
    foreach ($suscripcionesMensuales as $suscripcion) {
        $mes = $suscripcion['mes'] - 1; // Ajustar el mes para que sea de 0 a 11
        $ingresosMensuales[$mes] = $suscripcion['suscritos'] * (106 / 12); // Ingresos mensuales por suscripción
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

include '../includes/header_admin.php';
?>

<div class="admin d-flex" id="wrapper" style="min-height: 100vh; overflow-x: hidden;">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper" style="width: 150px;">
        <div class="sidebar-heading text-white">DesireCloset Admin</div>
        <div class="list-group list-group-flush">
            <a href="admin.php" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
            <a href="ver_usuarios.php" class="list-group-item list-group-item-action bg-dark text-white">Usuarios</a>
            <a href="ver_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Productos</a>
            <a href="estadistica_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Estadística</a>
            <a href="verificar_dni.php" class="list-group-item list-group-item-action bg-dark text-white">Verificar DNI</a>
            <a href="modificartablas.php" class="list-group-item list-group-item-action bg-dark text-white">Modificar BD</a>
            <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-white">Cerrar Sesión</a>
        </div>
    </div>

    <!-- Contenido de la página -->
    <main id="page-content-wrapper" class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        <div class="container mt-5">
            <h2>Dashboard</h2>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Usuarios</div>
                        <div class="card-body">
                            <h5 class="card-title text-white"><?php echo $totalUsuarios; ?></h5>
                          
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Productos en Venta</div>
                        <div class="card-body">
                            <h5 class="card-title text-white"><?php echo $totalProductosEnVenta; ?></h5>
                            <p class="card-text text-white">Total de productos en venta.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Productos Vendidos</div>
                        <div class="card-body">
                            <h5 class="card-title text-white"><?php echo $totalProductosVendidos; ?></h5>
                            <p class="card-text text-white">Total de productos vendidos.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Ingresos Totales</div>
                        <div class="card-body">
                            <h5 class="card-title text-white">€<?php echo number_format($totalIngresos, 2); ?></h5>
                          
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <canvas id="ingresosMensualesChart" style="width:100%; height:500px;"></canvas>
                </div>
            </div>
        </div>
    </main>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Toggle the side navigation
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    // Chart.js para ingresos mensuales
    const ctx = document.getElementById('ingresosMensualesChart').getContext('2d');
    const ingresosMensualesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            datasets: [{
                label: 'Ingresos Mensuales (€)',
                data: <?php echo json_encode($ingresosMensuales); ?>,
                backgroundColor: 'rgba(220, 53, 69, 0.5)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../includes/footer_admin.php'; ?>
