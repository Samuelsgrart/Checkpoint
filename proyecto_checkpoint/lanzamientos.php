<?php
session_start();
require_once './php/config.php';

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
$hoy = time();

//LOGICA PAGINACION
$limit = 24; 
$pagina = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($pagina - 1) * $limit;

if (!function_exists('getCoverURL')) {
    function getCoverURL($url, $size = 't_cover_big') {
        if (empty($url)) return "https://via.placeholder.com/264x352?text=Sin+Portada";
        return "https:" . str_replace('t_thumb', $size, $url);
    }
}

//CONSULTA API
$proximos_juegos = [];
if (function_exists('consultarAPI')) {
$query = "fields name, cover.url, first_release_date, release_dates.date, release_dates.category;
              where first_release_date > $hoy
              & cover != null
              & platforms = (6, 167, 169, 130)
              & (hypes > 10 | category = 0);
              sort first_release_date asc;
              limit $limit;
              offset $offset;";
    $proximos_juegos = consultarAPI('games', $query);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHECKPOINT - LANZAMIENTOS</title>
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
<main class="contenedor lanzamientos-main">
    <div class="lanzamientos-header">
        <h3 class="titulo-seccion">Próximos Lanzamientos</h3>
    </div>

    <div class="grid-juegos">
        <?php if (!empty($proximos_juegos)): ?>
            <?php foreach ($proximos_juegos as $pj):
                $foto = isset($pj['cover']['url']) ? getCoverURL($pj['cover']['url']) : "https://via.placeholder.com/264x352?text=No+Image";
                
                if (isset($pj['first_release_date'])) {
                    $timestamp = (int)$pj['first_release_date'];
                    
                    //MIRA SI ES 31 DE DICIEMBRE
                    $dia_mes = date("d M", $timestamp);
                    $anio = date("Y", $timestamp);

                    if ($dia_mes == "31 Dec") {
                        $fecha_final = "AÑO " . $anio;
                    } else {
                        $fecha_final = date("d M Y", $timestamp);
                    }
                } else {
                    $fecha_final = "TBD";
                }
            ?>
                <article class="card">
                    <a href="juego.php?id=<?php echo $pj['id']; ?>" class="card-link">
                        <div class="badge-lanzamiento">
                            <?php echo $fecha_final; ?>
                        </div>

                        <img src="<?php echo $foto; ?>" alt="<?php echo htmlspecialchars($pj['name']); ?>" loading="lazy">
                        
                        <div class="card__contenido">
                            <h3 class="card__titulo-juego">
                                <?php echo htmlspecialchars($pj['name']); ?>
                            </h3>
                            
                            <div class="card__footer">
                                <span class="btn-ver-mas">Ver Ficha</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>No hay juegos que mostrar.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="paginacion">
        <?php if ($pagina > 1): ?>
            <a href="?p=<?php echo $pagina - 1; ?>" class="btn-paginacion btn-anterior">
                <i class="fas fa-arrow-left"></i> Anterior
            </a>
        <?php endif; ?>

        <span class="pagina-actual">
            PÁGINA <?php echo $pagina; ?>
        </span>

        <?php if (count($proximos_juegos) == $limit): ?>
            <a href="?p=<?php echo $pagina + 1; ?>" class="btn-paginacion btn-siguiente">
                Siguiente <i class="fas fa-arrow-right"></i>
            </a>
        <?php endif; ?>
    </div>
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