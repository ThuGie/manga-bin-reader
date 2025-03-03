<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

class MangaBinMetadataHandler {
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
     * Generate and return metadata for a specific manga chapter
     * @param string $mangaId
     * @param float $chapterNumber
     * @return array Metadata about the bin file
     */
    public function getChapterMetadata($mangaId, $chapterNumber) {
        // Construct bin file path
        $binFilePath = sprintf(
            '%s/manga_%s_chapter_%s.bin', 
            $this->storageDir, 
            $mangaId, 
            $chapterNumber
        );

        // Check if file exists
        if (!file_exists($binFilePath)) {
            http_response_code(404);
            echo json_encode(['error' => 'Bin file not found']);
            exit;
        }

        // Open file and calculate image offsets
        $fileHandle = fopen($binFilePath, 'rb');
        $fileSize = filesize($binFilePath);
        
        $imageOffsets = [0]; // First image starts at 0
        $imageCount = 0;
        $currentOffset = 0;

        // Calculate offsets
        while ($currentOffset < $fileSize) {
            // Read image size (4-byte unsigned integer)
            $imageSizeData = fread($fileHandle, 4);
            $imageSize = unpack('N', $imageSizeData)[1];
            
            // Move to next image
            $currentOffset += 4 + $imageSize;
            $imageOffsets[] = $currentOffset;
            $imageCount++;
        }

        fclose($fileHandle);

        // Return metadata
        return [
            'mangaId' => $mangaId,
            'chapterNumber' => $chapterNumber,
            'fileSize' => $fileSize,
            'imageCount' => $imageCount,
            'imageOffsets' => $imageOffsets
        ];
    }

    /**
     * Create a bin file from uploaded images
     * @param string $mangaId
     * @param float $chapterNumber
     * @param array $imageFiles
     * @return string Path to created bin file
     */
    public function createBinFile($mangaId, $chapterNumber, $imageFiles) {
        // Ensure input validation
        if (!is_array($imageFiles) || empty($imageFiles)) {
            throw new Exception('No image files provided');
        }

        // Create bin file path
        $binFilePath = sprintf(
            '%s/manga_%s_chapter_%s.bin', 
            $this->storageDir, 
            $mangaId, 
            $chapterNumber
        );

        // Open bin file for writing
        $binFileHandle = fopen($binFilePath, 'wb');

        // Process and write each image
        foreach ($imageFiles as $imagePath) {
            // Read image content
            $imageContent = file_get_contents($imagePath);
            
            // Write image size (4-byte unsigned integer)
            $imageSize = strlen($imageContent);
            fwrite($binFileHandle, pack('N', $imageSize));
            
            // Write image content
            fwrite($binFileHandle, $imageContent);
        }

        // Close file
        fclose($binFileHandle);

        return $binFilePath;
    }

    /**
     * Handle incoming requests
     */
    public function handleRequest() {
        // Handle OPTIONS request for CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        // GET request for metadata
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $mangaId = $_GET['manga'] ?? null;
            $chapterNumber = $_GET['chapter'] ?? null;

            if (!$mangaId || !$chapterNumber) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing manga ID or chapter number']);
                exit;
            }

            try {
                $metadata = $this->getChapterMetadata($mangaId, $chapterNumber);
                echo json_encode($metadata);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        }

        // POST request for creating bin file
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mangaId = $_POST['manga'] ?? null;
            $chapterNumber = $_POST['chapter'] ?? null;
            $imagePaths = $_POST['images'] ?? null;

            if (!$mangaId || !$chapterNumber || !$imagePaths) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required parameters']);
                exit;
            }

            try {
                $binFilePath = $this->createBinFile($mangaId, $chapterNumber, $imagePaths);
                echo json_encode([
                    'success' => true,
                    'binFilePath' => $binFilePath
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
}

// Usage
$handler = new MangaBinMetadataHandler();
$handler->handleRequest();