<?php
// backend/db.php
require_once 'config.php';

function supabase_request($endpoint, $method = 'GET', $data = null, $token = null) {
    // Verificar que la extensión cURL esté disponible para evitar errores fatales en HTML
    if (!function_exists('curl_init')) {
        return [
            'error' => 'La extensión cURL de PHP no está habilitada. Habilítala en php.ini para que el CRUD de productos funcione.',
            'status' => 500
        ];
    }

    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . ($token ? $token : SUPABASE_KEY),
        'Content-Type: application/json',
        'Prefer: return=representation' // Para que devuelva los datos insertados/actualizados
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        return ['error' => curl_error($ch)];
    }
    
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if ($http_code >= 400) {
        return ['error' => $decoded_response, 'status' => $http_code];
    }

    return $decoded_response;
}
?>
