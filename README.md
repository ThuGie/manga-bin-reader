# Manga Bin Reader

## Quick Start Guide

### Prerequisites
- Node.js (14.0+ recommended)
- PHP (7.4+ recommended)
- Composer
- Docker (optional)

### Local Development Setup

#### 1. Clone the Repository
```bash
git clone https://github.com/ThuGie/manga-bin-reader.git
cd manga-bin-reader
```

#### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

#### 3. Configure Environment
```bash
# Copy example environment file
cp .env.example .env

# Edit .env file with your configuration
```

#### 4. Start Development Server
```bash
# Start frontend development server
npm run start:dev

# Or start simple http server
npm start
```

#### 5. Backend Server
- Ensure you have a PHP-enabled web server (Apache/Nginx)
- Point document root to the project directory

### Docker Setup (Alternative)
```bash
# Build and start containers
docker-compose up -d

# Stop containers
docker-compose down
```

### Development Scripts
- `npm run build`: Build production frontend
- `npm test`: Run JavaScript tests
- `composer test`: Run PHP tests
- `npm run lint`: Lint JavaScript code
- `composer lint`: Lint PHP code

### Troubleshooting
- Ensure all dependencies are installed
- Check `.env` file configuration
- Verify PHP extensions are enabled

### Contributing
Please read `CONTRIBUTING.md` for details on our code of conduct and the process for submitting pull requests.

### License
This project is licensed under the MIT License - see the `LICENSE` file for details.