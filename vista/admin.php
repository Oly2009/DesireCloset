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
    // Total de usuarios no administradores (rol = usuario)
    $stmt = $conn->query("
        SELECT COUNT(*) 
        FROM Usuarios u
        JOIN Usuarios_Roles ur ON u.idUsuario = ur.idUsuario
        WHERE ur.idRol = 2
    ");
    $totalUsuarios = $stmt->fetchColumn();

    // Total de productos
    $stmt = $conn->query("SELECT COUNT(*) FROM Productos");
    $totalProductos = $stmt->fetchColumn();

    // Total de ventas
    $stmt = $conn->query("SELECT COUNT(*) FROM Transacciones");
    $totalVentas = $stmt->fetchColumn();

    // Obtener el número de usuarios suscritos por mes con el rol de usuario (idRol = 2)
    $stmt = $conn->query("
        SELECT 
            MONTH(u.fechaRegistro) as mes,
            COUNT(*) as suscritos
        FROM Usuarios u
        JOIN Usuarios_Roles ur ON u.idUsuario = ur.idUsuario
        WHERE ur.idRol = 2
        GROUP BY MONTH(u.fechaRegistro)
    ");
    $suscripcionesMensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular ingresos mensuales basados en los usuarios suscritos
    $ingresosMensuales = array_fill(0, 12, 0);
    foreach ($suscripcionesMensuales as $suscripcion) {
        $mes = $suscripcion['mes'] - 1; // Ajustar el mes para que sea de 0 a 11
        $ingresosMensuales[$mes] = $suscripcion['suscritos'] * (106 / 12); // Ingresos mensuales por suscripción
    }

    // Calcular ingresos totales
    $totalIngresos = array_sum($ingresosMensuales) * 12; // Ingresos anuales
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

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

    <!-- Contenido de la página -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <button class="btn btn-primary" id="menu-toggle">Toggle Menu</button>
        </nav>

        <div class="container-fluid">
            <h1 class="mt-4">Dashboard</h1>
            <p>Bienvenido al panel de administración de DesireCloset.</p>
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Usuarios</div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $totalUsuarios; ?></h5>
                            <p class="card-text">Total de usuarios registrados.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Productos</div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $totalProductos; ?></h5>
                            <p class="card-text">Total de productos en venta.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">Ventas</div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $totalVentas; ?></h5>
                            <p class="card-text">Total de ventas realizadas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-header">Ingresos</div>
                        <div class="card-body">
                            <h5 class="card-title">€<?php echo number_format($totalIngresos, 2); ?></h5>
                            <p class="card-text">Ingresos totales.</p>
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
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<?php include '../includes/footer.php'; ?>

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

