<?php
session_start(); // Iniciar la sesión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/conexion.php';

// Obtener el ID del usuario
$userId = $_SESSION['user_id'];

$database = new Database(); 
$db = $database->getConnection();

// Inserta un mensaje si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contenido'])) {
    $contenido = $_POST['contenido'];
    $idProducto = $_POST['idProducto'];
    $idReceptor = $_POST['idReceptor'];

    $queryInsert = "INSERT INTO mensajes (idEmisor, idReceptor, idProducto, contenido, visto) VALUES (?, ?, ?, ?, 0)";
    $stmtInsert = $db->prepare($queryInsert);
    if ($stmtInsert->execute([$userId, $idReceptor, $idProducto, $contenido])) {
        header("Location: chat.php?id=$idProducto&usuario=$idReceptor");
        exit();
    } else {
        echo 'Error al enviar el mensaje.';
    }
}

// Marcar los mensajes como vistos si se envía el formulario de vaciar chat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vaciar_chat'])) {
    $idProducto = $_POST['idProducto'];
    $idReceptor = $_POST['idReceptor'];
    $queryUpdate = "UPDATE mensajes SET visto = 2 WHERE idProducto = ? AND ((idEmisor = ? AND idReceptor = ?) OR (idEmisor = ? AND idReceptor = ?))";
    $stmtUpdate = $db->prepare($queryUpdate);
    if ($stmtUpdate->execute([$idProducto, $userId, $idReceptor, $idReceptor, $userId])) {
        header("Location: chat.php?id=$idProducto&usuario=$idReceptor");
        exit();
    } else {
        echo 'Error al vaciar el chat.';
    }
}

// Consulta para obtener todas las conversaciones del usuario
$query = "SELECT DISTINCT p.idProducto, p.nombreProducto, u.nombreUsuario, u.idUsuario, f.nombreFoto
          FROM mensajes m
          JOIN productos p ON m.idProducto = p.idProducto
          JOIN usuarios u ON (m.idEmisor = u.idUsuario OR m.idReceptor = u.idUsuario)
          LEFT JOIN fotos f ON p.idProducto = f.idProducto
          WHERE (m.idEmisor = ? OR m.idReceptor = ?) AND u.idUsuario != ? AND m.visto != 2
          GROUP BY p.idProducto, u.idUsuario";
$stmt = $db->prepare($query);
$stmt->execute([$userId, $userId, $userId]);
$conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <!-- Lista de conversaciones -->
        <div class="col-md-4">
            <h2>Mis Conversaciones</h2>
            <div class="list-group">
                <?php foreach ($conversaciones as $conv): ?>
                    <a href="chat.php?id=<?php echo $conv['idProducto']; ?>&usuario=<?php echo $conv['idUsuario']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-start align-items-center">
                            <?php if ($conv['nombreFoto']): ?>
                                <img src="<?php echo htmlspecialchars($conv['nombreFoto']); ?>" alt="Foto del producto" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <img src="../assets/img/default-product.png" alt="Foto del producto" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php endif; ?>
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($conv['nombreProducto']); ?></h5>
                                <small><?php echo htmlspecialchars($conv['nombreUsuario']); ?></small>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Chat -->
        <div class="col-md-8">
            <?php if (isset($_GET['id']) && isset($_GET['usuario'])): ?>
                <?php
                $idProducto = $_GET['id'];
                $idReceptor = $_GET['usuario'];

                // Consulta para obtener los detalles del producto
                $query = "SELECT p.*, u.nombreUsuario, u.foto as fotoUsuario, GROUP_CONCAT(f.nombreFoto) as nombreFoto
                          FROM productos p
                          JOIN usuarios u ON p.idUsuario = u.idUsuario
                          LEFT JOIN fotos f ON p.idProducto = f.idProducto
                          WHERE p.idProducto = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$idProducto]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verifica si el producto existe
                if (!$producto) {
                    echo 'Producto no encontrado.';
                    exit();
                }

                // Consulta para obtener los mensajes del producto
                $queryMensajes = "SELECT m.*, ue.nombreUsuario as nombreEmisor, ur.nombreUsuario as nombreReceptor 
                                  FROM mensajes m
                                  JOIN usuarios ue ON m.idEmisor = ue.idUsuario
                                  JOIN usuarios ur ON m.idReceptor = ur.idUsuario
                                  WHERE m.idProducto = ? AND ((m.idEmisor = ? AND m.idReceptor = ?) OR (m.idEmisor = ? AND m.idReceptor = ?))
                                  ORDER BY m.idMensaje ASC";
                $stmtMensajes = $db->prepare($queryMensajes);
                $stmtMensajes->execute([$idProducto, $userId, $idReceptor, $idReceptor, $userId]);
                $mensajes = $stmtMensajes->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="chat-container d-flex flex-column">
                    <!-- Información del producto y vendedor -->
                    <div class="chat-header d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($producto['nombreFoto']); ?>" alt="Foto del producto" class="img-thumbnail me-3" style="width: 70px; height: 70px; object-fit: cover;">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($producto['nombreProducto']); ?></h5>
                                <p class="mb-0"><?php echo htmlspecialchars($producto['precio']); ?> €</p>
                            </div>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($producto['nombreUsuario']); ?></strong>
                        </div>
                    </div>

                    <div class="chat-body flex-grow-1 overflow-auto mb-3">
                        <!-- Mensajes del chat -->
                        <div class="chat-messages">
                            <?php foreach ($mensajes as $mensaje): ?>
                                <div class="chat-message">
                                    <strong><?php echo htmlspecialchars($mensaje['nombreEmisor']); ?>:</strong>
                                    <p><?php echo htmlspecialchars($mensaje['contenido']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Formulario para enviar mensajes -->
                    <form method="POST" class="d-flex">
                        <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>">
                        <input type="hidden" name="idReceptor" value="<?php echo $idReceptor; ?>">
                        <input type="text" name="contenido" class="form-control me-2" placeholder="Escribir mensaje" required>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>

                    <!-- Botones para vaciar chat y volver -->
                    <div class="mt-3">
                        <form method="POST" class="d-inline-block me-2">
                            <input type="hidden" name="vaciar_chat" value="1">
                            <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>">
                            <input type="hidden" name="idReceptor" value="<?php echo $idReceptor; ?>">
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Vaciar chat</button>
                        </form>
                        <button class="btn btn-dark" onclick="window.location.href='miperfil.php'"><i class="fas fa-arrow-left"></i> Volver</button>
                    </div>
                </div>
            <?php else: ?>
                <p>Selecciona una conversación para ver los mensajes.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
