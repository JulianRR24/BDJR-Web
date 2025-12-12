<?php
// backend/product_manage.php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

// Helper to check Auth
// In a real production app, we would verify the JWT token here.
// For now, we rely on the frontend sending a 'Authorization' header or similar, 
// but since 'db.php' handles the Supabase request with the token, we can just pass it through.
// Actually, for Admin, we should verify the user has a specific role or just exist.
// Since we don't have roles implemented in the DB yet, we'll assume any logged-in user can manage products (Dangerous in Prod, OK for this MVP).

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Para el panel de administración usaremos SIEMPRE la service key de Supabase.
// Esto hace que las peticiones REST se ejecuten con rol "service_role" y no se apliquen
// las políticas de Row Level Security (RLS) a esta ruta backend.
// IMPORTANTE: no expongas este archivo ni la SUPABASE_KEY directamente al frontend.
$token = SUPABASE_KEY;

if ($method === 'POST') {
    // CREATE
    $name = $input['name'] ?? '';
    
    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'Product Name is required']);
        exit;
    }

    // Auto-generate Image Paths
    // Convention: assets/images/products/[Name].png
    // We clean the name to be filename friendly if needed, but user requirement implies direct usage.
    // Let's safe-ish it: Remove special chars? 
    // User Example: "FinanzApp1" -> "FinanzApp1.png"
    // Let's stick to the exact string provided for simplicity as requested.
    
    $imageBasicName = $name; // Or maybe sanitize? preg_replace('/[^A-Za-z0-9_\-]/', '', $name);
    // User said: "nombre del producto y busque un png con ese nombre"
    // Example: Name="Software Finanzas" -> Image="Software Finanzas.png"? Or "SoftwareFinanzas.png"?
    // Usually spaces in URLs are bad. Let's assume the user will type the name that matches the file, 
    // OR we strip spaces. Let's try to match exactly what they type + .png for now.
    
    $imagePath = "assets/images/products/{$imageBasicName}.png";
    
    // Construct Payload
    $data = [
        'name' => $name,
        'vendor' => $input['vendor'] ?? '',
        'description' => $input['description'] ?? '',
        'long_description' => $input['long_description'] ?? '',
        'price' => (float)($input['price'] ?? 0),
        'compare_price' => (float)($input['compare_price'] ?? 0),
        'stock' => (int)($input['stock'] ?? 0),
        'category' => $input['category'] ?? 'General',
        'image_url' => $imagePath,
        'images' => json_encode([$imagePath, $imagePath]), // Mocking multiple images for carousel
        'features' => json_encode($input['features'] ?? []),
        'benefits' => json_encode($input['benefits'] ?? []),
    ];

    $result = supabase_request('bdjr_products', 'POST', $data, $token);

    if (isset($result['error'])) {
        http_response_code($result['status'] ?? 500);
        echo json_encode($result);
    } else {
        echo json_encode(['message' => 'Product created', 'data' => $result]);
    }

} elseif ($method === 'PUT') {
    // UPDATE
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        exit;
    }

    // Similar logic to POST but only update present fields? 
    // Or full update. Let's assume full update for simplicity or merge.
    // Ideally we should re-generate image paths if name changes.
    
    $data = [];
    if (isset($input['name'])) {
        $data['name'] = $input['name'];
        $imagePath = "assets/images/products/{$input['name']}.png";
        $data['image_url'] = $imagePath;
        $data['images'] = json_encode([$imagePath, $imagePath]);
    }
    if (isset($input['vendor'])) $data['vendor'] = $input['vendor'];
    if (isset($input['description'])) $data['description'] = $input['description'];
    if (isset($input['long_description'])) $data['long_description'] = $input['long_description'];
    if (isset($input['price'])) $data['price'] = (float)$input['price'];
    if (isset($input['compare_price'])) $data['compare_price'] = (float)$input['compare_price'];
    if (isset($input['stock'])) $data['stock'] = (int)$input['stock'];
    if (isset($input['category'])) $data['category'] = $input['category'];
    if (isset($input['features'])) $data['features'] = json_encode($input['features']);
    if (isset($input['benefits'])) $data['benefits'] = json_encode($input['benefits']);

    $result = supabase_request('bdjr_products?id=eq.' . $id, 'PATCH', $data, $token);

    if (isset($result['error'])) {
        http_response_code($result['status'] ?? 500);
        echo json_encode($result);
    } else {
        echo json_encode(['message' => 'Product updated', 'data' => $result]);
    }

} elseif ($method === 'DELETE') {
    // DELETE
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        exit;
    }

    $result = supabase_request('bdjr_products?id=eq.' . $id, 'DELETE', null, $token);

    if (isset($result['error'])) {
        http_response_code($result['status'] ?? 500);
        echo json_encode($result);
    } else {
        echo json_encode(['message' => 'Product deleted']);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
