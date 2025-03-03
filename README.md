# Manga Bin Reader

## Quick Start Guide

### Prerequisites
- Node.js (14.0+ recommended)
- PHP (7.4+ recommended)
- Composer
- MySQL/MariaDB
- Docker (optional)

### Local Development Setup

#### 1. Clone the Repository
```bash
git clone https://github.com/ThuGie/manga-bin-reader.git
cd manga-bin-reader
```

#### 2. Run Setup Script
```bash
# Make setup script executable
chmod +x setup.sh

# Run setup (requires sudo for directory permissions)
sudo ./setup.sh
```

#### 3. Database Setup
```bash
# Option 1: Manual Database Creation
mysql -u root -p
> CREATE DATABASE manga_library;
> USE manga_library;
> source database/migrations/001_create_initial_tables.sql;

# Option 2: Using Docker Compose
docker-compose up -d database
```

#### 4. Configure Environment
```bash
# Edit .env file with your database credentials
nano .env
```

#### 5. Start Development Servers
```bash
# Start PHP built-in server
php -S localhost:8000 -t frontend

# In another terminal, start frontend dev server
npm run start:dev
```

### Docker Setup (Alternative)
```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs

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
- Confirm database connection settings

### Project Structure
```
manga-bin-reader/
├── backend/           # PHP backend scripts
├── frontend/          # React frontend
├── storage/           # Manga storage
│   ├── manga_bins/    # Binary manga files
│   ├── manga_covers/  # Cover image storage
│   └── temp_uploads/ # Temporary upload directory
├── database/          # Database migrations
└── tests/             # Unit and integration tests
```

### Contributing
Please read `CONTRIBUTING.md` for details on our code of conduct and the process for submitting pull requests.

### License
This project is licensed under the MIT License - see the `LICENSE` file for details.