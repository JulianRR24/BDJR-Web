<?php
// backend/auth.php
require_once 'config.php';

// Helper to make Auth API requests
function supabase_auth_request($endpoint, $data) {
    $url = SUPABASE_URL . '/auth/v1/' . $endpoint;
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    file_put_contents('debug_auth.log', date('Y-m-d H:i:s') . " Supabase Response ($http_code): " . $response . "\n", FILE_APPEND);
    return ['data' => $decoded, 'status' => $http_code];
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

// Debug logging - Use a fixed path or check if writable
$logFile = __DIR__ . '/debug_auth.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " Action: $action Input: " . json_encode($input) . "\n", FILE_APPEND);


if ($action === 'register') {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password required']);
        exit;
    }

    $result = supabase_auth_request('signup', ['email' => $email, 'password' => $password]);
    
    http_response_code($result['status']);
    echo json_encode($result['data']);

} elseif ($action === 'login') {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Email and password required',
            'debug_input' => $input,
            'debug_action' => $action
        ]);
        exit;
    }

    $result = supabase_auth_request('token?grant_type=password', ['email' => $email, 'password' => $password]);

    // Force 200 OK to allow frontend to read the JSON error details
    // http_response_code($result['status']); 
    http_response_code(200); 
    
    echo json_encode([
        'data' => $result['data'],
        'debug_supabase_status' => $result['status'],
        'debug_supabase_response' => $result['data']
    ]);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action', 'debug_action' => $action]);
}
?>
