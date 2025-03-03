<?php
require_once __DIR__ . '/../config.php';

class Logger {
    // Log levels
    const EMERGENCY = 'EMERGENCY';
    const ALERT     = 'ALERT';
    const CRITICAL  = 'CRITICAL';
    const ERROR     = 'ERROR';
    const WARNING   = 'WARNING';
    const NOTICE    = 'NOTICE';
    const INFO      = 'INFO';
    const DEBUG     = 'DEBUG';

    // Hierarchy of log levels
    private static $logLevels = [
        self::EMERGENCY => 7,
        self::ALERT     => 6,
        self::CRITICAL  => 5,
        self::ERROR     => 4,
        self::WARNING   => 3,
        self::NOTICE    => 2,
        self::INFO      => 1,
        self::DEBUG     => 0
    ];

    /**
     * Log a message
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function log($level, $message, array $context = []) {
        // Check if logging is enabled
        if (!MangaReaderConfig::get('logging.enabled')) {
            return;
        }

        // Get log path
        $logPath = MangaReaderConfig::get('logging.log_path');
        $configLogLevel = MangaReaderConfig::get('logging.log_level', self::INFO);

        // Check if message should be logged based on current log level
        if (self::$logLevels[$level] > self::$logLevels[$configLogLevel]) {
            return;
        }

        // Prepare log entry
        $timestamp = date('Y-m-d H:i:s');
        $contextString = $context ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextString}" . PHP_EOL;

        // Ensure log directory exists
        $logDir = dirname($logPath);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Write to log file
        error_log($logEntry, 3, $logPath);
    }

    /**
     * Log emergency messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function emergency($message, array $context = []) {
        self::log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log alert messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function alert($message, array $context = []) {
        self::log(self::ALERT, $message, $context);
    }

    /**
     * Log critical messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function critical($message, array $context = []) {
        self::log(self::CRITICAL, $message, $context);
    }

    /**
     * Log error messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function error($message, array $context = []) {
        self::log(self::ERROR, $message, $context);
    }

    /**
     * Log warning messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function warning($message, array $context = []) {
        self::log(self::WARNING, $message, $context);
    }

    /**
     * Log notice messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function notice($message, array $context = []) {
        self::log(self::NOTICE, $message, $context);
    }

    /**
     * Log info messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function info($message, array $context = []) {
        self::log(self::INFO, $message, $context);
    }

    /**
     * Log debug messages
     * @param string $message Log message
     * @param array $context Additional context
     */
    public static function debug($message, array $context = []) {
        self::log(self::DEBUG, $message, $context);
    }

    /**
     * Rotate log files to prevent them from becoming too large
     * @param int $maxFiles Maximum number of log files to keep
     * @param int $maxSize Maximum size of log file in bytes
     */
    public static function rotate($maxFiles = 5, $maxSize = 10485760) { // 10MB default
        $logPath = MangaReaderConfig::get('logging.log_path');
        
        // Check if log file exists and is too large
        if (file_exists($logPath) && filesize($logPath) > $maxSize) {
            $logDir = dirname($logPath);
            $filename = basename($logPath);

            // Rotate existing log files
            for ($i = $maxFiles - 1; $i > 0; $i--) {
                $oldFile = "{$logDir}/{$filename}.{$i}";
                $newFile = "{$logDir}/{$filename}." . ($i + 1);
                
                if (file_exists($oldFile)) {
                    rename($oldFile, $newFile);
                }
            }

            // Rename current log file
            rename($logPath, "{$logDir}/{$filename}.1");

            // Create a new log file
            touch($logPath);
            chmod($logPath, 0666);
        }
    }
}

// Automatically rotate logs when this file is included
Logger::rotate();