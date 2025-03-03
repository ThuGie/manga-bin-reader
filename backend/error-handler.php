<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logger.php';

class ErrorHandler {
    /**
     * Register custom error and exception handlers
     */
    public static function register() {
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);

        // Set custom exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set a custom shutdown function to catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File where error occurred
     * @param int $errline Line number
     * @return bool
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        // Check if error should be reported based on current error reporting level
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Determine error type
        switch ($errno) {
            case E_USER_ERROR:
                Logger::error("ERROR [{$errno}] {$errstr}", [
                    'file' => $errfile,
                    'line' => $errline
                ]);
                self::sendErrorResponse(500, 'A critical error occurred');
                exit(1);

            case E_USER_WARNING:
            case E_WARNING:
                Logger::warning("WARNING [{$errno}] {$errstr}", [
                    'file' => $errfile,
                    'line' => $errline
                ]);
                break;

            case E_USER_NOTICE:
            case E_NOTICE:
            case E_STRICT:
                Logger::notice("NOTICE [{$errno}] {$errstr}", [
                    'file' => $errfile,
                    'line' => $errline
                ]);
                break;

            default:
                Logger::error("Unknown error type: [{$errno}] {$errstr}", [
                    'file' => $errfile,
                    'line' => $errline
                ]);
                break;
        }

        // Don't execute PHP's internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     * @param Throwable $exception
     */
    public static function handleException($exception) {
        // Log the full exception details
        Logger::error('Uncaught Exception: ' . $exception->getMessage(), [
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Send error response
        self::sendErrorResponse(
            $exception instanceof HttpException ? $exception->getStatusCode() : 500, 
            $exception->getMessage()
        );
    }

    /**
     * Handle shutdown to catch fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();

        // Check for fatal errors
        if ($error !== null && in_array($error['type'], [
            E_ERROR, 
            E_CORE_ERROR, 
            E_COMPILE_ERROR, 
            E_USER_ERROR
        ])) {
            // Clear output buffer to prevent partial content
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Log shutdown error
            Logger::emergency('Fatal Error', $error);

            // Send error response
            self::sendErrorResponse(500, 'A fatal error occurred');
        }
    }

    /**
     * Send standardized error response
     * @param int $statusCode HTTP status code
     * @param string $message Error message
     */
    public static function sendErrorResponse($statusCode = 500, $message = 'An error occurred') {
        // Clear any existing output
        if (ob_get_length()) {
            ob_clean();
        }

        // Set HTTP response code
        http_response_code($statusCode);

        // Set content type to JSON
        header('Content-Type: application/json');

        // Prepare error response
        $errorResponse = [
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message
        ];

        // Add debug information in development
        if (MangaReaderConfig::get('debug_mode', false)) {
            $errorResponse['debug'] = [
                'timestamp' => date('Y-m-d H:i:s'),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown'
            ];
        }

        // Output error as JSON
        echo json_encode($errorResponse);
        exit;
    }
}

/**
 * Custom HTTP Exception for more detailed error handling
 */
class HttpException extends \Exception {
    private $statusCode;

    public function __construct($message, $statusCode = 500) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }
}

// Register error handlers when the file is included
ErrorHandler::register();