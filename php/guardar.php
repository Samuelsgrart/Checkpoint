<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id_usuario'])) {
    die("Debes iniciar sesión para valorar juegos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = (int)$_SESSION['id_usuario'];
    $id_juego_igdb = (int)$_POST['id_juego'];
    
    $nombre_videojuego = isset($_POST['nombre_juego']) ? $bd->real_escape_string($_POST['nombre_juego']) : 'Juego desconocido';
    $portada_url = isset($_POST['portada_url']) ? $bd->real_escape_string($_POST['portada_url']) : '';

    $nota_igdb = isset($_POST['nota_igdb']) ? (int)round($_POST['nota_igdb']) : 0;
    $puntuacion = (float)$_POST['puntuacion'];
    $comentario = $bd->real_escape_string($_POST['comentario']);
    $es_favorito = isset($_POST['favorito']) ? 1 : 0;

    $estado = isset($_POST['estado']) ? $bd->real_escape_string($_POST['estado']) : 'Completado';

if ($estado === 'Pendiente') {
    $puntuacion = 0;
    $comentario = "";
    $es_favorito = 0;
} else {
    $puntuacion = isset($_POST['puntuacion']) ? (float)$_POST['puntuacion'] : 0;
    $comentario = isset($_POST['comentario']) ? $bd->real_escape_string($_POST['comentario']) : '';
    $es_favorito = isset($_POST['favorito']) ? 1 : 0;
}
    if ($es_favorito == 1) {
        $stmt_check = $bd->prepare("SELECT COUNT(*) as total FROM reseñas WHERE id_usuario = ? AND favorito = 1 AND id_videojuego != ?");
        $stmt_check->bind_param("ii", $id_user, $id_juego_igdb);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result()->fetch_assoc();

        if ($res_check['total'] >= 4) {
            // Si ya tiene 4 en otros juegos, redirigimos con error
            header("Location: ../juego.php?id=$id_juego_igdb&error=limite_favs");
            exit();
        }
    }

    $sql = "INSERT INTO reseñas (id_usuario, id_videojuego, nombre_videojuego, portada_url, nota_igdb, comentario, nota_checkpoint, favorito, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            comentario = VALUES(comentario),
            nota_checkpoint = VALUES(nota_checkpoint),
            favorito = VALUES(favorito),
            nota_igdb = VALUES(nota_igdb),
            portada_url = VALUES(portada_url),
            estado = VALUES(estado)";

    $stmt = $bd->prepare($sql);
    $tipos = "iissisdis";
    $stmt->bind_param($tipos, $id_user, $id_juego_igdb, $nombre_videojuego, $portada_url, $nota_igdb, $comentario, $puntuacion, $es_favorito, $estado);

    if ($stmt->execute()) {
        if (file_exists('logros.php')) {
            include_once 'logros.php';
            verificarLogros($id_user, $bd);
        }

        header("Location: ../juego.php?id=$id_juego_igdb&status=ok");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}