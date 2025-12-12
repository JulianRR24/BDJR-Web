<?php
// backend/ping.php
require_once 'config.php'; // CORS headers
header('Content-Type: application/json');

echo json_encode(['status' => 'ok']);
?>
