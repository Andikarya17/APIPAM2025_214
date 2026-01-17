<?php
/**
 * BENGKELKU API - Response Helper
 * 
 * CRITICAL: Functions here must NEVER produce fatal errors
 */

/**
 * JSON Response Helper - Safely outputs JSON and terminates script
 */
function jsonResponse($status, $message, $data = null, $code = 200) {
    // Clear all output buffers safely
    while (@ob_get_level() > 0) {
        @ob_end_clean();
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

/**
 * Log error to file - NEVER throws, uses @ to suppress all errors
 */
function logError($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    $logFile = $logDir . '/api_error.log';
    
    // Silently try to create logs directory
    if (!@is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? @json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr\n";
    
    // Silently try to write log
    @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Global error handler to catch fatal errors and return JSON
 */
function fatalErrorHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Clear any output
        while (@ob_get_level() > 0) {
            @ob_end_clean();
        }
        
        // Log the error
        @logError("FATAL ERROR", $error);
        
        // Return JSON error
        header("Content-Type: application/json; charset=utf-8");
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Server error: " . $error['message'],
            "data" => null
        ]);
    }
}

// Register shutdown handler
register_shutdown_function('fatalErrorHandler');
