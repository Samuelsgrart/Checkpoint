<?php
session_start();
include './php/config.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id']) || !isset($_GET['tipo'])) {
    header("Location: index.php");
    exit();
}

$id_ver = intval($_GET['id']);
$tipo = $_GET['tipo'];

$stmt_n = $bd->prepare("SELECT nombre FROM usuarios WHERE id_usuario = ?");
$stmt_n->bind_param("i", $id_ver);
$stmt_n->execute();
$res_n = $stmt_n->get_result();
if($res_n->num_rows == 0) die("Usuario no encontrado");
$nombre_perfil = $res_n->fetch_assoc()['nombre'];

if ($tipo == 'seguidores') {
    $titulo = "Seguidores de " . $nombre_perfil;
    $sql = "SELECT u.id_usuario, u.nombre, u.descripcion
            FROM seguidores s
            JOIN usuarios u ON s.id_seguidor = u.id_usuario
            WHERE s.id_seguido = ?";
} else {
    $titulo = "Siguiendo - " . $nombre_perfil;
    $sql = "SELECT u.id_usuario, u.nombre, u.descripcion
            FROM seguidores s
            JOIN usuarios u ON s.id_seguido = u.id_usuario
            WHERE s.id_seguidor = ?";
}

$stmt = $bd->prepare($sql);
$stmt->bind_param("i", $id_ver);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?> - CHECKPOINT</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-main">
    <header class="header-principal">
        <div class="contenedor contenido-header">
            <a href="index.php" class="logo-link"><h1 class="logo">CHECK<span>POINT</span></h1></a>
            <nav class="user-nav">
                <a href="perfil.php?id=<?php echo $id_ver; ?>"><i class="fas fa-arrow-left"></i> Volver al perfil</a>
            </nav>
        </div>
    </header>

    <main class="contenedor perfil-page">
        <h2 class="titulo-seccion"><?php echo $titulo; ?></h2>
        
        <div class="grid-comentarios-local">
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($user = $resultado->fetch_assoc()): ?>
                    <div class="card-comentario" style="cursor: pointer;" onclick="window.location='perfil.php?id=<?php echo $user['id_usuario']; ?>'">
                        <div class="card-comentario-header" style="border-bottom: none; margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 1.5rem;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['nombre']); ?>&background=F3456B&color=fff&size=50"
                                     style="border-radius: 50%; border: 2px solid var(--rosa); width: 50px;" alt="Avatar">
                                <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>
                            </div>
                            <i class="fas fa-chevron-right" style="color: var(--rosa); opacity: 0.5;"></i>
                        </div>
                        <p class="comentario-texto-comunidad">
                            <?php echo htmlspecialchars($user['descripcion'] ?: 'Este usuario prefiere mantener el misterio...'); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <p>Parece que no hay nadie por aquí todavía.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>