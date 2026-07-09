<?php
/**
 * Centralized Error Handler for MAATKA API
 */

class ErrorHandler {
    
    private static $logFile = __DIR__ . '/error.log';
    
    /**
     * Handle API errors
     */
    public static function handle($error, $statusCode = 500, $logError = true) {
        http_response_code($statusCode);
        
        if ($logError) {
            self::log($error, $statusCode);
        }
        
        // Determine error message based on environment
        $debug = defined('APP_DEBUG') && APP_DEBUG === true;
        
        $response = [
            'success' => false,
            'message' => self::getUserMessage($statusCode),
            'error_code' => $statusCode
        ];
        
        // Add detailed error in debug mode
        if ($debug && is_string($error)) {
            $response['debug'] = $error;
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Log error to file
     */
    public static function log($error, $statusCode = 500) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        
        $errorMessage = is_string($error) ? $error : json_encode($error);
        
        $logEntry = "[$timestamp] [HTTP $statusCode] [IP: $ip] [URI: $uri] $errorMessage" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Get user-friendly error message
     */
    private static function getUserMessage($statusCode) {
        $messages = [
            400 => 'Invalid request. Please check your input.',
            401 => 'Unauthorized access.',
            403 => 'Access forbidden.',
            404 => 'Resource not found.',
            405 => 'Method not allowed.',
            429 => 'Too many requests. Please try again later.',
            500 => 'Internal server error. Please try again.',
            503 => 'Service temporarily unavailable.'
        ];
        
        return $messages[$statusCode] ?? 'An error occurred.';
    }
    
    /**
     * Handle database errors
     */
    public static function handleDatabaseError($e) {
        self::log('Database Error: ' . $e->getMessage(), 500);
        self::handle('Database connection failed', 500, false);
    }
    
    /**
     * Handle validation errors
     */
    public static function handleValidationError($errors) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }
    
    /**
     * Handle payment errors
     */
    public static function handlePaymentError($error) {
        self::log('Payment Error: ' . $error, 500);
        echo json_encode([
            'success' => false,
            'message' => 'Payment processing failed. Please try again or contact support.',
            'error_code' => 'PAYMENT_ERROR'
        ]);
        exit;
    }
}

// Set global exception handler
set_exception_handler(function($e) {
    ErrorHandler::log('Uncaught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    ErrorHandler::handle('An unexpected error occurred', 500);
});

// Set global error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error = "[$errno] $errstr in $errfile:$errline";
    ErrorHandler::log($error);
    
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ErrorHandler::handle('A critical error occurred', 500);
    }
    
    return true;
});
?>