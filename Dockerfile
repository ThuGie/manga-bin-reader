# Multi-stage build for Manga Bin Reader

# Stage 1: Frontend Build
FROM node:16-alpine AS frontend-build
WORKDIR /app/frontend

# Copy package files and install dependencies
COPY package*.json ./
RUN npm ci

# Copy frontend source
COPY frontend ./
COPY webpack.config.js ./

# Build frontend assets
RUN npm run build

# Stage 2: PHP Backend
FROM php:8.0-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip fileinfo

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy backend files
COPY backend ./backend
COPY config.php ./
COPY .htaccess ./

# Copy built frontend from previous stage
COPY --from=frontend-build /app/frontend/dist ./frontend/dist

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

# Generate autoloader
RUN composer dump-autoload --no-dev --optimize

# Create storage directories
RUN mkdir -p /var/www/html/storage/manga_bins \
    && mkdir -p /var/www/html/storage/manga_covers \
    && mkdir -p /var/www/html/storage/temp_uploads \
    && mkdir -p /var/www/html/logs

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/logs

# Configure Apache
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]