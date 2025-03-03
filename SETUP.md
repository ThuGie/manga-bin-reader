# Manga Bin Reader - Setup and Installation Guide

## Prerequisites

### Server Requirements
- PHP 7.4 or higher
- Apache web server with mod_rewrite enabled
- Minimum 1GB disk space for manga storage
- Recommended: PHP extensions
  - mbstring
  - json
  - fileinfo

### Browser Requirements
- Modern browser supporting:
  - ES6 JavaScript
  - IndexedDB
  - Fetch API
- Recommended browsers:
  - Chrome 80+
  - Firefox 75+
  - Safari 13.1+
  - Edge 80+

## Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/ThuGie/manga-bin-reader.git
cd manga-bin-reader
```

### 2. Configure Web Server
- Point your web server's document root to the project directory
- Ensure `.htaccess` is enabled and functional

### 3. Set Up Storage Directories
```bash
mkdir -p storage/manga_bins
mkdir -p storage/manga_covers
mkdir -p storage/temp_uploads
mkdir -p logs
```

### 4. Set Permissions
```bash
chmod -R 755 storage
chmod -R 755 logs
```

### 5. Configuration
Edit `config.php` to customize:
- Storage paths
- Logging settings
- Security configurations

### 6. Web Server Configuration

#### Apache
- Enable mod_rewrite
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx
Add to your server block:
```nginx
location / {
    try_files $uri $uri/ /frontend/index.html;
}
```

## Usage

### Adding Manga
1. Open the web interface
2. Click "Add New Manga"
3. Enter manga details and cover image

### Uploading Chapters
1. Select a manga
2. Click "Add Chapter"
3. Provide chapter number
4. Upload images in order

### Reading Manga
1. Click on a manga
2. Select a chapter
3. Navigate through pages

## Troubleshooting

### Common Issues
- Ensure PHP extensions are installed
- Check file permissions
- Verify web server configuration
- Review error logs in `logs/` directory

### Debugging
- Enable debug mode in `config.php`
- Check server logs
- Verify PHP version compatibility

## Performance Optimization

### Image Handling
- Large chapters may take longer to process
- Recommended: Compress images before upload
- Suggested image formats: WebP, JPEG

### Storage Management
- Periodically clean up unused bin files
- Monitor storage usage

## Security Notes
- Keep software updated
- Use strong file permissions
- Limit access to backend directories

## Contribution
Contributions are welcome! Please read `CONTRIBUTING.md`

## License
See `LICENSE` file for details

## Support
Open an issue on GitHub for bug reports or feature requests.