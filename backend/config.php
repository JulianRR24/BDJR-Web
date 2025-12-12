<?php
// backend/config.php

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Handle Preflight Requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Supabase Configuration
define('SUPABASE_URL', 'https://hmtnewymuanlvdbfdmrh.supabase.co'); 

// Usa AQUÍ la service_role key (la que me acabas de pasar)
define('SUPABASE_KEY', 'poner aqui service_role key');

// Error Reporting (Desactivar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>