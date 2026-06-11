<?php
session_start();
include './php/config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

//VARIABLES
$id_propio = $_SESSION['id_usuario'];
$id_user = $id_propio;
$es_mi_perfil = true;
$lo_sigo = false;
$total_seguidores = 0;
$total_siguiendo = 0;
$mensaje_bio = "";
$error_bio = "";

include_once './php/logros.php';
verificarLogros($id_propio, $bd);

if (isset($_GET['id']) && $_GET['id'] != $id_propio) {
    $es_mi_perfil = false;
    $id_user = intval($_GET['id']);
}

//IMGS
if (!function_exists('getCoverImage')) {
    function getCoverImage($url, $size = 't_cover_big', $placeholderText = 'Sin+Portada') {
        if (empty($url)) return "https://via.placeholder.com/264x352?text=" . $placeholderText;
        $clean = str_replace('t_thumb', $size, preg_replace('#^https?://#', '', $url));
        return "https://" . ltrim($clean, '/');
    }
}

//BIO
if ($es_mi_perfil && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_bio'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $nueva_bio = substr(trim($_POST['descripcion']), 0, 255);
        $stmt_up = $bd->prepare("UPDATE usuarios SET descripcion = ? WHERE id_usuario = ?");
        $stmt_up->bind_param("si", $nueva_bio, $id_propio);
        if ($stmt_up->execute()) $mensaje_bio = "¡Bio actualizada!";
        $stmt_up->close();
    }
}

//DATOS
$stmt_u = $bd->prepare("SELECT nombre, email, descripcion, avatar_url FROM usuarios WHERE id_usuario = ?");
$stmt_u->bind_param("i", $id_user);
$stmt_u->execute();
$user_data = $stmt_u->get_result()->fetch_assoc();
$stmt_u->close();

if (!$user_data) die("Usuario no encontrado.");

//SEGUIDORES
$res_seg = $bd->query("SELECT COUNT(*) as total FROM seguidores WHERE id_seguido = $id_user");
if($res_seg) $total_seguidores = $res_seg->fetch_assoc()['total'];

$res_sig = $bd->query("SELECT COUNT(*) as total FROM seguidores WHERE id_seguidor = $id_user");
if($res_sig) $total_siguiendo = $res_sig->fetch_assoc()['total'];

if (!$es_mi_perfil) {
    $check = $bd->prepare("SELECT id_seguimiento FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?");
    $check->bind_param("ii", $id_propio, $id_user);
    $check->execute();
    if ($check->get_result()->num_rows > 0) $lo_sigo = true;
    $check->close();
}

//FILTRADO
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : 'Todos';

//FAVORITOS
$res_favs = $bd->query("SELECT id_videojuego, nombre_videojuego, portada_url, nota_checkpoint FROM reseñas WHERE id_usuario = $id_user AND favorito = 1 LIMIT 4");
$total_favoritos = $res_favs->num_rows;

//HISTORIAL
$sql_historial = "SELECT id_videojuego, nombre_videojuego, portada_url, comentario, nota_checkpoint, estado 
                  FROM reseñas WHERE id_usuario = $id_user";

if ($estado_filtro !== 'Todos') {
    $sql_historial .= " AND estado = '" . $bd->real_escape_string($estado_filtro) . "'";
}

$sql_historial .= " ORDER BY id_reseña DESC LIMIT 9"; 

$res_historial = $bd->query($sql_historial);
$total_criticas_filtradas = $res_historial->num_rows;

$res_total_real = $bd->query("SELECT COUNT(*) as total FROM reseñas WHERE id_usuario = $id_user");
$total_criticas = $res_total_real->fetch_assoc()['total'];

$res_mis_logros = $bd->query("SELECT l.* FROM logros l JOIN usuarios_logros ul ON l.id_logro = ul.id_logro WHERE ul.id_usuario = $id_user");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user_data['nombre']); ?> - CHECKPOINT</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-main">

