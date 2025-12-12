<?php
// backend/orders.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Get the JWT token from the Authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // 1. Create Order
    $orderData = [
        'user_id' => $input['user_id'], // In a real app, extract this from the JWT for security
        'total_amount' => $input['total_amount'],
        'shipping_address' => $input['shipping_address']
    ];

    $orderResult = supabase_request('bdjr_orders', 'POST', $orderData, $token);

    if (isset($orderResult['error'])) {
        http_response_code($orderResult['status']);
        echo json_encode($orderResult);
        exit;
    }

    $orderId = $orderResult[0]['id'];

    // 2. Create Order Items
    $items = $input['items'];
    $orderItemsData = [];
    foreach ($items as $item) {
        $orderItemsData[] = [
            'order_id' => $orderId,
            'product_id' => $item['id'],
            'quantity' => $item['quantity'],
            'price_at_purchase' => $item['price']
        ];
    }

    $itemsResult = supabase_request('bdjr_order_items', 'POST', $orderItemsData, $token);

    if (isset($itemsResult['error'])) {
        // Note: In a real app, you might want to rollback the order creation here
        http_response_code($itemsResult['status']);
        echo json_encode(['error' => 'Order created but items failed', 'details' => $itemsResult]);
    } else {
        echo json_encode(['message' => 'Order placed successfully', 'order' => $orderResult[0]]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
