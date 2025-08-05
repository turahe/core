ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set build arguments for Laravel and Testbench versions
ARG LARAVEL_VERSION=10.*
ARG TESTBENCH_VERSION=10.*

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock* ./

# Install dependencies with specific Laravel and Testbench versions
RUN composer require "laravel/framework:${LARAVEL_VERSION}" "orchestra/testbench:${TESTBENCH_VERSION}" --no-interaction --no-update
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Copy application code
COPY . .

# Create test configuration
RUN cp docker/database.env .env
RUN cp phpunit.xml.dist phpunit.xml

# Set permissions
RUN chmod -R 755 /app

# Expose port for testing
EXPOSE 8000

# Default command
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"] 