<?php if (isset($_SESSION['nuevo_logro']) && !empty($_SESSION['nuevo_logro'])): ?>
        <div id="logro-data"
             style="display:none;"
             data-nombre="<?php echo htmlspecialchars($_SESSION['nuevo_logro']); ?>">
        </div>
        <?php unset($_SESSION['nuevo_logro']); ?>
    <?php endif; ?>
<header class="header-principal">
    <div class="contenedor contenido-header">
        <a href="index.php" class="logo-link"><h1 class="logo">CHECK<span>POINT</span></h1></a>
        <div class="buscador-container">
            <input type="text" id="input-busqueda" placeholder="Buscar juegos..." autocomplete="off">
            <div id="sugerencias" class="sugerencias-box"></div>
        </div>
<nav class="user-nav">
    <?php if (isset($_SESSION['nombre'])): ?>
        <div class="user-info-header">
            <a href="perfil.php" class="user-profile-data">
                <?php
                    $mi_foto_header = !empty($_SESSION['avatar_url'])
                        ? $_SESSION['avatar_url']
                        : "https://ui-avatars.com/api/?name=".urlencode($_SESSION['nombre'])."&background=F3456B&color=fff&size=40";
                ?>
                <img src="<?php echo $mi_foto_header; ?>" alt="Perfil" class="img-perfil-header">
                
                <span class="user-name">Hola, <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span></span>
            </a>
            <a href="logout.php" class="btn-logout">Salir</a>
        </div>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="registro.php">Registro</a>
    <?php endif; ?>
</nav>
    </div>
</header>

<main class="contenedor" style="margin-top: 5rem;">
    <?php if ($mensaje_bio): ?><div class="mensaje-exito"><?php echo htmlspecialchars($mensaje_bio); ?></div><?php endif; ?>
    <?php if ($error_bio): ?><div class="mensaje-error"><?php echo htmlspecialchars($error_bio); ?></div><?php endif; ?>

<section class="perfil-header">
<div class="perfil-main-content">
    <div class="perfil-avatar-container">
    <?php
        $avatar_perfil_visitado = !empty($user_data['avatar_url'])
            ? $user_data['avatar_url']
            : "https://ui-avatars.com/api/?name=".urlencode($user_data['nombre'])."&background=F3456B&color=fff&size=150";
    ?>
    <img src="<?php echo $avatar_perfil_visitado; ?>" alt="Avatar" class="avatar-img">
    
    <?php if ($es_mi_perfil): ?>
        <form action="php/subir_avatar.php" method="POST" enctype="multipart/form-data" id="form-avatar">
            <label for="input-avatar" class="btn-cambiar-foto">
                <i class="fas fa-camera"></i>
            </label>
            <input type="file" name="avatar" id="input-avatar" style="display:none;" onchange="document.getElementById('form-avatar').submit();">
        </form>
    <?php endif; ?>
