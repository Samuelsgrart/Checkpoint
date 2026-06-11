<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$bd = new mysqli("localhost", "root", "", "diagrama_proyecto");
$bd->set_charset("utf8");

if (!isset($_SESSION['id_usuario'])) {
    die("Error: No has iniciado sesión.");
}

$id_user = $_SESSION['id_usuario'];
$directorio = "../img/avatares/";

if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true);
}

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['avatar'];
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $nombre_final = "avatar_" . $id_user . "_" . time() . "." . $ext;
    $ruta_destino = $directorio . $nombre_final;
    $ruta_db = "img/avatares/" . $nombre_final;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        // 5. Actualizar solo la columna que ya confirmamos que existe
        $stmt = $bd->prepare("UPDATE usuarios SET avatar_url = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $ruta_db, $id_user);
        
if ($stmt->execute()) {
    $_SESSION['avatar_url'] = $ruta_db;

    $stmt->close();
    header("Location: ../perfil.php");
    exit();
        } else {
            die("Error en BD: " . $bd->error);
        }
    } else {
        die("Error: No se pudo mover el archivo a $directorio. Revisa los permisos.");
    }
} else {
    die("Error: No se subió ningún archivo o es demasiado grande.");
}
?>