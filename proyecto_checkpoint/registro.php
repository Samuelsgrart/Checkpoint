<?php
include './php/config.php';

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];

    if (strlen($password_raw) < 8 || !preg_match("/[A-Za-z]/", $password_raw) || !preg_match("/[0-9]/", $password_raw)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, letras y números.";
        $tipo_mensaje = "error";
    } else {
        $stmt_check = $bd->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $mensaje = "Este correo ya está registrado. Intenta con otro.";
            $tipo_mensaje = "error";
        } else {
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')";
            $stmt = $bd->prepare($sql);
            $stmt->bind_param("sss", $nombre, $email, $password_hash);

            if ($stmt->execute()) {
                $mensaje = "¡Registro con éxito! Ya puedes iniciar sesión.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al registrar: " . $bd->error;
                $tipo_mensaje = "error";
            }
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - CHECKPOINT</title>
        <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="body-login">
    <div class="contenedor-formulario">
        <a href="index.php" class="logo-link"><h1 class="logo">CHECK<span>POINT</span></h1></a>
        
        <form method="POST" class="form-checkpoint">
            <h2>Crear Cuenta</h2>

            <?php if ($mensaje !== ""): ?>
                <p style="color: <?php echo ($tipo_mensaje === 'success') ? '#2ecc71' : '#ff4d4d'; ?>;
                          background: rgba(0,0,0,0.2); padding: 10px; border-radius: 5px; font-size: 1.4rem; margin-bottom: 2rem;">
                    <?php echo $mensaje; ?>
                    <?php if ($tipo_mensaje === 'success'): ?>
                        <br><a href="login.php" style="color: white; font-weight: bold; text-decoration: underline;">Ir al Login</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <div class="campo">
                <input type="text" name="nombre" placeholder="Nombre de usuario" required>
            </div>
            <div class="campo">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="campo">
                <input type="password" name="password" placeholder="Contraseña (letras y números)" required minlength="8">
            </div>
            
            <button type="submit" class="btn-registro">Registrarme</button>
        </form>
        
        <p class="enlace-alternativo">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</body>
</html>