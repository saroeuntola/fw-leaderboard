<?php
require_once 'API_BASE.php';
function validateRequest()
{
    // DOMAIN CHECK
    $origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
    if (stripos($origin, ALLOWED_DOMAIN) !== 0) {
        http_response_code(403);
        exit('Forbidden: Your IP or domain is not allowed');
    }

}
