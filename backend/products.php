<?php
// backend/products.php
require_once 'config.php'; // <-- added for CORS headers
require_once 'db.php';
header('Content-Type: application/json'); // <-- ensure JSON response

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch all products
    // Select * from products
    $products = supabase_request('bdjr_products?select=*&order=created_at.desc');
    
    if (isset($products['error'])) {
        http_response_code($products['status']);
        echo json_encode($products);
    } else {
        echo json_encode($products);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