</div>

    <div class="perfil-info">
        <div class="perfil-info-top">
            <h2 class="user-name"><?php echo htmlspecialchars($user_data['nombre']); ?></h2>
            <?php if ($es_mi_perfil): ?>
                <button type="button" class="btn-editar-icon" id="btn-editar-bio" title="Editar Perfil"><i class="fas fa-edit"></i> Editar Perfil</button>
            <?php else: ?>
                <a href="php/procesar_follow.php?id=<?php echo $id_user; ?>&accion=<?php echo $lo_sigo ? 'unfollow' : 'follow'; ?>" class="<?php echo $lo_sigo ? 'btn-unfollow' : 'btn-follow'; ?>" style="text-decoration: none; padding: 0.8rem 1.5rem; border-radius: 0.5rem; font-weight: bold;">
                    <i class="fas <?php echo $lo_sigo ? 'fa-user-minus' : 'fa-user-plus'; ?>"></i> <?php echo $lo_sigo ? 'Dejar de seguir' : 'Seguir'; ?>
                </a>
            <?php endif; ?>
        </div>
            <div id="bio-container">
                <p class="user-bio" id="txt-bio"><?php echo nl2br(htmlspecialchars($user_data['descripcion'] ?: '¡Bienvenido a mi perfil! Aquí puedes compartir información sobre ti y tus juegos favoritos.')); ?></p>
                <?php if ($es_mi_perfil): ?>
                <form method="POST" id="form-bio" class="oculto" style="margin-top: 1.5rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <textarea name="descripcion" class="textarea-bio" maxlength="255"><?php echo htmlspecialchars($user_data['descripcion'] ?? ''); ?></textarea>
                    <div style="text-align: right; margin-top: 0.5rem; font-size: 1.2rem; color: var(--crema); opacity: 0.6;"><span id="char-count">0</span>/255</div>
                    <div class="bio-btn-group">
                        <button type="submit" name="actualizar_bio" class="btn-registro-small"><i class="fas fa-save"></i> Guardar</button>
                        <button type="button" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>

            <div class="perfil-stats">
                <div class="stat-item"><span class="stat-num"><?php echo $total_criticas; ?></span><span class="stat-label">Críticas</span></div>
                <a href="lista_social.php?id=<?php echo $id_user; ?>&tipo=seguidores" class="stat-item" style="text-decoration: none;"><span class="stat-num"><?php echo $total_seguidores; ?></span><span class="stat-label">Seguidores</span></a>
                <a href="lista_social.php?id=<?php echo $id_user; ?>&tipo=siguiendo" class="stat-item" style="text-decoration: none;"><span class="stat-num"><?php echo $total_siguiendo; ?></span><span class="stat-label">Siguiendo</span></a>
            </div>
        </div>
    </div>
</section>

<section>
    <h3 class="titulo-seccion">Mis Imprescindibles ❤️</h3>
    <div class="grid-juegos">
        <?php if($total_favoritos > 0): ?>
            <?php while($fav = $res_favs->fetch_assoc()):
                $img_fav = getCoverImage($fav['portada_url'], 't_cover_big');
            ?>
                <article class="card">
                    <a href="juego.php?id=<?php echo $fav['id_videojuego']; ?>" class="card-link">
                        <div class="badge-rating"><?php echo number_format($fav['nota_checkpoint'], 1); ?></div>
                        <img src="<?php echo $img_fav; ?>" alt="Portada" loading="lazy">
                        <div class="card__contenido">
                            <h3><?php echo htmlspecialchars($fav['nombre_videojuego']); ?></h3>
                            <span class="btn-ver-mas">Ver más</span>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 4rem; background: rgba(255,255,255,0.03); border-radius: 1rem;">
                <p style="color: var(--crema); opacity: 0.7; font-size: 1.8rem;"><i class="far fa-heart" style="display: block; margin-bottom: 1rem; font-size: 3rem;"></i>Aún no hay favoritos.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section>
    <h3 class="titulo-seccion" style="margin-top: 5rem;">Mi Historial de Críticas</h3>

    <div class="perfil-tabs">
        <a href="perfil.php?id=<?php echo $id_user; ?>&estado=Todos" class="tab-item <?php echo $estado_filtro == 'Todos' ? 'active' : ''; ?>">Todos</a>
        <a href="perfil.php?id=<?php echo $id_user; ?>&estado=Jugando" class="tab-item <?php echo $estado_filtro == 'Jugando' ? 'active' : ''; ?>">🎮 Jugando</a>
        <a href="perfil.php?id=<?php echo $id_user; ?>&estado=Completado" class="tab-item <?php echo $estado_filtro == 'Completado' ? 'active' : ''; ?>">🏆 Completados</a>
        <a href="perfil.php?id=<?php echo $id_user; ?>&estado=Pendiente" class="tab-item <?php echo $estado_filtro == 'Pendiente' ? 'active' : ''; ?>">⏳ Pendientes</a>
        <a href="perfil.php?id=<?php echo $id_user; ?>&estado=Abandonado" class="tab-item <?php echo $estado_filtro == 'Abandonado' ? 'active' : ''; ?>">❌ Abandonados</a>
    </div>

    <div class="scroll-historial-container" id="historial-ajax">
        <div class="grid-reseñas">
            <?php if($total_criticas_filtradas > 0): ?>
                <?php while($rev = $res_historial->fetch_assoc()):
                    $img_historial = getCoverImage($rev['portada_url'], 't_cover_small');
                ?>
                    <article class="review-mini">
                        <img src="<?php echo $img_historial; ?>" class="img-historial" alt="Portada" loading="lazy">
                        <div class="review-info" style="flex: 1;">
                            <span class="badge-estado"><?php echo htmlspecialchars($rev['estado'] ?: 'Completado'); ?></span>
                            <div class="review-header">
                                <a href="juego.php?id=<?php echo $rev['id_videojuego']; ?>" class="link-juego-review">
                                    <?php echo htmlspecialchars($rev['nombre_videojuego']); ?>
                                </a>
                            </div>
                            <p class="comentario-txt"><?php echo htmlspecialchars($rev['comentario']); ?></p>
                           <div class="review-scores">
    <?php if ($rev['estado'] !== 'Pendiente'): ?>
        <div class="score-item check-mini">
            <span class="label">Mi Nota:</span>
            <span class="valor"><?php echo number_format($rev['nota_checkpoint'], 1); ?></span>
        </div>
    <?php else: ?>
        <div class="score-item check-mini">
            <span class="label" style="opacity: 0.5;">Sin puntuar</span>
        </div>
    <?php endif; ?>
