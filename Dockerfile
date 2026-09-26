FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Configure Composer to ignore security advisories (for build)
RUN composer config --no-plugins allow-plugins.composer/composer true 2>/dev/null || true && \
    composer config --no-plugins policy.advisories.block false 2>/dev/null || true

# Install PHP dependencies (skip scripts to avoid package:discover issues)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Install Node dependencies and build assets
RUN npm install && npm run build

# Ensure bootstrap/cache exists and is writable
RUN mkdir -p /var/www/bootstrap/cache && chmod -R 775 /var/www/bootstrap/cache

# Ensure storage framework directories exist
RUN mkdir -p /var/www/storage/framework/sessions /var/www/storage/framework/views /var/www/storage/framework/cache /var/www/storage/framework/testing

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]