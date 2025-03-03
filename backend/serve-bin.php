<?php
/**
 * Serve Manga Bin File Contents
 * Handles HTTP Range requests for efficient image extraction
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Range, Content-Type');

class MangaBinFileServer {
    private $basePath;
    private $storageDir;

    public function __construct($basePath = './storage') {
        $this->basePath = realpath($basePath);
        $this->storageDir = $this->basePath . '/manga_bins';

        // Ensure storage directory exists
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }
    }

    /**
     * Validate and sanitize manga ID and chapter number
     * @param string $mangaId
     * @param float $chapterNumber
     * @return string Full path to bin file
     * @throws Exception If file not found
     */
    private function validateBinFile($mangaId, $chapterNumber) {
        // Construct bin file path
        $binFilePath = sprintf(
            '%s/manga_%s_chapter_%s.bin', 
            $this->storageDir, 
            $mangaId, 
            $chapterNumber
        );

        // Check if file exists
        if (!file_exists($binFilePath)) {
            throw new Exception('Bin file not found', 404);
        }

        return $binFilePath;
    }

    /**
     * Handle HTTP Range request for bin file
     * @param string $binFilePath
     */
    private function serveFileRange($binFilePath) {
        $fileSize = filesize($binFilePath);
        $start = 0;
        $end = $fileSize - 1;

        // Check for Range header
        if (isset($_SERVER['HTTP_RANGE'])) {
            // Parse Range header
            if (preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches)) {
                $start = intval($matches[1]);
                $end = isset($matches[2]) ? intval($matches[2]) : $fileSize - 1;
            }
        }

        // Validate range
        $start = max(0, $start);
        $end = min($end, $fileSize - 1);
        $length = $end - $start + 1;

        // Set headers
        header('Content-Type: application/octet-stream');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $length);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Cache-Control: public, max-age=86400'); // 1-day cache

        // Open file and seek to start position
        $handle = fopen($binFilePath, 'rb');
        fseek($handle, $start);

        // Output requested range
        echo fread($handle, $length);
        fclose($handle);
        exit;
    }

    /**
     * Handle incoming requests
     */
    public function handleRequest() {
        // Handle CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        // Extract parameters
        $mangaId = $_GET['manga'] ?? null;
        $chapterNumber = $_GET['chapter'] ?? null;

        // Validate parameters
        if (!$mangaId || !$chapterNumber) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing manga ID or chapter number']);
            exit;
        }

        try {
            // Get full path to bin file
            $binFilePath = $this->validateBinFile($mangaId, $chapterNumber);

            // Serve file range
            $this->serveFileRange($binFilePath);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Execute request handling
$binFileServer = new MangaBinFileServer();
$binFileServer->handleRequest();