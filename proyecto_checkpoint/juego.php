<?php
session_start();
require_once './php/config.php';

$id_juego = isset($_GET['id']) ? intval($_GET['id']) : 0;
$juego = null;

if ($id_juego > 0) {
    $query = "fields name, summary, cover.url, screenshots.url, total_rating,
              first_release_date, genres.name, platforms.name,
              involved_companies.developer, involved_companies.publisher,
              involved_companies.company.name;
              where id = $id_juego;";

    $resultado = consultarAPI('games', $query);
    
    if (!empty($resultado)) {
        $juego = $resultado[0];

        //BUSCA SI EL USUARIO YA TIENE UNA RESEÑA DE ESTE JUEGO
        $mi_reseña = null;
        if (isset($_SESSION['id_usuario'])) {
            $id_user = $_SESSION['id_usuario'];
            $sql_mi_rev = "SELECT * FROM reseñas WHERE id_usuario = $id_user AND id_videojuego = $id_juego LIMIT 1";
            $res_mi_rev = $bd->query($sql_mi_rev);
            if ($res_mi_rev && $res_mi_rev->num_rows > 0) {
                $mi_reseña = $res_mi_rev->fetch_assoc();
            }
        }

        //CALCULAR NOTA MEDIA DE LA WEB
        $sql_media = "SELECT AVG(nota_checkpoint) as media FROM reseñas WHERE id_videojuego = $id_juego";
        $res_media = $bd->query($sql_media);
        $fila_media = $res_media->fetch_assoc();
        $nota_media_web = $fila_media['media'] ? $fila_media['media'] : 0;

//TRAER LAS RESEÑAS REALES
$query_comentarios = "SELECT r.*, u.nombre, u.avatar_url FROM reseñas r
                      JOIN usuarios u ON r.id_usuario = u.id_usuario
                      WHERE r.id_videojuego = $id_juego 
                      AND r.estado != 'Pendiente'
                      ORDER BY r.id_reseña DESC";
$res_comentarios = $bd->query($query_comentarios);

        $comentarios_locales = [];
        if($res_comentarios) {
            while ($row = $res_comentarios->fetch_assoc()) {
                $comentarios_locales[] = $row;
            }
        }

        $developer = "No disponible"; $publisher = "No disponible";
        if (isset($juego['involved_companies'])) {
            foreach ($juego['involved_companies'] as $ic) {
                if ($ic['developer']) $developer = $ic['company']['name'];
                if ($ic['publisher']) $publisher = $ic['company']['name'];
            }
        }

        $img_portada = isset($juego['cover']['url']) ? "https:" . str_replace('t_thumb', 't_cover_big', $juego['cover']['url']) : "img/no-cover.png";
        $img_fondo = isset($juego['screenshots'][0]['url']) ? "https:" . str_replace('t_thumb', 't_1080p', $juego['screenshots'][0]['url']) : "";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHECKPOINT - <?php echo isset($juego['name']) ? htmlspecialchars($juego['name']) : 'Juego'; ?></title>
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

<main class="contenedor-juego" style="--bg-image: url('<?php echo $img_fondo; ?>');">
    <?php if (isset($_GET['error']) && $_GET['error'] == 'limite_favs'): ?>
    <div style="background: #ff4444; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-family: var(--fuente__secundaria); text-transform: uppercase; font-weight: bold;">
        ⚠️ Ya tienes 4 favoritos. Quita uno desde tu perfil para añadir este.
    </div>
<?php endif; ?>
<div class="contenedor grid-juego-detalle">
        
        <aside class="col-izquierda">
            <img src="<?php echo $img_portada; ?>" alt="Portada" class="portada-grande">
            <div class="ficha-tecnica">
                <p><strong>GÉNERO</strong><br><?php echo implode(', ', array_column($juego['genres'] ?? [], 'name')); ?></p>
                <p><strong>PLATAFORMAS</strong><br><?php echo implode(', ', array_column($juego['platforms'] ?? [], 'name')); ?></p>
                
                <div class="notas-contenedor-flex">
                    <div class="nota-item">
                        <span class="texto-nota">NOTA IGDB</span>
                        <div class="circulo-nota">
                            <?php echo isset($juego['total_rating']) ? round($juego['total_rating']) : '--'; ?>
                        </div>
                    </div>
                    <div class="nota-item">
                        <span class="texto-nota">CHECKPOINT</span>
                        <div class="circulo-nota nota-comunidad">
                            <?php echo ($nota_media_web > 0) ? number_format($nota_media_web, 1) : '--'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <section class="col-central">
            <h2 class="titulo-principal"><?php echo htmlspecialchars($juego['name']); ?> <span>(<?php echo isset($juego['first_release_date']) ? date('Y', $juego['first_release_date']) : ''; ?>)</span></h2>
            <div class="empresas">
                <p>Desarrollado por: <span><?php echo $developer; ?></span></p>
                <p>Distribuido por: <span><?php echo $publisher; ?></span></p>
            </div>
            <div class="resumen-breve">
                <h3>Sinopsis</h3>
                <p><?php
                    $resumen = $juego['summary'] ?? 'Sin descripción.';
                    echo nl2br(htmlspecialchars(mb_strimwidth($resumen, 0, 350, "..."))); 
                ?></p>
            </div>
        </section>

<aside class="col-derecha">
            <div class="caja-interaccion">
                <h3>Tu Checkpoint</h3>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <form action="php/guardar.php" method="POST">
                        <input type="hidden" name="id_juego" value="<?php echo $id_juego; ?>">
                        <input type="hidden" name="nombre_juego" value="<?php echo htmlspecialchars($juego['name']); ?>">
                        <input type="hidden" name="portada_url" value="<?php echo isset($juego['cover']['url']) ? $juego['cover']['url'] : ''; ?>">
                        <input type="hidden" name="nota_igdb" value="<?php echo isset($juego['total_rating']) ? $juego['total_rating'] : 0; ?>">
                <div id="seccion-puntuacion">
                        <label class="label-interaccion">Tu Nota</label>
                        <div class="rating-estrellas-half">
                            <?php
                            $nota_guardada = isset($mi_reseña) ? ($mi_reseña['nota_checkpoint'] * 2) : 0;
                            for($i=10; $i>=1; $i--):
                                $tipo = ($i % 2 == 0) ? 'full' : 'half';
                                $checked = ($nota_guardada == $i) ? 'checked' : '';
                            ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="puntuacion" value="<?php echo $i/2; ?>" <?php echo $checked; ?> />
                                <label class="<?php echo $tipo; ?>" for="star<?php echo $i; ?>" title="<?php echo $i/2; ?> estrellas"></label>
                            <?php endfor; ?>
                        </div>

                        <div class="favorito-btn">
                            <input type="checkbox" name="favorito" id="fav" <?php echo (isset($mi_reseña) && $mi_reseña['favorito']) ? 'checked' : ''; ?>>
                            <label for="fav" class="corazon-label">
                                <span class="texto-fav">FAVORITO</span>
                                <span class="icono-corazon">❤</span>
                            </label>
                        </div>
                </div>

<div class="form-group" style="margin-bottom: 1.5rem;">
    <label class="label-interaccion" style="display: block; margin-bottom: 0.5rem; color: var(--rosa); font-weight: bold;">
        <i class="fas fa-gamepad"></i> Tu estado con este juego:
    </label>
    <select name="estado" id="estado-juego" onchange="verificarEstado()" class="textarea-bio" style="min-height: auto; padding: 1rem; width: 100%; cursor: pointer;">
        <?php $est = $mi_reseña['estado'] ?? 'Completado'; ?>
        <option value="Completado" <?php echo ($est == 'Completado') ? 'selected' : ''; ?>>🏆 Completado</option>
        <option value="Jugando" <?php echo ($est == 'Jugando') ? 'selected' : ''; ?>>🎮 Jugando actualmente</option>
        <option value="Pendiente" <?php echo ($est == 'Pendiente') ? 'selected' : ''; ?>>⏳ Pendiente</option>
        <option value="Abandonado" <?php echo ($est == 'Abandonado') ? 'selected' : ''; ?>>❌ Abandonado</option>
    </select>
</div>

<div>
    <label class="label-interaccion" style="display: block; margin-bottom: 0.5rem; color: var(--rosa); font-weight: bold;">
        <i class="fas fa-edit"></i> Tu reseña:
    </label>
    <textarea name="comentario" id="comentario-area" placeholder="Escribe tu reseña..."><?php echo isset($mi_reseña) ? htmlspecialchars($mi_reseña['comentario']) : ''; ?></textarea>
</div>
                        <button type="submit" class="btn-primario btn-full">
                            <?php echo isset($mi_reseña) ? 'Actualizar' : 'Publicar'; ?>
                        </button>
                    </form>

                    <?php if (isset($mi_reseña)): ?>
                        <form action="php/borrar_reseña.php" method="POST" class="form-borrar" onsubmit="return confirm('¿Seguro que quieres borrar tu reseña?');">
                            <input type="hidden" name="id_juego" value="<?php echo $id_juego; ?>">
                            <button type="submit" class="btn-secundario btn-full btn-borrar">Borrar Reseña</button>
                        </form>
                    <?php endif; ?>

                <?php else: ?>
                    <p class="login-prompt">Inicia sesión para puntuar este juego.</p>
                    <a href="login.php" class="btn-secundario btn-block">Login</a>
                <?php endif; ?>
            </div>
        </aside>
    </div>

<section class="contenedor seccion-comentarios">
    <h3 class="titulo-seccion">Reseñas de la comunidad</h3>
    <div class="grid-comentarios-local">
        <?php if (!empty($comentarios_locales)): ?>
            <?php foreach ($comentarios_locales as $com): ?>
                <a href="perfil.php?id=<?php echo $com['id_usuario']; ?>" class="card-link" style="text-decoration: none; color: inherit; display: block;">
                    <article class="card-comentario" style="transition: transform 0.2s, border-color 0.2s; cursor: pointer;">
                        <div class="card-comentario-header">
                            <div style="display: flex; align-items: center; gap: 1rem;">
            <?php
    // Si tiene foto en BD la usa, si no, el avatar de letras rosa
    $foto_comentario = !empty($com['avatar_url'])
        ? $com['avatar_url'] 
        : "https://ui-avatars.com/api/?name=".urlencode($com['nombre'])."&background=F3456B&color=fff&size=50";
?>
<img src="<?php echo $foto_comentario; ?>" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover; border: 2px solid var(--rosa);" alt="Avatar de <?php echo htmlspecialchars($com['nombre']); ?>">
                                <strong><?php echo htmlspecialchars($com['nombre']); ?></strong>
                            </div>
                            <span class="nota-user-comentario">
                                <?php echo number_format($com['nota_checkpoint'], 1); ?>
                            </span>
                        </div>
                        
                        <?php if($com['favorito']): ?>
                            <span class="badge-favorito">❤️ FAVORITO</span>
                        <?php endif; ?>
                        
                        <p class="comentario-texto-comunidad">"<?php echo nl2br(htmlspecialchars($com['comentario'])); ?>"</p>
                    </article>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-state-simple">Aún no hay reseñas. ¡Sé el primero!</p>
        <?php endif; ?>
    </div>
</section>
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