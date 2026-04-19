<?php
function verificarLogros($id_usuario, $bd) {
    $criticas = $bd->query("SELECT COUNT(*) as total FROM reseñas WHERE id_usuario = $id_usuario")->fetch_assoc()['total'];
    $favoritos = $bd->query("SELECT COUNT(*) as total FROM reseñas WHERE id_usuario = $id_usuario AND favorito = 1")->fetch_assoc()['total'];

    $user_query = $bd->query("SELECT descripcion, avatar_url, fecha_registro FROM usuarios WHERE id_usuario = $id_usuario")->fetch_assoc();
    
    $notas_max = $bd->query("SELECT COUNT(*) as total FROM reseñas WHERE id_usuario = $id_usuario AND nota_checkpoint = 5")->fetch_assoc()['total'];
    
    $notas_min = $bd->query("SELECT COUNT(*) as total FROM reseñas WHERE id_usuario = $id_usuario AND nota_checkpoint <= 1")->fetch_assoc()['total'];

    $fecha_reg = new DateTime($user_query['fecha_registro'] ?? 'now');
    $hoy = new DateTime();
    $dias_antiguedad = $hoy->diff($fecha_reg)->days;

    $sql = "SELECT * FROM logros WHERE id_logro NOT IN (
                SELECT id_logro FROM usuarios_logros WHERE id_usuario = ?
            )";
    $stmt = $bd->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $posibles_logros = $stmt->get_result();

    while ($logro = $posibles_logros->fetch_assoc()) {
        $ganado = false;
        $condicion = $logro['clave_condicion'];
        $meta = $logro['valor_requerido'];

        switch ($condicion) {
            case 'total_criticas':
                if ($criticas >= $meta) $ganado = true;
                break;

            case 'total_favs':
                if ($favoritos >= $meta) $ganado = true;
                break;

            case 'bio_completa':
                if (!empty($user_query['descripcion']) && strlen(trim($user_query['descripcion'])) > 10) {
                    $ganado = true;
                }
                break;

            case 'multi_top':
                if ($notas_max >= $meta) $ganado = true;
                break;

            case 'nota_baja':
                if ($notas_min >= $meta) $ganado = true;
                break;

            case 'antiguedad':
                if ($dias_antiguedad >= $meta) $ganado = true;
                break;
                
            case 'avatar_personalizado':
                $foto = $user_query['avatar_url'] ?? '';

                if (!empty($foto) && strpos($foto, 'ui-avatars.com') === false) {
                    $ganado = true;
                }
                break;
        }

if ($ganado) {
            $stmt_ins = $bd->prepare("INSERT INTO usuarios_logros (id_usuario, id_logro) VALUES (?, ?)");
            $stmt_ins->bind_param("ii", $id_usuario, $logro['id_logro']);
            
            if ($stmt_ins->execute()) {
                $_SESSION['nuevo_logro'] = $logro['nombre'];
            }
            
            $stmt_ins->close();
            break;
        }
    }
    $stmt->close();
}
?>