</div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; opacity: 0.6; grid-column: 1/-1;">
                    <p style="color: var(--crema);">No hay juegos en esta categoría.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<section>
    <h3 class="titulo-seccion" style="margin-top: 5rem;">Logros</h3>
    <div class="grid-logros">
        <?php
        $todos = $bd->query("SELECT * FROM logros");
        $ganados_res = $bd->query("SELECT id_logro FROM usuarios_logros WHERE id_usuario = $id_user");
        $ganados_ids = [];
        while($g = $ganados_res->fetch_assoc()) { $ganados_ids[] = $g['id_logro']; }

        while($l = $todos->fetch_assoc()):
            $desbloqueado = in_array($l['id_logro'], $ganados_ids);
            $icono = '🏆';
        ?>
            <div class="logro-card <?php echo $desbloqueado ? 'ganado' : 'bloqueado'; ?>">
                <div class="logro-contenido">
                    <span class="logro-visual"><?php echo $desbloqueado ? $icono : '🔒'; ?></span>
                    <div class="logro-info">
                        <span class="logro-titulo"><?php echo $l['nombre']; ?></span>
                        <p class="logro-desc"><?php echo $l['descripcion']; ?></p>
                    </div>
                </div>
                <?php if(!$desbloqueado): ?><div class="progreso-mini">Bloqueado</div><?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</section>
</main>

<footer class="footer-principal">
    <div class="contenedor grid-footer">
        <div class="footer__col">
            <h3 class="logo">CHECK<span>POINT</span></h3>
            <p>Tu comunidad gaming de confianza.</p>
        </div>
        <div class="footer__col">
            <h4>Contacto</h4>
            <div class="footer__nav">
                <a href="mailto:samuelsgrart@gmail.com"><i class="fas fa-envelope"></i> samuelsgrart@gmail.com</a>
                <a href="https://www.linkedin.com/in/samuelsgrart" target="_blank"><i class="fab fa-linkedin"></i> LinkedIn</a>
            </div>
        </div>
    </div>
    <div class="footer__copyright">
        <p>&copy; 2026 CHECKPOINT - Samuel Gutiérrez Ramírez</p>
    </div>
</footer>
<script src="js/main.js"></script>
</body>
</html>