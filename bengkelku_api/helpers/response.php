<?php
/**
 * JSON response helper.
 */
function jsonResponse($status, $message, $data = null, $code = 200) {
    // Clean any accidental output only if buffer exists
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
