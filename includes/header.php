<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
$isLoggedIn = isset($_SESSION['user_id']);

// Verificar si el usuario es administrador
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

// Obtener el nombre del archivo actual para activar el enlace correspondiente en la barra de navegación
$current_file = basename($_SERVER['PHP_SELF']);

// Conectar a la base de datos para obtener la cantidad de mensajes no leídos
$mensajesNuevos = 0;
if ($isLoggedIn) {
    require_once '../config/conexion.php';
    $database = new Database();
    $conn = $database->getConnection();

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as mensajesNuevos FROM mensajes WHERE idReceptor = ? AND leido = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $mensajesNuevos = $stmt->fetch(PDO::FETCH_ASSOC)['mensajesNuevos'];
    } catch (Exception $e) {
        $mensajesNuevos = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DesireCloset</title>
    <link rel="shortcut icon" href="../assets/img/logo.jpg" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="py-2" style="background-color: #000000;">
        <div class="header container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-8 d-flex align-items-center">
                    <a href="#" class="navbar-brand">
                        <img src="../assets/img/logo.jpg" alt="Logo de DesireCloset" class="logo" style="width:80px;">
                    </a>
                    <div class="ms-2">
                        <h2 class="text-danger mb-0">DesireCloset</h2>
                        <h5 class="text-danger text-center display-10 mb-0">Conectando Fantasías</h5>
                    </div>
                </div>
                <div class="col-lg-4 d-flex align-items-center justify-content-end">
                    <form class="d-flex me-3" action="../vista/busqueda.php" method="GET">
                        <input class="form-control me-2" type="search" name="busqueda" placeholder="Buscar" aria-label="Buscar">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-search"></i></button>
                    </form>
                    <ul class="navbar-nav d-flex flex-row">
                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item"><a class="nav-link text-danger me-3" href="../vista/chat.php"><i class="fas fa-comments fa-lg"></i>
                                <?php if ($mensajesNuevos > 0): ?>
                                    <span class="badge bg-danger"><?php echo $mensajesNuevos; ?></span>
                                <?php endif; ?>
                            </a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link text-danger me-3" href="../vista/miperfil.php"><i class="fas fa-user fa-lg"></i></a></li>
                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link text-danger dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cog fa-lg"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="editar_perfil.php"><i class="fas fa-pencil-alt"></i> Editar perfil</a></li>
                                    
                                    <li><a class="dropdown-item" href="establecer_contrasena.php"><i class="fas fa-key"></i> Cambiar contraseña</a></li>
                                    
                                    <?php if (!$isAdmin): // Mostrar opción de borrar solo si no es administrador ?>
                                    <li>
                                        <form action="borrar_perfil.php" method="post" onsubmit="return confirm('¿Estás seguro de que quieres borrar tu perfil? Esta acción no se puede deshacer.');">
                                            <button type="submit" class="dropdown-item"><i class="fas fa-trash-alt"></i> Borrar perfil</button>
                                        </form>
                                    </li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="verInformacion.php"><i class="fas fa-info-circle"></i> Ver información</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="../vista/logout.php" method="post">
                                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    <nav class="navbar navbar-expand-lg navbar-light bg-danger">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a href="../vista/principal.php" class="nav-link fs-4 <?= $current_file == 'principal.php' ? 'active' : '' ?>">Home</a></li>
                    <li class="nav-item"><a href="../vista/todos.php" class="nav-link fs-4 <?= $current_file == 'todos.php' ? 'active' : '' ?>">Todo</a></li>
                    <li class="nav-item"><a href="../vista/braga.php" class="nav-link fs-4 <?= $current_file == 'braga.php' ? 'active' : '' ?>">Braga y Tanga</a></li>
                    <li class="nav-item"><a href="../vista/sujetadores.php" class="nav-link fs-4 <?= $current_file == 'sujetadores.php' ? 'active' : '' ?>">Sujetadores</a></li>
                    <li class="nav-item"><a href="../vista/fotosdepie.php" class="nav-link fs-4 <?= $current_file == 'fotosdepie.php' ? 'active' : '' ?>">Fotos de pies</a></li>
                    <li class="nav-item"><a href="../vista/juguetessexuales.php" class="nav-link fs-4 <?= $current_file == 'juguetessexuales.php' ? 'active' : '' ?>">Juguetes Sexuales</a></li>
                    <?php if ($isAdmin): ?>
                        <li class="nav-item"><a href="admin.php" class="nav-link fs-4 <?= $current_file == 'admin.php' ? 'active' : '' ?>">Panel de Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>