<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

include './php/config.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id_usuario, nombre, password, avatar_url FROM usuarios WHERE email = ?";
    $stmt = $bd->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($user = $resultado->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            
            $_SESSION['avatar_url'] = $user['avatar_url'];
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "El usuario no existe";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - CHECKPOINT</title>
        <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="body-login">
    <div class="contenedor-formulario">
        <a href="index.php" class="logo-link"><h1 class="logo">CHECK<span>POINT</span></h1></a>
        <form method="POST" class="form-checkpoint">
            <h2>Iniciar Sesión</h2>

            <?php if ($error): ?>
                <p style="color: #ff4d4d; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 5px; font-size: 1.4rem;">
                    <?php echo $error; ?>
                </p>
            <?php endif; ?>

            <div class="campo">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="campo">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn-registro">Entrar</button>
        </form>
        <p class="enlace-alternativo">¿Aún no tienes cuenta? <a href="registro.php">Regístrate gratis</a></p>
    </div>
</body>
</html>