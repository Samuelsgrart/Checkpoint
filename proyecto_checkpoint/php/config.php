<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$client_id = "PON TU KEY;";
$client_secret = "PON TU KEY;

//API
function consultarAPI($endpoint, $query) {
    global $client_id, $client_secret;

    $auth_url = "https://id.twitch.tv/oauth2/token?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials";
    
    $ch_auth = curl_init();
    curl_setopt($ch_auth, CURLOPT_URL, $auth_url);
    curl_setopt($ch_auth, CURLOPT_POST, 1);
    curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_auth, CURLOPT_SSL_VERIFYPEER, false);
    
    $auth_res = json_decode(curl_exec($ch_auth), true);
    curl_close($ch_auth);
    
    $token = $auth_res['access_token'] ?? null;

    if (!$token) return ["status" => "error", "message" => "No se pudo generar el token"];

    $url = "https://api.igdb.com/v4/" . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Client-ID: $client_id",
        "Authorization: Bearer $token",
        "Content-Type: text/plain"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

$bd = new mysqli("localhost", "root", "", "proyecto_checkpoint");
$bd->set_charset("utf8");

$res_reviews = $bd->query("SELECT r.*, u.nombre, u.avatar_url FROM reseñas r JOIN usuarios u ON r.id_usuario = u.id_usuario ORDER BY r.id_reseña DESC LIMIT 5");
?>