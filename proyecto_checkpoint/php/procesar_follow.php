<?php
include('config.php');
session_start();

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$id_seguido = intval($_GET['id']);
$id_seguidor = $_SESSION['id_usuario'];
$accion = $_GET['accion'];

if ($accion == 'follow') {
    $bd->query("INSERT IGNORE INTO seguidores (id_seguidor, id_seguido) VALUES ($id_seguidor, $id_seguido)");
} else {
    $bd->query("DELETE FROM seguidores WHERE id_seguidor = $id_seguidor AND id_seguido = $id_seguido");
}

header("Location: ../perfil.php?id=" . $id_seguido);
exit();
?>