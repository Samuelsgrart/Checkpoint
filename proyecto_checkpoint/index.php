<?php
session_start();
require_once './php/config.php';

$juegos_portada = [];
$proximos_juegos = [];
$hoy = time();

$archivo_cache = './php/cache_igdb.json';
$tiempo_cache = 86400;

if (file_exists($archivo_cache) && (time() - filemtime($archivo_cache) < $tiempo_cache)) {
    $datos_cache = json_decode(file_get_contents($archivo_cache), true);
    $juegos_portada = $datos_cache['tops'] ?? [];
    $proximos_juegos = $datos_cache['proximos'] ?? [];
} else {
    if (function_exists('consultarAPI')) {
        $hace_seis_meses = $hoy - (180 * 24 * 60 * 60);

        $query_tops = "fields name, cover.url, first_release_date, total_rating, total_rating_count, hypes, follows;
                       where cover != null
                       & first_release_date <= $hoy
                       & first_release_date >= $hace_seis_meses
                       & platforms = (6, 130, 167, 169)
                       & (total_rating_count > 10 | hypes > 10 | follows > 10)
                       & version_parent = null;
                       sort first_release_date desc;
                       limit 8;";
        $juegos_portada = consultarAPI('games', $query_tops);

        $query_proximos = "fields name, cover.url, first_release_date, hypes, total_rating_count;
                           where first_release_date > $hoy
                           & cover != null
                           & platforms = (6, 167, 169, 130)
                           & (hypes > 5 | total_rating_count > 2);
                           sort first_release_date asc;
                           limit 4;";
        $proximos_juegos = consultarAPI('games', $query_proximos);

        if (!empty($juegos_portada) || !empty($proximos_juegos)) {
            file_put_contents($archivo_cache, json_encode([
                'tops' => $juegos_portada,
                'proximos' => $proximos_juegos
            ]));
        }
    }
}

//FUNCION AUXILIAR
if (!function_exists('getCoverURL')) {
    function getCoverURL($url) {
        if (empty($url)) return "https://via.placeholder.com/264x352?text=Sin+Portada";
        return "https:" . str_replace('t_thumb', 't_cover_big', $url);
    }
}

//CONSULTA RESEÑAS
$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;

