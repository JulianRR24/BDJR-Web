<?php
// backend/create_preference.php
require_once 'config.php';

// Check for Composer autoloader in common locations
$autoloadPaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php'
];

$autoloadFound = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    http_response_code(500);
    echo json_encode(['error' => 'Mercado Pago SDK not found. Please run "composer require mercadopago/dx-php" in the backend directory.', 'debug_paths' => $autoloadPaths]);
    exit;
}

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

header('Content-Type: application/json');

// 1. Configure SDK
MercadoPagoConfig::setAccessToken("APP_USR-2434854249578165-120414-4f28f0ee978d00bcc2f68521c0811527-3040725718");

// 2. Get Input Data (Cart Items)
$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];
$user = $input['user'] ?? [];

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'No items provided']);
    exit;
}

// 3. Build Preference Items
$preferenceItems = [];
foreach ($items as $item) {
    $preferenceItems[] = [
        "title" => $item['name'],
        "quantity" => (int)$item['quantity'],
        "unit_price" => (float)$item['price'],
        "currency_id" => "COP"
    ];
}

// 4. Create Preference
$client = new PreferenceClient();
try {
    $preference = $client->create([
        "items" => $preferenceItems,
        "payer" => [
            "email" => $user['email'] ?? 'test_user_123@testuser.com', // Fallback para pruebas
        ],
        "back_urls" => [
            "success" => "http://localhost/bdjr-web/dashboard.html?status=success",
            "failure" => "http://localhost/bdjr-web/cart.html?status=failure",
            "pending" => "http://localhost/bdjr-web/cart.html?status=pending"
        ],
        // auto_return no es necesario para Wallet Brick; lo quitamos para evitar invalid_auto_return
    ]);

    echo json_encode(['id' => $preference->id]);

} catch (MPApiException $e) {
    // Errores devueltos por la API de Mercado Pago (datos inválidos, credenciales, etc.)
    $apiResponse = $e->getApiResponse();

    // MPResponse es un objeto, no un array
    $statusCode = method_exists($apiResponse, 'getStatusCode')
        ? $apiResponse->getStatusCode()
        : null;

    $body = method_exists($apiResponse, 'getContent')
        ? $apiResponse->getContent()
        : null;

    http_response_code(500);
    echo json_encode([
        'error'   => 'Mercado Pago API error',
        'status'  => $statusCode,
        'body'    => $body,
        'message' => $e->getMessage(),
    ]);
} catch (Exception $e) {
    // Otros errores de PHP
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
    ]);
}
?>
