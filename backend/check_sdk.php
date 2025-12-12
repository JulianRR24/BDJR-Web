<?php
// backend/check_sdk.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    'C:/xampp/htdocs/bdjr-web/backend/vendor/autoload.php'
];

$results = [];
$found = false;
$sdkLoaded = false;

foreach ($paths as $path) {
    $exists = file_exists($path);
    $results[$path] = $exists ? 'FOUND' : 'NOT FOUND';
    if ($exists) {
        $found = true;
        // Try to require it to see if it crashes
        try {
            require_once $path;
            $results['autoload_load'] = 'SUCCESS';
            
            if (class_exists('MercadoPago\MercadoPagoConfig')) {
                $results['sdk_class'] = 'FOUND';
                $sdkLoaded = true;
            } else {
                $results['sdk_class'] = 'NOT FOUND (Autoload worked but class missing)';
            }
            
        } catch (Throwable $e) {
            $results['autoload_load'] = 'FAILED: ' . $e->getMessage();
        }
        break;
    }
}

echo json_encode([
    'status' => $sdkLoaded ? 'SUCCESS' : 'FAILURE',
    'message' => $sdkLoaded ? 'SDK Found and Loaded' : 'SDK Not Found or Failed to Load',
    'details' => $results,
    'php_version' => phpversion(),
    'current_dir' => __DIR__
]);
?>
