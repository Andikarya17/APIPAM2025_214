<?php
/**
 * JSON response helper - Simple version
 */
function jsonResponse($status, $message, $data = null, $code = 200) {
    // Discard any previous output
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}
