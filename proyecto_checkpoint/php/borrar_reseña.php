<?php
session_start();
include 'config.php';

if (isset($_SESSION['id_usuario']) && isset($_POST['id_juego'])) {
    $id_user = $_SESSION['id_usuario'];
    $id_juego = intval($_POST['id_juego']);

    $sql = "DELETE FROM reseñas WHERE id_usuario = ? AND id_videojuego = ?";
    $stmt = $bd->prepare($sql);
    $stmt->bind_param("ii", $id_user, $id_juego);

    if ($stmt->execute()) {
        header("Location: ../juego.php?id=$id_juego&status=borrado");
    } else {
        echo "Error al borrar";
    }
    $stmt->close();
}