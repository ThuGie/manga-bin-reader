<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

class MangaChapterUploader {
    private $basePath;
    private $storageDir;
    private $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB per image
    private $maxTotalSize = 200 * 1024 * 1024; // 200MB total

    public function __construct($basePath = './storage') {
        $this->basePath = realpath($basePath);
        $this->storageDir = $this->basePath . '/manga_bins';
        $this->tempUploadDir = $this->basePath . '/temp_uploads';

        // Ensure directories exist
        foreach ([$this->storageDir, $this->tempUploadDir] as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }

    /**
     * Validate uploaded images
     * @param array $files Files to validate
     * @return array Validated files
     * @throws Exception If validation fails
     */
    private function validateImages($files) {
        $validatedFiles = [];
        $totalSize = 0;

        foreach ($files as $file) {
            // Check file type
            if (!in_array($file['type'], $this->allowedImageTypes)) {
                throw new Exception("Invalid file type: {$file['type']}");
            }

            // Check individual file size
            if ($file['size'] > $this->maxFileSize) {
                throw new Exception("File too large: {$file['name']}");
            }

            // Check total upload size
            $totalSize += $file['size'];
            if ($totalSize > $this->maxTotalSize) {
                throw new Exception("Total upload size exceeds limit");
            }

            $validatedFiles[] = $file;
        }

        return $validatedFiles;
    }

    /**
     * Create a bin file from sorted images
     * @param string $mangaId
     * @param float $chapterNumber
     * @param array $imageFiles
     * @return string Path to created bin file
     */
    public function createBinFile($mangaId, $chapterNumber, $imageFiles) {
        // Sort images by filename to ensure correct order
        usort($imageFiles, function($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        // Construct bin file path
        $binFilePath = sprintf(
            '%s/manga_%s_chapter_%s.bin', 
            $this->storageDir, 
            $mangaId, 
            $chapterNumber
        );

        // Open bin file for writing
        $binFileHandle = fopen($binFilePath, 'wb');

        // Process and write each image
        foreach ($imageFiles as $image) {
            // Read image content
            $imageContent = file_get_contents($image['tmp_name']);
            
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
     * Handle chapter upload request
     */
    public function handleUpload() {
        // Handle CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        // Extract parameters
        $mangaId = $_POST['manga_id'] ?? null;
        $chapterNumber = $_POST['chapter_number'] ?? null;

        // Validate parameters
        if (!$mangaId || !$chapterNumber) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing manga ID or chapter number']);
            exit;
        }

        // Check if files were uploaded
        if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
            http_response_code(400);
            echo json_encode(['error' => 'No images uploaded']);
            exit;
        }

        try {
            // Restructure $_FILES array
            $images = [];
            $fileCount = count($_FILES['images']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $images[] = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];
            }

            // Validate images
            $validatedImages = $this->validateImages($images);

            // Create bin file
            $binFilePath = $this->createBinFile($mangaId, $chapterNumber, $validatedImages);

            // Cleanup temporary files
            foreach ($validatedImages as $image) {
                @unlink($image['tmp_name']);
            }

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Chapter uploaded successfully',
                'bin_file' => basename($binFilePath),
                'image_count' => count($validatedImages)
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
    }
}

// Execute upload handling
$uploader = new MangaChapterUploader();
$uploader->handleUpload();