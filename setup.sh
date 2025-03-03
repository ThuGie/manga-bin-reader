#!/bin/bash

# Manga Bin Reader Setup Script

# Ensure script is run with bash
if [ -z "$BASH_VERSION" ]
then
    exec bash "$0" "$@"
fi

# Check if running with sudo/root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run with sudo" 
   exit 1
fi

# Base storage directory
STORAGE_DIR="storage"

# Create necessary directories
DIRS=(
    "$STORAGE_DIR/manga_bins"
    "$STORAGE_DIR/manga_covers"
    "$STORAGE_DIR/temp_uploads"
    "$STORAGE_DIR/logs"
)

echo "Creating storage directories..."
for dir in "${DIRS[@]}"; do
    mkdir -p "$dir"
    chmod 775 "$dir"
    echo "Created directory: $dir"
done

# Set correct permissions
echo "Setting permissions..."
chown -R $(logname):$(logname) "$STORAGE_DIR"

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env file from example"
fi

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install

# Install Node.js dependencies
echo "Installing Node.js dependencies..."
npm install

# Build frontend
echo "Building frontend..."
npm run build

echo "Setup complete!"
exit 0