-- Manga Table
CREATE TABLE IF NOT EXISTS mangas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Chapters Table
CREATE TABLE IF NOT EXISTS chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manga_id INT NOT NULL,
    chapter_number DECIMAL(5,2) NOT NULL,
    bin_file_path VARCHAR(255) NOT NULL,
    image_count INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_manga_name ON mangas(name);
CREATE INDEX idx_manga_chapters ON chapters(manga_id, chapter_number);

-- Initial seed data (optional)
INSERT INTO mangas (name, description) VALUES 
    ('Sample Manga', 'A sample manga for initial setup')
ON DUPLICATE KEY UPDATE description = 'A sample manga for initial setup';