if ($id_usuario_sesion) {
    $sql_reviews = "SELECT r.*, u.nombre
                    FROM reseñas r
                    JOIN usuarios u ON r.id_usuario = u.id_usuario
                    JOIN seguidores s ON r.id_usuario = s.id_seguido
                    WHERE s.id_seguidor = $id_usuario_sesion
                    AND r.estado != 'Pendiente'
                    ORDER BY r.id_reseña DESC LIMIT 10";
    
    $res_reviews = $bd->query($sql_reviews);

    if ($res_reviews->num_rows == 0) {
        $res_reviews = $bd->query("SELECT r.*, u.nombre FROM reseñas r JOIN usuarios u ON r.id_usuario = u.id_usuario WHERE r.estado != 'Pendiente' ORDER BY r.id_reseña DESC LIMIT 6");
        $titulo_feed = "Últimas Críticas Globales";
    } else {
        $titulo_feed = "Actividad de tus amigos";
    }
} else {
    $res_reviews = $bd->query("SELECT r.*, u.nombre FROM reseñas r JOIN usuarios u ON r.id_usuario = u.id_usuario WHERE r.estado != 'Pendiente' ORDER BY r.id_reseña DESC LIMIT 6");
    $titulo_feed = "Últimas Críticas";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHECKPOINT - Home</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="bg-main">
<header class="header-principal">
    <div class="contenedor contenido-header">
        <a href="index.php" class="logo-link">
            <h1 class="logo">CHECK<span>POINT</span></h1>
        </a>

        <div class="buscador-container">
            <input type="text" id="input-busqueda" placeholder="Buscar juegos..." autocomplete="off">
            <div id="sugerencias" class="sugerencias-box"></div>
        </div>

<nav class="user-nav">
    <?php if (isset($_SESSION['nombre'])): ?>
        <div class="user-info-header">
            <a href="perfil.php" class="user-profile-data">
                <?php 
        $avatar_header = !empty($_SESSION['avatar_url']) 
            ? $_SESSION['avatar_url'] 
            : "https://ui-avatars.com/api/?name=".urlencode($_SESSION['nombre'])."&background=F3456B&color=fff&size=40";
    ?>
    <img src="<?php echo $avatar_header; ?>" alt="Perfil" class="img-perfil-header">
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

<main class="contenedor">
<section id="destacados">
    <h2 class="titulo-seccion">Top Juegos del Momento</h2>
    <div class="grid-juegos">
        <?php if (is_array($juegos_portada) && !empty($juegos_portada)): ?>
            <?php foreach ($juegos_portada as $juego):
                $img = "https://via.placeholder.com/264x352?text=Sin+Imagen";
                if (isset($juego['cover']['url'])) {
                    $img = "https:" . str_replace('t_thumb', 't_cover_big', $juego['cover']['url']);
                }
            ?>
            <article class="card">
                <a href="juego.php?id=<?php echo $juego['id']; ?>" class="card-link">

                    <?php if (isset($juego['total_rating'])): ?>
                        <div class="badge-rating">
                            <?php echo round($juego['total_rating'] / 10, 1); ?>
                        </div>
                    <?php endif; ?>

                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($juego['name']); ?>" loading="lazy">

                    <div class="card__contenido">
                        <h3><?php echo htmlspecialchars($juego['name']); ?></h3>
                        <span class="btn-ver-mas">Ver detalles</span>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="error-api">No se han podido cargar los juegos destacados.</p>
        <?php endif; ?>
    </div>
</section>

<?php

//LOGICA LANZAMIENTOS
$hoy = time();
$query_proximos = "fields name, cover.url, first_release_date, hypes;
                   where first_release_date > $hoy 
                   & cover != null 
                   & platforms = (6, 167, 169, 130) 
                   & hypes > 50; 
                   sort first_release_date asc;
                   limit 4;";

$proximos_juegos = consultarAPI('games', $query_proximos);

if (!function_exists('getCoverURL')) {
    function getCoverURL($url) {
        if (empty($url)) return "https://via.placeholder.com/264x352?text=Sin+Portada";
        return "https:" . str_replace('t_thumb', 't_cover_big', $url);
    }
}
?>

<section class="contenedor" style="margin-top: 8rem; margin-bottom: 8rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <h3 class="titulo-seccion" style="margin: 0;">Próximos Lanzamientos</h3>
        <a href="lanzamientos.php" class="btn-ver-mas" style="background: var(--rosa); padding: 0.8rem 1.5rem; border-radius: 0.5rem; color: white; font-size: 1.4rem;">Ver calendario completo</a>
    </div>

    <div class="grid-juegos">
        <?php if (!empty($proximos_juegos)): ?>
            <?php foreach ($proximos_juegos as $pj): 
                $foto = isset($pj['cover']['url']) ? getCoverURL($pj['cover']['url']) : "https://via.placeholder.com/264x352?text=No+Image";
                $fecha = isset($pj['first_release_date']) ? date("d M", $pj['first_release_date']) : "TBD";
            ?>
                <article class="card">
                    <a href="juego.php?id=<?php echo $pj['id']; ?>" class="card-link">
                        <div class="badge-rating" style="background: #3498db; width: auto; padding: 0 1.2rem; font-size: 1.2rem; border-radius: 5px;">
                            <?php echo $fecha; ?>
                        </div>
                        <img src="<?php echo $foto; ?>" alt="<?php echo htmlspecialchars($pj['name']); ?>" loading="lazy">
                        <div class="card__contenido">
                            <h3 style="font-size: 1.6rem; margin-bottom: 1.5rem; height: 3.8rem; overflow: hidden;">
                                <?php echo htmlspecialchars($pj['name']); ?>
                            </h3>
                            <div style="text-align: center;">
                                <span class="btn-ver-mas">Ver Ficha</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; opacity: 0.5;">Cargando lanzamientos...</p>
        <?php endif; ?>
    </div>
</section>

<section class="reseñas-home">
    <h2 class="titulo-seccion"><?php echo $titulo_feed; ?></h2>
    <div class="grid-reseñas">
        <?php if ($res_reviews && $res_reviews->num_rows > 0): ?>
            <?php while($rev = $res_reviews->fetch_assoc()): ?>
                <article class="review-mini">
                    <div class="review-header">
                        <span class="review-user"><?php echo htmlspecialchars($rev['nombre']); ?></span>
                        <span class="review-separator">está <?php echo $rev['estado'] ?? 'reseñando'; ?></span>
                        <a href="juego.php?id=<?php echo $rev['id_videojuego']; ?>" class="link-juego-review">
                            <?php echo htmlspecialchars($rev['nombre_videojuego']); ?>
                        </a>
                    </div>
                    
                    <p class="comentario-txt">"<?php echo htmlspecialchars($rev['comentario']); ?>"</p>
                    
                    <div class="review-scores">
                        <div class="score-item igdb-mini">
                            <span class="label">IGDB</span>
                            <span class="valor">
                                <?php echo ($rev['nota_igdb'] > 0) ? round($rev['nota_igdb']) : '--'; ?>
                            </span>
                        </div>
                        <div class="score-item check-mini">
                            <span class="label">CHECK</span>
                            <span class="valor">
                                <?php echo number_format($rev['nota_checkpoint'], 1); ?>
                            </span>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state-simple">
                <p>Aún no hay reseñas en la comunidad. ¡Sé el primero!</p>
            </div>
        <?php endif; ?>
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