<?php
// backend/test_auth.php
require_once 'config.php';

header('Content-Type: application/json');

// 1. Test File Writing
$logFile = __DIR__ . '/test_log.txt';
$writeResult = file_put_contents($logFile, "Test write at " . date('Y-m-d H:i:s'));

$response = [
    'file_write_test' => $writeResult !== false ? 'SUCCESS' : 'FAILED',
    'log_path' => $logFile,
    'php_version' => phpversion(),
    'supabase_url' => defined('SUPABASE_URL') ? SUPABASE_URL : 'NOT_DEFINED',
    'supabase_key_length' => defined('SUPABASE_KEY') ? strlen(SUPABASE_KEY) : 0
];

// 2. Test Supabase Connection (Simple Health Check if possible, or just config)
// We'll try a simple curl to the auth endpoint to see if it's reachable
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SUPABASE_URL . '/auth/v1/health'); // Some endpoints might be different, but let's try root or health
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . SUPABASE_KEY]);
$curlResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response['supabase_connection_test'] = [
    'http_code' => $httpCode,
    'response' => $curlResponse
];

echo json_encode($response);
?>
