<?php
class MangaReaderConfig {
    // Base file storage configuration
    private static $config = [
        // Base path for storing manga files
        'storage_base_path' => __DIR__ . '/storage',

        // Allowed image types for upload
        'allowed_image_types' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif'
        ],

        // Maximum file size for individual images (10MB)
        'max_image_size' => 10 * 1024 * 1024,

        // Maximum total upload size per chapter (200MB)
        'max_total_upload_size' => 200 * 1024 * 1024,

        // Directories for different storage types
        'directories' => [
            'manga_bins' => 'manga_bins',
            'manga_covers' => 'manga_covers',
            'temp_uploads' => 'temp_uploads'
        ],

        // Logging configuration
        'logging' => [
            'enabled' => true,
            'log_path' => __DIR__ . '/logs/manga_reader.log',
            'log_level' => 'INFO'
        ],

        // Security settings
        'security' => [
            'allowed_origins' => [
                'http://localhost',
                'https://yourdomain.com'
            ],
            'max_chapters_per_manga' => 500,
            'max_images_per_chapter' => 300
        ]
    ];

    /**
     * Get a configuration value
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get($key, $default = null) {
        // Support dot notation for nested keys
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $nestedKey) {
            if (!isset($value[$nestedKey])) {
                return $default;
            }
            $value = $value[$nestedKey];
        }

        return $value;
    }

    /**
     * Set a configuration value
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public static function set($key, $value) {
        $keys = explode('.', $key);
        $current = &self::$config;

        foreach ($keys as $nestedKey) {
            $current = &$current[$nestedKey];
        }

        $current = $value;
    }

    /**
     * Initialize storage directories
     */
    public static function initializeStorageDirectories() {
        $basePath = self::get('storage_base_path');

        foreach (self::get('directories') as $dirKey => $dirName) {
            $fullPath = $basePath . '/' . $dirName;
            
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
        }
    }

    /**
     * Validate configuration
     * @return bool
     */
    public static function validate() {
        // Check base storage path
        if (!is_writable(self::get('storage_base_path'))) {
            throw new Exception('Storage base path is not writable');
        }

        // Validate logging configuration
        if (self::get('logging.enabled')) {
            $logPath = self::get('logging.log_path');
            $logDir = dirname($logPath);
            
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }

            if (!is_writable($logDir)) {
                throw new Exception('Log directory is not writable');
            }
        }

        return true;
    }
}

// Initialize storage directories on load
MangaReaderConfig::initializeStorageDirectories();