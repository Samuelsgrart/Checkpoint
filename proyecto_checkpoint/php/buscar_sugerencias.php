<?php
include './config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
        echo json_encode([]);
        exit;
    }

    $busqueda = trim($_GET['q']);
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 15) : 8;

    $query = 'search "' . addslashes($busqueda) . '";
              fields name, id, cover.url, total_rating, total_rating_count, first_release_date;
              where version_parent = null & parent_game = null;
              limit ' . $limit . ';';

    $sugerencias = consultarAPI('games', $query);

    if (empty($sugerencias) || !is_array($sugerencias)) {
        echo json_encode([]);
        exit;
    }

    $resultados = array_map(function($juego) {
        return [
            'id' => $juego['id'],
            'name' => $juego['name'],
            'cover' => isset($juego['cover']['url']) ? 'https:' . $juego['cover']['url'] : null,
            'rating' => $juego['total_rating'] ?? null,
            'rating_count' => $juego['total_rating_count'] ?? 0,
            'year' => isset($juego['first_release_date'])
                ? date('Y', $juego['first_release_date'])
                : null
        ];
    }, $sugerencias);

    usort($resultados, function($a, $b) {
        $scoreA = ($a['rating_count'] * 0.7) + (($a['rating'] ?? 0) * 0.3);
        $scoreB = ($b['rating_count'] * 0.7) + (($b['rating'] ?? 0) * 0.3);
        return $scoreB <=> $scoreA;
    });

    echo json_encode($resultados);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la búsqueda']);
    error_log("Error buscar_sugerencias: " . $e->getMessage());
}
?>