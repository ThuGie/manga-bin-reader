class MangaBinReader {
    constructor(binFileUrl) {
        this.binFileUrl = binFileUrl;
        this.imageCache = new Map();
        this.metadataCache = new Map();
    }

    /**
     * Fetch metadata for a specific chapter
     * @param {string} mangaId 
     * @param {number} chapterNumber 
     * @returns {Promise<Object>} Chapter metadata
     */
    async fetchChapterMetadata(mangaId, chapterNumber) {
        // Check cache first
        const cacheKey = `${mangaId}_${chapterNumber}`;
        if (this.metadataCache.has(cacheKey)) {
            return this.metadataCache.get(cacheKey);
        }

        // Fetch metadata from server
        const response = await fetch(`../backend/manga-metadata.php?manga=${mangaId}&chapter=${chapterNumber}`);
        
        if (!response.ok) {
            throw new Error('Failed to fetch chapter metadata');
        }

        const metadata = await response.json();
        this.metadataCache.set(cacheKey, metadata);
        return metadata;
    }

    /**
     * Extract a specific image from the bin file
     * @param {string} mangaId 
     * @param {number} chapterNumber 
     * @param {number} imageIndex 
     * @returns {Promise<Blob>} Image as a Blob
     */
    async getImage(mangaId, chapterNumber, imageIndex) {
        // Create a unique cache key
        const cacheKey = `${mangaId}_${chapterNumber}_${imageIndex}`;
        
        // Check image cache first
        if (this.imageCache.has(cacheKey)) {
            return this.imageCache.get(cacheKey);
        }

        // Fetch chapter metadata
        const metadata = await this.fetchChapterMetadata(mangaId, chapterNumber);

        // Validate image index
        if (imageIndex < 0 || imageIndex >= metadata.imageCount) {
            throw new Error('Invalid image index');
        }

        // Fetch the bin file
        const response = await fetch(this.binFileUrl, {
            headers: {
                // Request specific byte range
                'Range': `bytes=${metadata.imageOffsets[imageIndex]}-${metadata.imageOffsets[imageIndex + 1] || metadata.fileSize}`
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch image');
        }

        // Read image data
        const arrayBuffer = await response.arrayBuffer();
        const imageBlob = new Blob(
            [new Uint8Array(arrayBuffer)], 
            { type: this._detectMimeType(new Uint8Array(arrayBuffer)) }
        );

        // Cache the image
        this.imageCache.set(cacheKey, imageBlob);

        return imageBlob;
    }

    /**
     * Preload a range of images
     * @param {string} mangaId 
     * @param {number} chapterNumber 
     * @param {number} startIndex 
     * @param {number} count 
     */
    async preloadImages(mangaId, chapterNumber, startIndex, count) {
        const preloadPromises = [];
        for (let i = startIndex; i < startIndex + count; i++) {
            const cacheKey = `${mangaId}_${chapterNumber}_${i}`;
            if (!this.imageCache.has(cacheKey)) {
                preloadPromises.push(this.getImage(mangaId, chapterNumber, i));
            }
        }
        await Promise.all(preloadPromises);
    }

    /**
     * Detect MIME type based on image signature
     * @param {Uint8Array} imageData 
     * @returns {string} MIME type
     */
    _detectMimeType(imageData) {
        // JPEG signature
        if (imageData[0] === 0xFF && imageData[1] === 0xD8 && imageData[2] === 0xFF) {
            return 'image/jpeg';
        }
        
        // PNG signature
        if (imageData[0] === 137 && imageData[1] === 80 && imageData[2] === 78 && imageData[3] === 71) {
            return 'image/png';
        }
        
        // WebP signature
        if (imageData[0] === 82 && imageData[1] === 73 && imageData[2] === 70 && imageData[3] === 70) {
            return 'image/webp';
        }
        
        // Default fallback
        return 'application/octet-stream';
    }

    /**
     * Clear caches to free up memory
     */
    clearCaches() {
        this.imageCache.clear();
        this.metadataCache.clear();
    }
}

// Export for use in other modules
export default MangaBinReader;